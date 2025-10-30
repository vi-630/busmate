<?php
require_once dirname(__DIR__) . '/Libraries/Database.php';
require_once dirname(__DIR__) . '/Models/Usuario.php'; // ajuste o caminho se seu Model estiver em outra pasta

// helper: formata número para exibição com máscara brasileira
function formatPhoneMask(string $raw): ?string {
	$digits = preg_replace('/\D+/', '', $raw);
	if ($digits === '') return null;
	// Celular 11 dígitos: (00) 0 0000-0000
	if (strlen($digits) === 11) {
		return sprintf('(%s) %s %s-%s',
			substr($digits, 0, 2),
			substr($digits, 2, 1),
			substr($digits, 3, 4),
			substr($digits, 7, 4)
		);
	}
	// Fixo 10 dígitos: (00) 0000-0000
	if (strlen($digits) === 10) {
		return sprintf('(%s) %s-%s',
			substr($digits, 0, 2),
			substr($digits, 2, 4),
			substr($digits, 6, 4)
		);
	}
	// Outros tamanhos: tenta formatar ao menos com DDD (se houver)
	if (strlen($digits) > 2) {
		$ddd = substr($digits,0,2);
		$rest = substr($digits,2);
		return '(' . $ddd . ') ' . $rest;
	}
	return $raw;
}

class Empresas {
public function cadastrar() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . URL . '/paginas/cadastro_empresa');
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    // só ROOT pode cadastrar empresa
    if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
        header('Location: ' . URL . '/paginas/entrar?error=forbidden');
        exit;
    }

    // quem está criando (root atual)
    $criadorId = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    // Coleta de dados
    $dados = [
        'nome'       => trim($_POST['empr_nome'] ?? ''),
        'cnpj'       => trim($_POST['empr_cnpj'] ?? ''),
        'razao'      => trim($_POST['empr_razao'] ?? ''),
        'qtd_admin'  => max(1, intval($_POST['empr_qtd_admin'] ?? 1)), // no mínimo 1
        'logo'       => null,
    ];

    foreach (['nome','cnpj','razao'] as $campo) {
        if ($dados[$campo] === '') {
            header('Location: ' . URL . '/paginas/cadastro_empresa?erro=' . urlencode("Campo obrigatório: $campo"));
            exit;
        }
    }

    // Upload da logo (opcional)
    $uploadBase = dirname(__DIR__, 2) . '/public/uploads';
    $logoDir = $uploadBase . '/empresas_logo';
    if (!is_dir($logoDir)) mkdir($logoDir, 0755, true);

    if (!empty($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['logo'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg','image/png','image/webp'];
        if (in_array($mime, $allowed) && $f['size'] <= 5 * 1024 * 1024) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $newName = time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
            $dest = $logoDir.'/'.$newName;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $dados['logo'] = 'public/uploads/empresas_logo/'.$newName;
            }
        }
    }

    // INSERT com empr_criado_por e empr_qtd_admin (schema novo)
    require_once dirname(__DIR__) . '/Libraries/Database.php';
    $db = new Database();
    $db->query("
        INSERT INTO empresa
            (empr_nome, empr_razao, empr_cnpj, empr_logo, empr_criado_por, empr_qtd_admin)
        VALUES
            (:nome, :razao, :cnpj, :logo, :criado_por, :qtd)
    ");
    $db->bind(':nome',       $dados['nome']);
    $db->bind(':razao',      $dados['razao']);
    $db->bind(':cnpj',       $dados['cnpj']);
    $db->bind(':logo',       $dados['logo']);
    // se por algum motivo não houver user_id em sessão, grava NULL
    if ($criadorId === null) {
        $db->bind(':criado_por', null, PDO::PARAM_NULL);
    } else {
        $db->bind(':criado_por', $criadorId);
    }
    $db->bind(':qtd',        $dados['qtd_admin']);

    try {
        $db->executa();
        $newId = $db->ultimoIdInserido();
    } catch (Throwable $t) {
        file_put_contents(dirname(__DIR__).'/debug_empresa.txt', "[".date('Y-m-d H:i:s')."] ERRO INSERT EMPRESA:\n".$t->getMessage()."\n", FILE_APPEND);
        header('Location: ' . URL . '/paginas/cadastro_empresa?erro=' . urlencode('Erro ao cadastrar empresa.'));
        exit;
    }

    // Fallback (se criador ficou nulo por algum motivo, preenche agora)
    if (!empty($newId) && $criadorId !== null) {
        try {
            $db->query("UPDATE empresa SET empr_criado_por = :u WHERE empr_id = :id AND empr_criado_por IS NULL");
            $db->bind(':u', $criadorId);
            $db->bind(':id', $newId);
            $db->executa();
        } catch (Throwable $t) {
            file_put_contents(dirname(__DIR__).'/debug_empresa.txt', "[".date('Y-m-d H:i:s')."] Fallback criador:\n".$t->getMessage()."\n", FILE_APPEND);
        }
    }

    // Redireciona para cadastrar os administradores (usando a quantidade informada)
    if (!empty($newId) && $dados['qtd_admin'] > 0) {
        header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . $newId . '&faltam=' . $dados['qtd_admin']);
    } else {
        header('Location: ' . URL . '/paginas/detalhe_empresa?id=' . $newId);
    }
    exit;
}




    // ... seu método cadastrar() da empresa fica aqui ...

    public function cadastrar_admin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
        header('Location: ' . URL . '/paginas/entrar?error=forbidden');
        exit;
    }

    $empr_id   = intval($_POST['empr_id'] ?? 0);
    $nome      = trim($_POST['nome'] ?? '');
    $cpf       = trim($_POST['cpf'] ?? '');
    $senha     = (string)($_POST['senha'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $email_rec = trim($_POST['email_recuperacao'] ?? '');
    $tel_pri   = trim($_POST['telefone'] ?? '');
    $tel_alt   = trim($_POST['telefone_resp'] ?? ''); // pode renomear no HTML depois
    $faltam    = intval($_POST['faltam'] ?? ($_GET['faltam'] ?? 0));

    $missing = null;
    foreach (['nome','cpf','senha','email','telefone'] as $req) {
        if (trim((string)($_POST[$req] ?? '')) === '') { $missing = $req; break; }
    }
    if ($missing || $empr_id <= 0) {
        $msg = $empr_id <= 0 ? 'Empresa não informada.' : ('Campo obrigatório ausente: ' . $missing);
        header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . urlencode($empr_id) . '&faltam=' . max(1,$faltam) . '&erro=' . urlencode($msg));
        exit;
    }

    // Upload opcional da foto
    $uploadBase = dirname(__DIR__, 2) . '/public/uploads';
    if (!is_dir($uploadBase)) mkdir($uploadBase, 0755, true);
    $fotosDir = $uploadBase . '/usuarios_img';
    if (!is_dir($fotosDir)) mkdir($fotosDir, 0755, true);

    $fotoPath = null;
    if (!empty($_FILES['foto']) && isset($_FILES['foto']['error']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $f = $_FILES['foto'];
        $ext  = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $fotosDir . '/' . $name;
        if (move_uploaded_file($f['tmp_name'], $dest)) {
            $fotoPath = 'public/uploads/usuarios_img/' . $name;
        }
    }

    // Cria usuário (tius_id = 2 = ADMIN) JÁ COM empr_id
    try {
        $usuarioModel = new Usuario();
        $res = $usuarioModel->cadastrar([
            'nome'     => $nome,
            'cpf'      => $cpf,
            'senha'    => $senha,
            'tius_id'  => 2,
            'foto'     => $fotoPath,
            'empr_id'  => $empr_id,   // <== ESSENCIAL
        ]);

        if (isset($res['erro'])) {
            header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . urlencode($empr_id) . '&faltam=' . max(1,$faltam) . '&erro=' . urlencode($res['erro']));
            exit;
        }
        $newUserId = $res['id'] ?? null;
        if (empty($newUserId)) {
            throw new Exception('Falha ao cadastrar usuário administrador.');
        }
    } catch (Throwable $t) {
        @file_put_contents(dirname(__DIR__) . '/debug_empresa.txt', "[".date('Y-m-d H:i:s')."] Exceção ao cadastrar admin: ".$t->getMessage()."\n", FILE_APPEND);
        header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . urlencode($empr_id) . '&faltam=' . max(1,$faltam) . '&erro=' . urlencode('Erro ao cadastrar administrador.'));
        exit;
    }

    // Persistir email/telefone
    $db = new Database();

    if (!empty($email)) {
        $db->query("SELECT 1 FROM email WHERE emai_endereco = :e LIMIT 1");
        $db->bind(':e', $email);
        if ($db->resultado()) {
            header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . urlencode($empr_id) . '&faltam=' . max(1,$faltam) . '&erro=' . urlencode('Este e-mail já está cadastrado.'));
            exit;
        }
        $db->query("INSERT INTO email (emai_endereco, usua_id, is_principal) VALUES (:e, :u, 1)");
        $db->bind(':e', $email);
        $db->bind(':u', $newUserId);
        $db->executa();
    }

    if (!empty($email_rec)) {
        $db->query("INSERT INTO email (emai_endereco, usua_id, is_principal) VALUES (:e, :u, 0)");
        $db->bind(':e', $email_rec);
        $db->bind(':u', $newUserId);
        $db->executa();
    }

    if (!empty($tel_pri)) {
        $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:n, :u, 'CEL', 1)");
        $db->bind(':n', $tel_pri);
        $db->bind(':u', $newUserId);
        $db->executa();
    }
    if (!empty($tel_alt)) {
        $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:n, :u, 'COM', 0)");
        $db->bind(':n', $tel_alt);
        $db->bind(':u', $newUserId);
        $db->executa();
    }

    // Fallback: se por algum motivo não ficou gravado o empr_id no INSERT, força o vínculo
    try {
        $db->query("UPDATE usuario SET empr_id = :e WHERE usua_id = :u AND (empr_id IS NULL OR empr_id = 0)");
        $db->bind(':e', $empr_id);
        $db->bind(':u', $newUserId);
        $db->executa();
    } catch (Throwable $t) {
        @file_put_contents(dirname(__DIR__) . '/debug_empresa.txt', "[".date('Y-m-d H:i:s')."] Falha no fallback empre_id: ".$t->getMessage()."\n", FILE_APPEND);
    }

    // Fluxo sequencial
    $faltam = max(0, $faltam - 1);
    if ($faltam > 0) {
        header('Location: ' . URL . '/paginas/cadastro_admin_empresa?id=' . $empr_id . '&faltam=' . $faltam);
        exit;
    } else {
        $_SESSION['admin_cadastrado'] = true;
        header('Location: ' . URL . '/paginas/detalhe_empresa?id=' . $empr_id);
        exit;
    }
}

}
?>
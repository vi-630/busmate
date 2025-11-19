<?php
class Alunos extends Controllers
{
    private function requireDb() {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        return new Database();
    }

    /** PASSO 1: recebe o POST do formulário do aluno e guarda em sessão; vai para escolher_empresa */
public function solicitar() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Campos do formulário
    $nome         = trim($_POST['nome'] ?? '');
    $escola       = trim($_POST['escola'] ?? '');
    $curso        = trim($_POST['curso'] ?? '');
    $telefone     = trim($_POST['telefone'] ?? '');
    $telefoneResp = trim($_POST['telefone_resp'] ?? '');
    $turma        = trim($_POST['turma'] ?? '');
    $turno        = trim($_POST['turno'] ?? '');
    $endereco     = trim($_POST['endereco'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $emailRec     = trim($_POST['email_recuperacao'] ?? '');
    $senha        = (string)($_POST['senha'] ?? '');
    $obs          = trim($_POST['obs'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Preencha os campos obrigatórios.'));
        exit;
    }

    // Diretórios TMP (SEPARADOS)
    $fotoTmpDir = dirname(__DIR__, 2) . '/public/uploads/usuarios_img/tmp';
    $docTmpDir  = dirname(__DIR__, 2) . '/public/uploads/documentos/tmp';
    if (!is_dir($fotoTmpDir)) @mkdir($fotoTmpDir, 0777, true);
    if (!is_dir($docTmpDir))  @mkdir($docTmpDir,  0777, true);

    // FOTO (opcional)
    $fotoTmpPath = null;
    if (!empty($_FILES['foto']['name'])) {
        $f = $_FILES['foto'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $nomeArq = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = $fotoTmpDir . '/' . $nomeArq;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $fotoTmpPath = 'public/uploads/usuarios_img/tmp/' . $nomeArq;
            }
        }
    }

    // Função auxiliar para processar uploads de documentos
    $processarDocumento = function($fieldName) use ($docTmpDir) {
        if (!empty($_FILES[$fieldName]['name'])) {
            $f = $_FILES[$fieldName];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $nomeArq = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $docTmpDir . '/' . $nomeArq;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    return 'public/uploads/documentos/tmp/' . $nomeArq;
                }
            }
        }
        return null;
    };

    // Processar todos os documentos
    $comprovanteMaTriculaTmpPath = $processarDocumento('comprovante_matricula');
    $comprovatResidenciaTmpPath  = $processarDocumento('comprovante_residencia');
    $rgArquivoTmpPath            = $processarDocumento('rg_arquivo');
    $cpfArquivoTmpPath           = $processarDocumento('cpf_arquivo');
    $docResponsavelTmpPath       = $processarDocumento('doc_responsavel'); // opcional

    // Validar documentos obrigatórios
    if (!$comprovanteMaTriculaTmpPath || !$comprovatResidenciaTmpPath || !$rgArquivoTmpPath || !$cpfArquivoTmpPath) {
        header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Preencha todos os documentos obrigatórios.'));
        exit;
    }

    $_SESSION['cad_aluno'] = [
        'nome'                    => $nome,
        'escola'                  => $escola,
        'curso'                   => $curso,
        'telefone'                => $telefone,
        'telefone_resp'           => $telefoneResp,
        'turma'                   => $turma,
        'turno'                   => $turno,
        'endereco'                => $endereco,
        'email'                   => $email,
        'email_rec'               => $emailRec,
        'senha'                   => $senha,
        'obs'                     => $obs,
        'foto_tmp'                => $fotoTmpPath,
        'comprovante_matricula'   => $comprovanteMaTriculaTmpPath,
        'comprovante_residencia'  => $comprovatResidenciaTmpPath,
        'rg_arquivo'              => $rgArquivoTmpPath,
        'cpf_arquivo'             => $cpfArquivoTmpPath,
        'doc_responsavel'         => $docResponsavelTmpPath,
    ];

    header('Location: ' . URL . '/alunos/escolher_empresa');
    exit;
}



    /** PASSO 2: lista as empresas para o aluno selecionar */
    public function escolher_empresa() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['cad_aluno'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Preencha seus dados primeiro.'));
            exit;
        }

        $db = $this->requireDb();
        $q = trim($_GET['q'] ?? '');
        
        if (!empty($q)) {
            // Busca por nome, CNPJ, ou razão social
            $db->query(
                "SELECT e.empr_id, e.empr_nome, e.empr_cnpj, e.empr_razao, e.empr_logo,\n" .
                "  (SELECT GROUP_CONCAT(u.usua_nome SEPARATOR ', ') FROM usuario u WHERE u.empr_id = e.empr_id AND u.tius_id = 2 AND (u.usua_situacao IS NULL OR u.usua_situacao = 'A')) AS admins\n" .
                "FROM empresa e\n" .
                "WHERE e.empr_nome LIKE :q OR e.empr_cnpj LIKE :q OR e.empr_razao LIKE :q\n" .
                "ORDER BY e.empr_dth_criacao DESC"
            );
            $db->bind(':q', '%' . $q . '%');
        } else {
            $db->query(
                "SELECT e.empr_id, e.empr_nome, e.empr_cnpj, e.empr_razao, e.empr_logo,\n" .
                "  (SELECT GROUP_CONCAT(u.usua_nome SEPARATOR ', ') FROM usuario u WHERE u.empr_id = e.empr_id AND u.tius_id = 2 AND (u.usua_situacao IS NULL OR u.usua_situacao = 'A')) AS admins\n" .
                "FROM empresa e\n" .
                "ORDER BY e.empr_dth_criacao DESC"
            );
        }
        $empresas = $db->resultados();

        // Reaproveito sua pasta de views "paginas" para não criar outra
        $this->view('paginas/escolher_empresa', ['empresas' => $empresas, 'q' => $q]);
    }

    /** PASSO 3: cria a solicitação no banco e vai para status */
    public function criar_solicitacao() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['cad_aluno'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Sessão expirada. Refaça seu cadastro.'));
            exit;
        }

        $empr_id = intval($_POST['empr_id'] ?? 0);
        if ($empr_id <= 0) {
            header('Location: ' . URL . '/alunos/escolher_empresa?erro=' . urlencode('Selecione uma empresa válida.'));
            exit;
        }

        $cad = $_SESSION['cad_aluno'];
        $db  = $this->requireDb();

        // token para acompanhar status sem login
        $token = $this->uuidv4();

        // Inserir na solicitacao_aluno (removendo soli_comprovante_url)
        $sql = "INSERT INTO solicitacao_aluno (
            soli_nome,
            soli_email,
            soli_email_recuperacao,
            soli_tel,
            soli_responsavel_tel,
            soli_escola,
            soli_turno,
            soli_endereco,
            soli_curso,
            soli_turma,
            empr_id,
            soli_token,
            soli_senha_hash,
            soli_foto_url
        ) VALUES (
            :soli_nome,
            :soli_email,
            :soli_email_recuperacao,
            :soli_tel,
            :soli_responsavel_tel,
            :soli_escola,
            :soli_turno,
            :soli_endereco,
            :soli_curso,
            :soli_turma,
            :empr_id,
            :soli_token,
            :soli_senha_hash,
            :soli_foto_url
        )";

        $db->query($sql);
        $db->bind(':soli_nome', $cad['nome']);
        $db->bind(':soli_email', $cad['email']);
        $db->bind(':soli_email_recuperacao', $cad['email_rec']);
        $db->bind(':soli_tel', $cad['telefone']);
        $db->bind(':soli_responsavel_tel', $cad['telefone_resp']);
        $db->bind(':soli_escola', $cad['escola']);
        $db->bind(':soli_turno', $cad['turno']);
        $db->bind(':soli_endereco', $cad['endereco']);
        $db->bind(':soli_curso', $cad['curso']);
        $db->bind(':soli_turma', $cad['turma']);
        $db->bind(':empr_id', $empr_id);
        $db->bind(':soli_token', $token);
        $db->bind(':soli_senha_hash', password_hash($cad['senha'], PASSWORD_DEFAULT));
        $db->bind(':soli_foto_url', $cad['foto_tmp']);
        $db->executa();

        // Obter o ID da solicitação recém-criada
        $soli_id = $db->ultimoIdInserido();

        // Inserir documentos na tabela solicitacao_documento
        $documentos = [
            ['tipo' => 'COMPROVANTE_MATRICULA', 'url' => $cad['comprovante_matricula']],
            ['tipo' => 'COMPROVANTE_RESIDENCIA', 'url' => $cad['comprovante_residencia']],
            ['tipo' => 'RG_ALUNO', 'url' => $cad['rg_arquivo']],
            ['tipo' => 'CPF_ALUNO', 'url' => $cad['cpf_arquivo']],
        ];

        // Adicionar documento do responsável se fornecido
        if (!empty($cad['doc_responsavel'])) {
            $documentos[] = ['tipo' => 'DOC_RESPONSAVEL', 'url' => $cad['doc_responsavel']];
        }

        foreach ($documentos as $doc) {
            $db->query("INSERT INTO solicitacao_documento (soli_id, sodo_tipo, sodo_url_tmp) VALUES (:soli_id, :sodo_tipo, :sodo_url_tmp)");
            $db->bind(':soli_id', $soli_id);
            $db->bind(':sodo_tipo', $doc['tipo']);
            $db->bind(':sodo_url_tmp', $doc['url']);
            $db->executa();
        }

        // limpa a sessão do cadastro para não re-submeter
        unset($_SESSION['cad_aluno']);

        // Vai para a tela de status
        header('Location: ' . URL . '/alunos/status?token=' . urlencode($token));
        exit;
    }

    /** PASSO 4: página de status (aguardando aprovação) */
public function status() {
    $token = $_GET['token'] ?? '';
    if ($token === '') {
        header('Location: ' . URL . '/paginas/home');
        exit;
    }

    $db = $this->requireDb();
    $db->query("
        SELECT 
            s.soli_id,
            s.soli_nome,
            s.soli_dth_criacao,
            s.soli_status,
            s.motivo_recusa,
            e.empr_nome,
            e.empr_cnpj
        FROM solicitacao_aluno s
        INNER JOIN empresa e ON e.empr_id = s.empr_id
        WHERE s.soli_token = :t
        LIMIT 1
    ");
    $db->bind(':t', $token);
    $sol = $db->resultado();

    // Renomeia os campos para que a view funcione sem mudar nada
    if ($sol) {
        $sol->status     = $sol->soli_status; // compatível com o que a view espera
        $sol->empr_cnpj  = $sol->empr_cnpj ?? ''; // garante que exista
    }

    $this->view('paginas/solicitacao_status', ['sol' => $sol]);
}


    /** util: UUID v4 simples */
    private function uuidv4(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
<?php
// app/Controllers/Alunos.php
class Alunos extends Controllers
{
    // 1) Recebe o POST do cadastro_aluno (seu formulário atual), guarda rascunho e redireciona para escolher_empresa
    public function solicitar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/cadastro_aluno');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();

        // Campos do seu form
        $nome        = trim($_POST['nome'] ?? '');
        $escola      = trim($_POST['escola'] ?? '');
        $curso       = trim($_POST['curso'] ?? '');
        $telefone    = trim($_POST['telefone'] ?? '');
        $telefoneResp= trim($_POST['telefone_resp'] ?? '');
        $turma       = trim($_POST['turma'] ?? '');
        $turno       = trim($_POST['turno'] ?? '');
        $endereco    = trim($_POST['endereco'] ?? '');
        $emailRec    = trim($_POST['email_recuperacao'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $senha       = (string)($_POST['senha'] ?? ''); // não cria usuário ainda

        // Validação mínima
        if ($nome === '' || $email === '') {
            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Preencha nome e e-mail.'));
            exit;
        }

        // Uploads temporários (foto + comprovante)
        $baseDir = 'public/uploads/solicitacoes_tmp';
        if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);

        $uuid = self::uuidv4();
        $tmpDir = $baseDir . '/' . $uuid;
        @mkdir($tmpDir, 0777, true);

        $fotoUrl = null;
        if (!empty($_FILES['foto']['name'])) {
            $ok = self::saveUpload($_FILES['foto'], $tmpDir, ['image/png','image/jpeg','image/jpg','image/webp'], 10*1024*1024, $savedName);
            if (!$ok) {
                header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Foto inválida (PNG/JPG/WEBP, até 10MB).'));
                exit;
            }
            $fotoUrl = $tmpDir . '/' . $savedName;
        }

        $comprovanteUrl = null;
        if (!empty($_FILES['comprovante']['name'])) {
            $ok = self::saveUpload($_FILES['comprovante'], $tmpDir, ['application/pdf','image/png','image/jpeg','image/jpg','image/webp'], 5*1024*1024, $savedName2);
            if (!$ok) {
                header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Comprovante inválido (PDF/PNG/JPG/WEBP, até 5MB).'));
                exit;
            }
            $comprovanteUrl = $tmpDir . '/' . $savedName2;
        }

        // Guarda rascunho em sessão até escolher a empresa
        $_SESSION['soli_draft'] = [
            'uuid'            => $uuid,
            'nome'            => $nome,
            'escola'          => $escola,
            'curso'           => $curso,
            'telefone'        => $telefone,
            'telefone_resp'   => $telefoneResp,
            'turma'           => $turma,
            'turno'           => $turno,
            'endereco'        => $endereco,
            'email'           => $email,
            'email_rec'       => $emailRec,
            'senha_plain'     => $senha,           // só para possível uso futuro; NÃO inserir usuário aqui!
            'foto_url'        => $fotoUrl,
            'comprovante_url' => $comprovanteUrl,
        ];

        // Vai escolher a empresa
        header('Location: ' . URL . '/alunos/escolher_empresa');
        exit;
    }

    // 2) Lista empresas em cards para o aluno escolher
    public function escolher_empresa()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['soli_draft'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Preencha seus dados primeiro.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $where = '';
        if ($q !== '') {
            $where = "WHERE e.empr_nome LIKE :q OR e.empr_cnpj LIKE :q OR e.empr_razao LIKE :q";
        }

        $sql = "
            SELECT e.empr_id, e.empr_nome, e.empr_cnpj, e.empr_razao, e.empr_logo,
                   u.usua_nome AS criador_nome
            FROM empresa e
            LEFT JOIN usuario u ON u.usua_id = e.empr_criado_por
            $where
            ORDER BY e.empr_dth_criacao DESC
        ";
        $db->query($sql);
        if ($q !== '') $db->bind(':q', "%{$q}%");
        $empresas = $db->resultados();

        $this->view('paginas/escolher_empresa', ['empresas' => $empresas]);
    }

    // 3) Confirma a empresa escolhida e insere em solicitacao_aluno
    public function criar_solicitacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/alunos/escolher_empresa');
            exit;
        }
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['soli_draft'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Sessão expirada. Reenvie o formulário.'));
            exit;
        }

        $empr_id = intval($_POST['empr_id'] ?? 0);
        if ($empr_id <= 0) {
            header('Location: ' . URL . '/alunos/escolher_empresa?erro=' . urlencode('Selecione uma empresa válida.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // Gera token para o aluno acompanhar o status
        $token = self::uuidv4();

        // Garante que a tabela solicitacao_aluno tenha campos para foto e comprovante
        // (Se você já criou esses campos, ignora este comentário.)
        $draft = $_SESSION['soli_draft'];

        $sql = "
            INSERT INTO solicitacao_aluno
              (soli_nome, soli_matricula, soli_email, soli_telefone, soli_escola, soli_turno, soli_endereco, soli_curso, soli_obs,
               empr_id, status, motivo_recusa, soli_token, soli_dth_criacao, soli_foto_url, soli_comprovante_url)
            VALUES
              (:nome, :matricula, :email, :telefone, :escola, :turno, :endereco, :curso, :obs,
               :empr, 'PENDENTE', NULL, :token, NOW(), :foto, :comprovante)
        ";

        $db->query($sql);
        $db->bind(':nome',       $draft['nome']);
        $db->bind(':matricula',  $draft['turma']); // se você quiser usar matrícula mesmo, ajuste para o campo certo do seu form
        $db->bind(':email',      $draft['email']);
        $db->bind(':telefone',   $draft['telefone']);
        $db->bind(':escola',     $draft['escola']);
        $db->bind(':turno',      $draft['turno']);
        $db->bind(':endereco',   $draft['endereco']);
        $db->bind(':curso',      $draft['curso']);
        $db->bind(':obs',        null);
        $db->bind(':empr',       $empr_id);
        $db->bind(':token',      $token);
        $db->bind(':foto',       $draft['foto_url']);
        $db->bind(':comprovante',$draft['comprovante_url']);
        $db->executa();

        // Limpa rascunho
        unset($_SESSION['soli_draft']);

        // Vai para a página de status
        header('Location: ' . URL . '/alunos/status?t=' . urlencode($token));
        exit;
    }

    // 4) Tela de “Aguardando aprovação” (acompanha por token)
    public function status()
    {
        $t = $_GET['t'] ?? '';
        if ($t === '') {
            header('Location: ' . URL . '/paginas/home');
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();
        $db->query("SELECT * FROM solicitacao_aluno WHERE soli_token = :t LIMIT 1");
        $db->bind(':t', $t);
        $soli = $db->resultado();

        $this->view('paginas/solicitacao_status', ['soli' => $soli]);
    }

    /* Helpers */
    private static function saveUpload(array $file, string $dir, array $allowTypes, int $maxSize, &$savedName): bool
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return false;
        if ($file['error'] !== UPLOAD_ERR_OK) return false;
        if ($file['size'] > $maxSize) return false;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowTypes, true)) return false;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $savedName = time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        return move_uploaded_file($file['tmp_name'], $dir . '/' . $savedName);
    }

    private static function uuidv4(): string
    {
        $d = random_bytes(16);
        $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
        $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        $hex = bin2hex($d);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($hex, 4));
    }
}

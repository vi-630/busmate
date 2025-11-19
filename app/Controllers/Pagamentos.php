<?php
class Pagamentos extends Controllers {
    
    private function requireDb() {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        return new Database();
    }

    private function requireAlunoLogado() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 3) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
    }

    /**
     * POST /paginas/enviarComprovante
     * Aluno envia comprovante de pagamento para uma competência
     */
    public function enviarComprovante() {
        $this->requireAlunoLogado();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/contrato');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $contId = isset($_POST['cont_id']) ? intval($_POST['cont_id']) : 0;
        $competencia = isset($_POST['competencia']) ? trim($_POST['competencia']) : '';

        // Validações básicas
        if ($contId <= 0 || empty($competencia) || !preg_match('/^\d{4}-\d{2}$/', $competencia)) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Dados inválidos.'));
            exit;
        }

        $db = $this->requireDb();

        // Validar que o contrato pertence ao aluno
        $db->query("SELECT cont_id, usua_id FROM contrato WHERE cont_id = :id AND usua_id = :uid LIMIT 1");
        $db->bind(':id', $contId);
        $db->bind(':uid', $userId);
        $contrato = $db->resultado();

        if (!$contrato) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Contrato não encontrado.'));
            exit;
        }

        // Validar arquivo
        if (empty($_FILES['comprovante']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $_FILES['comprovante']['error'] === UPLOAD_ERR_NO_FILE 
                ? 'Arquivo não selecionado.' 
                : 'Erro ao fazer upload do arquivo.';
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode($errorMsg));
            exit;
        }

        $file = $_FILES['comprovante'];
        $maxSize = 10 * 1024 * 1024;

        if ($file['size'] > $maxSize) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Arquivo muito grande (máx 10 MB).'));
            exit;
        }

        // Validar MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'application/pdf'];
        if (!in_array($mime, $allowedMimes)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'pdf'];
            if (!in_array($ext, $allowedExts)) {
                header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Formato não permitido. Use PNG, JPG, WEBP ou PDF.'));
                exit;
            }
        }

        // Criar diretório de upload
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/comprovantes';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        if (!is_dir($uploadDir)) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Erro ao criar diretório de upload.'));
            exit;
        }

        // Gerar nome único para o arquivo
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $nomeArquivo = 'comprovante_' . $userId . '_' . $competencia . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = $uploadDir . '/' . $nomeArquivo;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            @file_put_contents(
                dirname(__DIR__) . '/debug_register.txt',
                '[' . date('Y-m-d H:i:s') . "] Erro ao mover arquivo de comprovante para $destino\n",
                FILE_APPEND
            );
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Falha ao salvar arquivo.'));
            exit;
        }

        // Caminho relativo
        $caminhoRelativo = 'public/uploads/comprovantes/' . $nomeArquivo;

        // Verificar se já existe pagamento
        $db->query("
            SELECT paga_id
            FROM pagamento
            WHERE usua_id = :uid AND cont_id = :cid AND paga_competencia = :comp
            LIMIT 1
        ");
        $db->bind(':uid', $userId);
        $db->bind(':cid', $contId);
        $db->bind(':comp', $competencia);
        $pagamentoExistente = $db->resultado();

        if ($pagamentoExistente) {
            // UPDATE
            $db->query("
                UPDATE pagamento
                SET paga_comprovante_url = :url,
                    paga_situacao = 'PENDENTE',
                    paga_dth = NOW()
                WHERE paga_id = :id
            ");
            $db->bind(':url', $caminhoRelativo);
            $db->bind(':id', $pagamentoExistente->paga_id);
        } else {
            // INSERT
            $db->query("
                SELECT cont_valor_total, empr_vlr_mensalidade
                FROM contrato c
                INNER JOIN empresa e ON e.empr_id = c.empr_id
                WHERE c.cont_id = :cid
                LIMIT 1
            ");
            $db->bind(':cid', $contId);
            $contratoFull = $db->resultado();

            $valor = $contratoFull->cont_valor_total ?? $contratoFull->empr_vlr_mensalidade ?? 0.00;

            $db->query("
                INSERT INTO pagamento (usua_id, cont_id, paga_competencia, paga_valor, paga_metodo, paga_situacao, paga_comprovante_url, paga_dth)
                VALUES (:uid, :cid, :comp, :valor, 'PIX', 'PENDENTE', :url, NOW())
            ");
            $db->bind(':uid', $userId);
            $db->bind(':cid', $contId);
            $db->bind(':comp', $competencia);
            $db->bind(':valor', $valor);
            $db->bind(':url', $caminhoRelativo);
        }

        try {
            $db->executa();
        } catch (Throwable $t) {
            @file_put_contents(
                dirname(__DIR__) . '/debug_register.txt',
                '[' . date('Y-m-d H:i:s') . "] Erro ao salvar comprovante no DB: " . $t->getMessage() . "\n",
                FILE_APPEND
            );
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Erro ao processar comprovante.'));
            exit;
        }

        header('Location: ' . URL . '/paginas/contrato?ok=' . urlencode('Comprovante enviado com sucesso! Aguarde a aprovação.'));
        exit;
    }

    /**
     * POST /pagamentos/aprovarPagamento
     * Admin aprova um pagamento
     */
    public function aprovarPagamento() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['paga_id'])) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        $pagaId = intval($_POST['paga_id']);
        $adminId = intval($_SESSION['user_id']);
        $db = $this->requireDb();

        // Validar que o admin é da empresa do contrato
        $db->query("
            SELECT p.paga_id, p.usua_id, c.empr_id
            FROM pagamento p
            INNER JOIN contrato c ON c.cont_id = p.cont_id
            WHERE p.paga_id = :pid
            LIMIT 1
        ");
        $db->bind(':pid', $pagaId);
        $pagamento = $db->resultado();

        if (!$pagamento) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        // Validar que o admin é da empresa
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $admin = $db->resultado();

        if (!$admin || $admin->empr_id !== $pagamento->empr_id) {
            header('Location: ' . URL . '/paginas/index_app');
            exit; 
        }

        // Aprovar pagamento — limpa eventual motivo de recusa
        $db->query("UPDATE pagamento
            SET paga_situacao = 'PAGO',
                paga_dth = NOW(),
                paga_motivo = NULL
            WHERE paga_id = :id");
        $db->bind(':id', $pagaId);
        $db->executa();

        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : URL . '/paginas/index_app';
        header('Location: ' . $redirect . '?ok=' . urlencode('Pagamento aprovado!'));
        exit;
    }

    /**
     * POST /pagamentos/recusarPagamento
     * Admin recusa um pagamento
     */
    public function recusarPagamento() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['paga_id'])) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        $pagaId = intval($_POST['paga_id']);
        $motivo = trim($_POST['motivo'] ?? '');
        $adminId = intval($_SESSION['user_id']);
        $db = $this->requireDb();

        // Validar que o admin é da empresa do contrato
        $db->query("
            SELECT p.paga_id, p.usua_id, c.empr_id
            FROM pagamento p
            INNER JOIN contrato c ON c.cont_id = p.cont_id
            WHERE p.paga_id = :pid
            LIMIT 1
        ");
        $db->bind(':pid', $pagaId);
        $pagamento = $db->resultado();

        if (!$pagamento) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        // Validar que o admin é da empresa
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $admin = $db->resultado();

        if (!$admin || $admin->empr_id !== $pagamento->empr_id) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        // Recusar pagamento — salva motivo para o aluno ver e permitir reenvio
        $db->query("UPDATE pagamento
            SET paga_situacao = 'FALHOU',
                paga_motivo = :motivo,
                paga_dth = NOW()
            WHERE paga_id = :id");
        $db->bind(':motivo', $motivo);
        $db->bind(':id', $pagaId);
        $db->executa();

        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : URL . '/paginas/index_app';
        header('Location: ' . $redirect . '?ok=' . urlencode('Pagamento recusado.'));
        exit;
    }
}
?>
<?php
class Contratos extends Controllers
{
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
     * PASSO: tela onde o aluno lê o contrato e envia documentos + contrato assinado.
     * GET /contratos/assinatura
     */
    public function assinatura() {
        $this->requireAlunoLogado();
        $usua_id = $_SESSION['user_id'];

        $db = $this->requireDb();

        // contrato mais recente do aluno
        $db->query("
    SELECT 
        c.*,
        e.empr_nome,
        e.empr_cnpj,
        e.empr_contrato_url,
        e.empr_pix_url,
        e.empr_chave_pix
    FROM contrato c
    INNER JOIN empresa e ON e.empr_id = c.empr_id
    WHERE c.usua_id = :id
    ORDER BY c.cont_dth_criacao DESC
    LIMIT 1
");

        $db->bind(':id', $usua_id);
        $contrato = $db->resultado();

        if (!$contrato || $contrato->cont_situacao !== 'RASCUNHO') {
            // se não tem contrato rascunho, volta pra tela principal
            header('Location: ' . URL . '/paginas/contrato');
            exit;
        }

        // documentos já enviados pelo aluno
        $db->query("
            SELECT *
            FROM documento
            WHERE usua_id = :id
            ORDER BY docu_dth_envio DESC, docu_id DESC
        ");
        $db->bind(':id', $usua_id);
        $documentos = $db->resultados();

        $this->view('contratos/assinatura', [
            'contrato'   => $contrato,
            'documentos' => $documentos
        ]);
    }

    /**
     * POST /contratos/salvarAssinatura
     * Recebe documentos + contrato assinado e grava na tabela documento.
     */
    public function salvarAssinatura() {
        $this->requireAlunoLogado();
        $usua_id = $_SESSION['user_id'];

        $db = $this->requireDb();

        // Confere se ainda é rascunho
        $db->query("SELECT cont_id, cont_situacao FROM contrato WHERE usua_id = :id ORDER BY cont_dth_criacao DESC LIMIT 1");
        $db->bind(':id', $usua_id);
        $c = $db->resultado();
        if (!$c || $c->cont_situacao !== 'RASCUNHO') {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Contrato não está mais em rascunho.'));
            exit;
        }

        $baseDir = dirname(__DIR__, 2) . '/public/uploads/documentos/alunos';
        if (!is_dir($baseDir)) @mkdir($baseDir, 0777, true);

        $maxSize = 5 * 1024 * 1024;
        $okTypes = [
            'application/pdf',
            'image/png',
            'image/jpeg',
            'image/jpg',
            'image/webp'
        ];

        $erros = [];
        $salvos = 0;

        // 1) documentos pessoais (múltiplos)
        if (!empty($_FILES['documentos']['name'][0])) {
            $names  = $_FILES['documentos']['name'];
            $types  = $_FILES['documentos']['type'];
            $tmp    = $_FILES['documentos']['tmp_name'];
            $errors = $_FILES['documentos']['error'];
            $sizes  = $_FILES['documentos']['size'];

            foreach ($names as $i => $origName) {
                if ($errors[$i] !== UPLOAD_ERR_OK) continue;
                if ($sizes[$i] > $maxSize) { $erros[] = "$origName: arquivo muito grande."; continue; }
                if (!in_array($types[$i], $okTypes)) { $erros[] = "$origName: formato não permitido."; continue; }

                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $novoNome = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $baseDir . '/' . $novoNome;

                if (move_uploaded_file($tmp[$i], $dest)) {
                    $relPath = 'public/uploads/documentos/alunos/' . $novoNome;
                    $db->query("
                        INSERT INTO documento (usua_id, docu_tipo, docu_nome_original, docu_url)
                        VALUES (:usua_id, :tipo, :nome, :url)
                    ");
                    $db->bind(':usua_id', $usua_id);
                    $db->bind(':tipo', 'DOC_PESSOAL');
                    $db->bind(':nome', $origName);
                    $db->bind(':url',  $relPath);
                    $db->executa();
                    $salvos++;
                }
            }
        }

        // 2) contrato assinado (arquivo único)
        if (!empty($_FILES['contrato_assinado']['name'])) {
            $f = $_FILES['contrato_assinado'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                if ($f['size'] > $maxSize) {
                    $erros[] = "Contrato assinado: arquivo muito grande.";
                } elseif (!in_array($f['type'], $okTypes)) {
                    $erros[] = "Contrato assinado: formato não permitido.";
                } else {
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    $novoNome = 'contrato_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = $baseDir . '/' . $novoNome;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $relPath = 'public/uploads/documentos/alunos/' . $novoNome;
                        $db->query("
                            INSERT INTO documento (usua_id, docu_tipo, docu_nome_original, docu_url)
                            VALUES (:usua_id, :tipo, :nome, :url)
                        ");
                        $db->bind(':usua_id', $usua_id);
                        $db->bind(':tipo', 'CONTRATO_ASSINADO');
                        $db->bind(':nome', $f['name']);
                        $db->bind(':url',  $relPath);
                        $db->executa();
                        $salvos++;
                    }
                }
            }
        }

        $msgOk   = $salvos > 0 ? "$salvos arquivo(s) enviados com sucesso." : '';
        $msgErro = $erros ? implode(' ', $erros) : '';

        $qs = [];
        if ($msgOk)   $qs[] = 'ok='   . urlencode($msgOk);
        if ($msgErro) $qs[] = 'erro=' . urlencode($msgErro);
        $qsStr = $qs ? ('?' . implode('&', $qs)) : '';

        header('Location: ' . URL . '/contratos/assinatura' . $qsStr);
        exit;
    }
}

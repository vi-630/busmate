<?php
class Solicitacoes extends Controllers
{
    private function db() {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        return new Database();
    }

    private function exigeAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
    }

    private function adminEmpresaId() {
        $db = $this->db();
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $_SESSION['user_id']);
        $r = $db->resultado();
        return $r ? intval($r->empr_id) : 0;
    }

    public function index() {
        $this->exigeAdmin();
        $empr_id = $this->adminEmpresaId();
        $map = ['pendente'=>'PENDENTE','aceita'=>'ACEITA','recusada'=>'RECUSADA'];
        $filtro = strtolower($_GET['f'] ?? 'pendente');
        $status = $map[$filtro] ?? 'PENDENTE';

        $solis = [];
        if ($empr_id > 0) {
            $db = $this->db();
            $db->query("
                SELECT s.*, e.empr_nome
                FROM solicitacao_aluno s
                INNER JOIN empresa e ON e.empr_id = s.empr_id
                WHERE s.empr_id = :empr AND s.soli_status = :st
                ORDER BY s.soli_dth_criacao DESC
            ");
            $db->bind(':empr', $empr_id);
            $db->bind(':st', $status);
            $solis = $db->resultados();
        }

        $this->view('paginas/solicitacoes_index', [
            'solis'  => $solis,
            'filtro' => $filtro
        ]);
    }

    public function ver() {
        $this->exigeAdmin();
        $empr_id = $this->adminEmpresaId();
        $id = intval($_GET['id'] ?? 0);
        if ($id<=0 || $empr_id<=0) { header('Location: ' . URL . '/solicitacoes/index'); exit; }

        $db = $this->db();
        $db->query("
            SELECT s.*, e.empr_nome
            FROM solicitacao_aluno s
            INNER JOIN empresa e ON e.empr_id = s.empr_id
            WHERE s.soli_id = :id AND s.empr_id = :empr
            LIMIT 1
        ");
        $db->bind(':id', $id);
        $db->bind(':empr', $empr_id);
        $sol = $db->resultado();
        if (!$sol) { header('Location: ' . URL . '/solicitacoes/index'); exit; }

        // Carrega documentos associados (solicitacao_documento)
        $db->query("SELECT sodo_tipo, sodo_url_tmp FROM solicitacao_documento WHERE soli_id = :id");
        $db->bind(':id', $id);
        $docsRows = $db->resultados() ?: [];
        $docs = [];
        foreach ($docsRows as $d) {
            $docs[$d->sodo_tipo] = $d->sodo_url_tmp;
        }

        // Carrega contrato mais recente do aluno com dados da empresa
        $contrato = null;
        if (!empty($sol->usua_id)) {
            $db->query("
                SELECT c.*, e.empr_contrato_url, e.empr_nome, e.empr_cnpj
                FROM contrato c
                INNER JOIN empresa e ON e.empr_id = c.empr_id
                WHERE c.usua_id = :uid
                ORDER BY c.cont_dth_criacao DESC
                LIMIT 1
            ");
            $db->bind(':uid', $sol->usua_id);
            $contrato = $db->resultado();
        }

        $this->view('paginas/solicitacao_detalhe', [
            'sol'      => $sol,
            'docs'     => $docs,
            'contrato' => $contrato
        ]);
    }

    public function decidir() {
    $this->exigeAdmin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . URL . '/solicitacoes/index');
        exit;
    }

    $empr_id = $this->adminEmpresaId();
    $soli_id = intval($_POST['soli_id'] ?? 0);
    $acao = strtoupper(trim($_POST['acao'] ?? ''));
    // Aceita tanto 'motivo' (legacy) quanto 'motivo_recusa' vindo do formulário
    $motivo  = trim(
        (isset($_POST['motivo_recusa']) ? $_POST['motivo_recusa'] : (isset($_POST['motivo']) ? $_POST['motivo'] : ''))
    );


    if ($soli_id <= 0 || $empr_id <= 0 || !in_array($acao, ['ACEITAR','RECUSAR'], true)) {
    header('Location: ' . URL . '/solicitacoes/ver?id=' . $soli_id . '&erro=' . urlencode('Parâmetros inválidos.'));
    exit;
    }


    $db = $this->db();

    try {
        // INÍCIO TRANSAÇÃO (sem getPdo)
        $db->query('START TRANSACTION'); 
        $db->executa();

        // Carrega a solicitação (e trava a linha, se quiser ser mais rígido) 
        $db->query("
            SELECT *
              FROM solicitacao_aluno
             WHERE soli_id = :id AND empr_id = :empr
             LIMIT 1
        ");
        $db->bind(':id', $soli_id);
        $db->bind(':empr', $empr_id);
        $sol = $db->resultado();

        if (!$sol) {
            // rollback
            $db->query('ROLLBACK'); $db->executa();
            header('Location: ' . URL . '/solicitacoes/index?erro=' . urlencode('Solicitação não encontrada.'));
            exit;
        }

        if ($acao === 'RECUSAR') {
            if ($motivo === '') $motivo = 'Sem justificativa informada.';
            
            // Deletar arquivos temporários antes de recusar
            // Foto temporária
            if (!empty($sol->soli_foto_url) && strpos($sol->soli_foto_url, '/tmp/') !== false) {
                $fotoPath = dirname(__DIR__, 2) . '/' . $sol->soli_foto_url;
                if (is_file($fotoPath)) @unlink($fotoPath);
            }
            
            // Documentos temporários
            $db->query("SELECT sodo_url_tmp FROM solicitacao_documento WHERE soli_id = :soli_id");
            $db->bind(':soli_id', $soli_id);
            $docsToDelete = $db->resultados() ?: [];
            
            foreach ($docsToDelete as $doc) {
                $docPath = dirname(__DIR__, 2) . '/' . $doc->sodo_url_tmp;
                if (is_file($docPath)) @unlink($docPath);
            }
            
            // Comprovante legado se houver
            if (!empty($sol->soli_comprovante_url) && strpos($sol->soli_comprovante_url, '/tmp/') !== false) {
                $comprovantePath = dirname(__DIR__, 2) . '/' . $sol->soli_comprovante_url;
                if (is_file($comprovantePath)) @unlink($comprovantePath);
            }
            
            $db->query("
                UPDATE solicitacao_aluno
                   SET soli_status     = 'RECUSADA',
                       motivo_recusa   = :motivo,
                       soli_dth_decisao= CURRENT_TIMESTAMP
                 WHERE soli_id = :id
                 LIMIT 1
            ");
            $db->bind(':motivo', $motivo);
            $db->bind(':id', $soli_id);
            $db->executa();

            // COMMIT e volta
            $db->query('COMMIT'); $db->executa();
            header('Location: ' . URL . '/solicitacoes/ver?id=' . $soli_id . '&ok=' . urlencode('Solicitação recusada.'));
            exit;
        }

        // === ACEITAR ===
        // 1) cria usuário aluno
        $senhaHash = $sol->soli_senha_hash ?: password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT);

        $db->query("
            INSERT INTO usuario (
                usua_nome, usua_turma, usua_curso, usua_escola, usua_turno, usua_endereco,
                usua_cpf, usua_foto, tius_id, usua_senha_hash, usua_matricula, usua_situacao,
                empr_id
            ) VALUES (
                :nome, :turma, :curso, :escola, :turno, :endereco,
                NULL, :foto, 3, :senha, :matricula, 'A',
                :empr
            )
        ");
        $db->bind(':nome', $sol->soli_nome);
        $db->bind(':turma', $sol->soli_turma);
        $db->bind(':curso', $sol->soli_curso);
        $db->bind(':escola', $sol->soli_escola);
        $db->bind(':turno', $sol->soli_turno);
        $db->bind(':endereco', $sol->soli_endereco);
        $db->bind(':foto', $sol->soli_foto_url ?: null);
        $db->bind(':senha', $senhaHash);
        // se você vier a ter campo de matrícula na solicitação, troque aqui:
        $db->bind(':matricula', null);
        $db->bind(':empr', $empr_id);
        $db->executa();

        $novoUserId = $db->ultimoIdInserido();
        if (!$novoUserId) {
            $db->query('ROLLBACK'); $db->executa();
            header('Location: ' . URL . '/solicitacoes/ver?id=' . $soli_id . '&erro=' . urlencode('Falha ao criar o usuário.'));
            exit;
        }

        // 2) email principal (se houver)
        if (!empty($sol->soli_email)) {
            $db->query("INSERT INTO email (emai_endereco, usua_id, is_principal) VALUES (:e, :u, 1)");
            $db->bind(':e', $sol->soli_email);
            $db->bind(':u', $novoUserId);
            $db->executa();
        }

        // 3) telefone(s) (se houver)
        if (!empty($sol->soli_tel)) {
            $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:t, :u, 'CEL', 1)");
            $db->bind(':t', $sol->soli_tel);
            $db->bind(':u', $novoUserId);
            $db->executa();
        }
        if (!empty($sol->soli_responsavel_tel)) {
            $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:t, :u, 'COM', 0)");
            $db->bind(':t', $sol->soli_responsavel_tel);
            $db->bind(':u', $novoUserId);
            $db->executa();
        }

        // 4) contrato (rascunho)
        $db->query("
            INSERT INTO contrato (empr_id, usua_id, cont_situacao)
            VALUES (:empr, :u, 'RASCUNHO')
        ");
        $db->bind(':empr', $empr_id);
        $db->bind(':u', $novoUserId);
        $db->executa();

        // 4b) MOVER FOTO DE tmp PARA PASTA FINAL
        $fotoFinalUrl = null;
        if (!empty($sol->soli_foto_url) && strpos($sol->soli_foto_url, '/tmp/') !== false) {
            $relTmp = $sol->soli_foto_url;
            $absTmp = dirname(__DIR__, 2) . '/' . $relTmp;

            if (is_file($absTmp)) {
                $destDir = dirname(__DIR__, 2) . '/public/uploads/usuarios_img';
                if (!is_dir($destDir)) @mkdir($destDir, 0777, true);
                $ext = pathinfo($absTmp, PATHINFO_EXTENSION);
                $nomeArq = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $to = $destDir . '/' . $nomeArq;
                
                if (@rename($absTmp, $to)) {
                    $fotoFinalUrl = 'public/uploads/usuarios_img/' . $nomeArq;
                    // Atualiza usuario.usua_foto com novo caminho
                    $db->query("UPDATE usuario SET usua_foto = :foto WHERE usua_id = :uid LIMIT 1");
                    $db->bind(':foto', $fotoFinalUrl);
                    $db->bind(':uid', $novoUserId);
                    $db->executa();
                }
            }
        }

        // 5) mover comprovante legado para pasta final (com fallback)
        if (!empty($sol->soli_comprovante_url)) {
            $relTmp = $sol->soli_comprovante_url; 
            $absTmp = dirname(__DIR__, 2) . '/' . $relTmp;

            if (!is_file($absTmp)) {
                $altRelTmp = str_replace('public/uploads/documentos/tmp/', 'public/uploads/usuarios_img/tmp/', $relTmp);
                $altAbsTmp = dirname(__DIR__, 2) . '/' . $altRelTmp;
                if (is_file($altAbsTmp)) {
                    $relTmp = $altRelTmp;
                    $absTmp = $altAbsTmp;
                }
            }

            if (is_file($absTmp)) {
                $destDir = dirname(__DIR__, 2) . '/public/uploads/documentos';
                if (!is_dir($destDir)) @mkdir($destDir, 0777, true);
                $ext = pathinfo($absTmp, PATHINFO_EXTENSION);
                $nomeArq = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $to = $destDir . '/' . $nomeArq;
                @rename($absTmp, $to);

                // Atualiza caminho final (opcional)
                $db->query("UPDATE solicitacao_aluno SET soli_comprovante_url = :r WHERE soli_id = :id LIMIT 1");
                $db->bind(':r', 'public/uploads/documentos/' . $nomeArq);
                $db->bind(':id', $soli_id);
                $db->executa();
            }
        }

        // 5b) mover NOVOS documentos (solicitacao_documento) para pasta final e criar documento
        $db->query("SELECT sodo_id, sodo_tipo, sodo_url_tmp FROM solicitacao_documento WHERE soli_id = :soli_id");
        $db->bind(':soli_id', $soli_id);
        $docsRows = $db->resultados() ?: [];

        foreach ($docsRows as $docRow) {
            $relTmp = $docRow->sodo_url_tmp;
            $absTmp = dirname(__DIR__, 2) . '/' . $relTmp;

            if (!is_file($absTmp)) {
                $altRelTmp = str_replace('public/uploads/documentos/tmp/', 'public/uploads/usuarios_img/tmp/', $relTmp);
                $altAbsTmp = dirname(__DIR__, 2) . '/' . $altRelTmp;
                if (is_file($altAbsTmp)) {
                    $relTmp = $altRelTmp;
                    $absTmp = $altAbsTmp;
                }
            }

            if (is_file($absTmp)) {
                $destDir = dirname(__DIR__, 2) . '/public/uploads/documentos';
                if (!is_dir($destDir)) @mkdir($destDir, 0777, true);
                $ext = pathinfo($absTmp, PATHINFO_EXTENSION);
                $nomeArq = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $to = $destDir . '/' . $nomeArq;
                @rename($absTmp, $to);

                $relFinal = 'public/uploads/documentos/' . $nomeArq;

                // Mapear tipo de documento para tipo_documento do banco
                $tipoMap = [
                    'COMPROVANTE_MATRICULA' => 'MATRICULA',
                    'COMPROVANTE_RESIDENCIA' => 'RESIDENCIA',
                    'RG_ALUNO' => 'RG',
                    'CPF_ALUNO' => 'CPF',
                    'DOC_RESPONSAVEL' => 'RESPONSAVEL'
                ];
                $tipoDoc = $tipoMap[$docRow->sodo_tipo] ?? $docRow->sodo_tipo;

                    // Inserir na tabela documento (colunas existentes: docu_*)
                    $db->query("
                        INSERT INTO documento (usua_id, docu_tipo, docu_nome_original, docu_url)
                        VALUES (:usua_id, :tipo, :nome, :url)
                    ");
                    $db->bind(':usua_id', $novoUserId);
                    $db->bind(':tipo', $tipoDoc);
                    $db->bind(':nome', basename($absTmp));
                    $db->bind(':url', $relFinal);
                    $db->executa();

                // Atualizar solicitacao_documento com novo caminho (opcional)
                $db->query("UPDATE solicitacao_documento SET sodo_url_tmp = :r WHERE sodo_id = :id");
                $db->bind(':r', $relFinal);
                $db->bind(':id', $docRow->sodo_id);
                $db->executa();
            }
        }

        // 6) marca solicitação como ACEITA
        $db->query("
            UPDATE solicitacao_aluno
               SET soli_status = 'ACEITA',
                   soli_dth_decisao = CURRENT_TIMESTAMP
             WHERE soli_id = :id
             LIMIT 1
        ");
        $db->bind(':id', $soli_id);
        $db->executa();

        // COMMIT
        $db->query('COMMIT'); 
        $db->executa();

        header('Location: ' . URL . '/solicitacoes/ver?id=' . $soli_id . '&ok=' . urlencode('Solicitação aceita.'));
        exit;

    } catch (Throwable $t) {
        // ROLLBACK de segurança
        try { $db->query('ROLLBACK'); $db->executa(); } catch(Throwable $e){}
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $soli_id . '&erro=' . urlencode('Falha ao decidir: ' . $t->getMessage()));
        exit;
    }
}


    // ===== NOVO: DOWNLOAD SEGURO DO COMPROVANTE =====
    // GET /solicitacoes/download_comprovante?id=123
public function download_comprovante() {
    // Mantive método antigo para compatibilidade com registros legados
    $this->exigeAdmin();
    $empr_id = $this->adminEmpresaId();
    $id = intval($_GET['id'] ?? 0);
    if ($id<=0 || $empr_id<=0) { header('Location: ' . URL . '/solicitacoes/index'); exit; }

    $db = $this->db();
    $db->query("SELECT soli_comprovante_url FROM solicitacao_aluno WHERE soli_id = :id AND empr_id = :empr LIMIT 1");
    $db->bind(':id', $id);
    $db->bind(':empr', $empr_id);
    $row = $db->resultado();

    if (!$row || empty($row->soli_comprovante_url)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Comprovante não encontrado.'));
        exit;
    }

    $rel = $row->soli_comprovante_url; // ex.: public/uploads/documentos/tmp/abc.png
    $abs = dirname(__DIR__, 2) . '/' . $rel;

    // Fallback: alguns casos antigos salvaram em usuarios_img/tmp
    if (!is_file($abs)) {
        $altRel = str_replace('public/uploads/documentos/tmp/', 'public/uploads/usuarios_img/tmp/', $rel);
        $altAbs = dirname(__DIR__, 2) . '/' . $altRel;

        if (is_file($altAbs)) {
            // Auto-reparo: mover para documentos/tmp e atualizar no banco
            $destDir = dirname(__DIR__, 2) . '/public/uploads/documentos/tmp';
            if (!is_dir($destDir)) @mkdir($destDir, 0777, true);
            $destName = basename($altAbs);
            $destAbs  = $destDir . '/' . $destName;
            if (@rename($altAbs, $destAbs)) {
                $rel = 'public/uploads/documentos/tmp/' . $destName;
                $abs = $destAbs;
                $db->query("UPDATE solicitacao_aluno SET soli_comprovante_url = :r WHERE soli_id = :id LIMIT 1");
                $db->bind(':r', $rel);
                $db->bind(':id', $id);
                $db->executa();
            } else {
                // falhou mover? então baixa diretamente do altAbs
                $abs = $altAbs;
            }
        }
    }

    if (!is_file($abs)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Arquivo ausente no servidor.'));
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $abs);
    finfo_close($finfo);

    $basename = basename($abs);
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $basename . '"');
    header('Content-Length: ' . filesize($abs));
    header('Cache-Control: no-store');
    readfile($abs);
    exit;
}


// Download genérico para documentos em solicitacao_documento
public function download_documento() {
    $this->exigeAdmin();
    $empr_id = $this->adminEmpresaId();
    $id = intval($_GET['id'] ?? 0);
    $tipo = trim((string)($_GET['tipo'] ?? ''));
    if ($id<=0 || $empr_id<=0 || $tipo === '') { header('Location: ' . URL . '/solicitacoes/index'); exit; }

    $db = $this->db();
    $db->query("SELECT d.sodo_url_tmp FROM solicitacao_documento d INNER JOIN solicitacao_aluno s ON s.soli_id = d.soli_id WHERE d.soli_id = :id AND d.sodo_tipo = :tipo AND s.empr_id = :empr LIMIT 1");
    $db->bind(':id', $id);
    $db->bind(':tipo', $tipo);
    $db->bind(':empr', $empr_id);
    $row = $db->resultado();

    if (!$row || empty($row->sodo_url_tmp)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Arquivo não encontrado.'));
        exit;
    }

    $rel = $row->sodo_url_tmp;
    $abs = dirname(__DIR__, 2) . '/' . $rel;

    // fallback para antigos paths
    if (!is_file($abs)) {
        $altRel = str_replace('public/uploads/documentos/tmp/', 'public/uploads/usuarios_img/tmp/', $rel);
        $altAbs = dirname(__DIR__, 2) . '/' . $altRel;
        if (is_file($altAbs)) $abs = $altAbs;
    }

    if (!is_file($abs)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Arquivo ausente no servidor.'));
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $abs);
    finfo_close($finfo);
    
    // Fallback se finfo falhar
    if ($mime === false || $mime === 'application/octet-stream') {
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    $basename = basename($abs);
    ob_end_clean();
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $basename . '"');
    header('Content-Length: ' . filesize($abs));
    header('Cache-Control: no-store');
    header('Pragma: no-cache');
    readfile($abs);
    exit;
}


// Visualizar documento (inline, no navegador)
public function visualizar_documento() {
    $this->exigeAdmin();
    $empr_id = $this->adminEmpresaId();
    $id = intval($_GET['id'] ?? 0);
    $tipo = trim((string)($_GET['tipo'] ?? ''));
    if ($id<=0 || $empr_id<=0 || $tipo === '') { header('Location: ' . URL . '/solicitacoes/index'); exit; }

    $db = $this->db();
    $db->query("SELECT d.sodo_url_tmp FROM solicitacao_documento d INNER JOIN solicitacao_aluno s ON s.soli_id = d.soli_id WHERE d.soli_id = :id AND d.sodo_tipo = :tipo AND s.empr_id = :empr LIMIT 1");
    $db->bind(':id', $id);
    $db->bind(':tipo', $tipo);
    $db->bind(':empr', $empr_id);
    $row = $db->resultado();

    if (!$row || empty($row->sodo_url_tmp)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Arquivo não encontrado.'));
        exit;
    }

    $rel = $row->sodo_url_tmp;
    $abs = dirname(__DIR__, 2) . '/' . $rel;

    // fallback para antigos paths
    if (!is_file($abs)) {
        $altRel = str_replace('public/uploads/documentos/tmp/', 'public/uploads/usuarios_img/tmp/', $rel);
        $altAbs = dirname(__DIR__, 2) . '/' . $altRel;
        if (is_file($altAbs)) $abs = $altAbs;
    }

    if (!is_file($abs)) {
        header('Location: ' . URL . '/solicitacoes/ver?id=' . $id . '&erro=' . urlencode('Arquivo ausente no servidor.'));
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $abs);
    finfo_close($finfo);
    
    // Fallback se finfo falhar
    if ($mime === false || $mime === 'application/octet-stream') {
        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    }

    // Content-Disposition: inline para visualizar no navegador
    ob_end_clean();
    header('Content-Type: ' . $mime);
    header('Content-Disposition: inline; filename="' . basename($abs) . '"');
    header('Content-Length: ' . filesize($abs));
    header('Cache-Control: no-store');
    header('Pragma: no-cache');
    readfile($abs);
    exit;
}

    /**
     * POST /solicitacoes/aprovarContrato
     * Admin aprova o contrato assinado de um aluno
     */
    public function aprovarContrato() {
        $this->exigeAdmin();

        if (empty($_POST['cont_id'])) {
            header('Location: ' . URL . '/solicitacoes');
            exit;
        }

        $cont_id = (int) $_POST['cont_id'];
        $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : null;
        $db = $this->db();

        // verifica que o contrato pertence à empresa do admin
        $db->query("
            SELECT c.cont_id, c.usua_id, c.empr_id
            FROM contrato c
            WHERE c.cont_id = :id LIMIT 1
        ");
        $db->bind(':id', $cont_id);
        $c = $db->resultado();

        if (!$c || $c->empr_id !== $this->adminEmpresaId()) {
            header('Location: ' . URL . '/solicitacoes');
            exit;
        }

        // atualizar contrato: ATIVO + data assinatura + início vigência + fim vigência (1 ano) + preencher valor (se nulo)
        $db->query("
            UPDATE contrato c
            INNER JOIN empresa e ON e.empr_id = c.empr_id
            SET c.cont_situacao = 'ATIVO',
                c.cont_dth_assinatura = NOW(),
                c.cont_inicio_vigencia = CURDATE(),
                c.cont_fim_vigencia = DATE_ADD(CURDATE(), INTERVAL 1 YEAR),
                c.cont_valor_total = COALESCE(c.cont_valor_total, e.empr_vlr_mensalidade),
                c.cont_motivo_recusa = NULL
            WHERE c.cont_id = :id
        ");
        $db->bind(':id', $cont_id);
        $db->executa();

        // Se houver redirect parameter, usa esse; senão, redireciona para solicitacoes/ver do aluno
        if (!empty($redirect)) {
            header('Location: ' . $redirect);
        } else {
            header('Location: ' . URL . '/solicitacoes/ver/' . $c->usua_id . '?ok=' . urlencode('Contrato aprovado!'));
        }
        exit;
    }

    /**
     * POST /solicitacoes/recusarContrato
     * Admin recusa o contrato assinado de um aluno
     */
    public function recusarContrato() {
        $this->exigeAdmin();

        if (empty($_POST['cont_id']) || empty($_POST['motivo'])) {
            header('Location: ' . URL . '/solicitacoes');
            exit;
        }

        $cont_id = (int) $_POST['cont_id'];
        $motivo = trim($_POST['motivo']);
        $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : null;
        
        if (strlen($motivo) > 255) {
            $motivo = substr($motivo, 0, 255);
        }

        $db = $this->db();

        // verifica que o contrato pertence à empresa do admin
        $db->query("
            SELECT c.cont_id, c.usua_id, c.empr_id
            FROM contrato c
            WHERE c.cont_id = :id LIMIT 1
        ");
        $db->bind(':id', $cont_id);
        $c = $db->resultado();

        if (!$c || $c->empr_id !== $this->adminEmpresaId()) {
            header('Location: ' . URL . '/solicitacoes');
            exit;
        }

        // atualizar contrato: volta para RASCUNHO + motivo
        $db->query("
            UPDATE contrato
            SET cont_situacao = 'RASCUNHO',
                cont_motivo_recusa = :motivo
            WHERE cont_id = :id
        ");
        $db->bind(':motivo', $motivo);
        $db->bind(':id', $cont_id);
        $db->executa();

        // Se houver redirect parameter, usa esse; senão, redireciona para solicitacoes/ver do aluno
        if (!empty($redirect)) {
            header('Location: ' . $redirect);
        } else {
            header('Location: ' . URL . '/solicitacoes/ver/' . $c->usua_id . '?ok=' . urlencode('Contrato recusado. Aluno deve reenviá-lo.'));
        }
        exit;
    }
}

<?php
class Forum extends Controllers {

    private function requireDb() {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        return new Database();
    }

    private function requireAuth() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
    }

    // POST /forum/criar
    public function criar() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/forum');
            exit;
        }

        $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
        $texto  = isset($_POST['conteudo']) ? trim($_POST['conteudo']) : '';

        if ($titulo === '' || $texto === '') {
            header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Título e conteúdo são obrigatórios.'));
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = intval($_SESSION['user_id']);

        try {
            $db = $this->requireDb();
            $db->query("INSERT INTO forum (foru_titulo, foru_texto, usua_id) VALUES (:titulo, :texto, :usua)");
            $db->bind(':titulo', $titulo);
            $db->bind(':texto', $texto);
            $db->bind(':usua', $userId);
            $db->executa();
            // pegar id inserido
            $newId = $db->ultimoId ?? null;
            // redirect para a página do fórum
            header('Location: ' . URL . '/paginas/forum#topic-' . ($newId ?: '')); 
            exit;
        } catch (Throwable $t) {
            @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Forum criar error: " . $t->getMessage() . "\n", FILE_APPEND);
            header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Erro ao criar tópico.'));
            exit;
        }
    }

    // POST /forum/responder/{id}
    public function responder($foruId = null) {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/forum');
            exit;
        }

        $foruId = intval($foruId ?: (isset($_GET['id']) ? $_GET['id'] : 0));
        $resposta = isset($_POST['resposta']) ? trim($_POST['resposta']) : '';

        if ($foruId <= 0 || $resposta === '') {
            header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Resposta inválida.'));
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = intval($_SESSION['user_id']);

        try {
            $db = $this->requireDb();
            // garantir que tópico existe e está ABERTO
            $db->query("SELECT foru_id FROM forum WHERE foru_id = :id AND foru_situacao = 'ABERTO' LIMIT 1");
            $db->bind(':id', $foruId);
            $exists = $db->resultado();
            if (!$exists) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Tópico não encontrado ou fechado.'));
                exit;
            }

            $db->query("INSERT INTO forum_resposta (fore_texto, foru_id, usua_id) VALUES (:texto, :foru, :usua)");
            $db->bind(':texto', $resposta);
            $db->bind(':foru', $foruId);
            $db->bind(':usua', $userId);
            $db->executa();

            header('Location: ' . URL . '/paginas/forum#topic-' . $foruId);
            exit;
        } catch (Throwable $t) {
            @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Forum responder error: " . $t->getMessage() . "\n", FILE_APPEND);
            header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Erro ao enviar resposta.'));
            exit;
        }
    }

    // GET /forum/respostas/{id} - AJAX para carregar respostas de um tópico
    public function respostas($foruId = null) {
        // Limpar o output buffer para evitar HTML do layout
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');

        if (empty($foruId)) $foruId = isset($_GET['id']) ? $_GET['id'] : 0;
        $foruId = intval($foruId);
        
        if ($foruId <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            exit;
        }

        try {
            $db = $this->requireDb();
            $db->query(
                "SELECT fr.fore_id, fr.fore_texto, fr.fore_dth_criacao,
                        u.usua_id, u.usua_nome, u.usua_foto
                 FROM forum_resposta fr
                 LEFT JOIN usuario u ON u.usua_id = fr.usua_id
                 WHERE fr.foru_id = :foru
                 ORDER BY fr.fore_dth_criacao ASC"
            );
            $db->bind(':foru', $foruId);
            $rows = $db->resultados() ?: [];
            
            $replies = [];
            foreach ($rows as $r) {
                $replies[] = [
                    'id' => $r->fore_id,
                    'texto' => $r->fore_texto,
                    'dth_criacao' => $r->fore_dth_criacao,
                    'autor_id' => $r->usua_id,
                    'autor_nome' => $r->usua_nome,
                    'autor_foto' => $r->usua_foto,
                ];
            }
            
            echo json_encode(['replies' => $replies]);
            exit;
        } catch (Throwable $t) {
            @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Forum respostas error: " . $t->getMessage() . "\n", FILE_APPEND);
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao carregar respostas: ' . $t->getMessage()]);
            exit;
        }
    }

    // POST /forum/editar/{id}
    public function editar($foruId = null) {
        $this->requireAuth();
        $foruId = intval($foruId ?: 0);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = intval($_SESSION['user_id']);

        $db = $this->requireDb();
        
        // verificar se é tópico ou resposta
        $isTopic = false;
        $itemData = null;
        
        $db->query("SELECT usua_id, foru_titulo, foru_texto FROM forum WHERE foru_id = :id LIMIT 1");
        $db->bind(':id', $foruId);
        $t = $db->resultado();
        
        if ($t) {
            $isTopic = true;
            $itemData = $t;
            if ($t->usua_id != $userId) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Sem permissão.'));
                exit;
            }
        } else {
            // é resposta?
            $db->query("SELECT usua_id, fore_texto FROM forum_resposta WHERE fore_id = :id LIMIT 1");
            $db->bind(':id', $foruId);
            $r = $db->resultado();
            if (!$r || $r->usua_id != $userId) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Sem permissão.'));
                exit;
            }
            $itemData = $r;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $texto = isset($_POST['texto']) ? trim($_POST['texto']) : '';
            if (empty($texto)) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Texto é obrigatório.'));
                exit;
            }

            try {
                if ($isTopic) {
                    $db->query("UPDATE forum SET foru_texto = :texto, foru_dth_atualizacao = NOW() WHERE foru_id = :id");
                } else {
                    $db->query("UPDATE forum_resposta SET fore_texto = :texto, fore_dth_atualizacao = NOW() WHERE fore_id = :id");
                }
                $db->bind(':texto', $texto);
                $db->bind(':id', $foruId);
                $db->executa();
                
                header('Location: ' . URL . '/paginas/forum?ok=' . urlencode('Atualizado com sucesso.'));
                exit;
            } catch (Throwable $t) {
                @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Forum editar error: " . $t->getMessage() . "\n", FILE_APPEND);
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Erro ao atualizar.'));
                exit;
            }
        }

        // GET - exibir form de edição
        $this->view('paginas/forum_editar', [
            'id' => $foruId,
            'isTopic' => $isTopic,
            'titulo' => $isTopic ? $itemData->foru_titulo : null,
            'texto' => $isTopic ? $itemData->foru_texto : $itemData->fore_texto,
        ]);
    }

    // GET /forum/excluir/{id}
    public function excluir($id = null) {
        $this->requireAuth();
        $id = intval($id ?: 0);
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = intval($_SESSION['user_id']);

        $db = $this->requireDb();
        
        // verificar se é tópico ou resposta e se pertence ao usuário
        $isTopic = false;
        $db->query("SELECT usua_id FROM forum WHERE foru_id = :id LIMIT 1");
        $db->bind(':id', $id);
        $t = $db->resultado();
        
        if ($t) {
            if ($t->usua_id != $userId) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Sem permissão.'));
                exit;
            }
            $isTopic = true;
        } else {
            $db->query("SELECT usua_id FROM forum_resposta WHERE fore_id = :id LIMIT 1");
            $db->bind(':id', $id);
            $r = $db->resultado();
            if (!$r || $r->usua_id != $userId) {
                header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Sem permissão.'));
                exit;
            }
        }

        try {
            if ($isTopic) {
                $db->query("DELETE FROM forum WHERE foru_id = :id");
            } else {
                $db->query("DELETE FROM forum_resposta WHERE fore_id = :id");
            }
            $db->bind(':id', $id);
            $db->executa();
            
            header('Location: ' . URL . '/paginas/forum?ok=' . urlencode('Excluído com sucesso.'));
            exit;
        } catch (Throwable $t) {
            @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Forum excluir error: " . $t->getMessage() . "\n", FILE_APPEND);
            header('Location: ' . URL . '/paginas/forum?erro=' . urlencode('Erro ao excluir.'));
            exit;
        }
    }

}
?>
<?php
class Avisos extends Controllers {

    private function requireDb() {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        return new Database();
    }

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
    }

    /**
     * GET /avisos
     * Lista todos os avisos
     */
    public function index() {
        $this->requireAdmin();
        
        $db = $this->requireDb();
        
        // Obter empr_id do admin logado
        $adminId = (int) $_SESSION['user_id'];
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $adminRow = $db->resultado();
        $emprId = ($adminRow && !empty($adminRow->empr_id)) ? intval($adminRow->empr_id) : 0;
        
        // Buscar por query de busca se existir
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $sql = "SELECT * FROM aviso WHERE empr_id = :empr_id";
        
        if (!empty($q)) {
            $sql .= " AND (avis_titulo LIKE :q OR avis_texto LIKE :q)";
        }
        
        $sql .= " ORDER BY avis_dth_criacao DESC";
        
        $db->query($sql);
        $db->bind(':empr_id', $emprId);
        if (!empty($q)) {
            $db->bind(':q', '%' . $q . '%');
        }
        $avisos = $db->resultados();

        $this->view('paginas/avisos_index', ['avisos' => $avisos ?? []]);
    }

    /**
     * POST /avisos/cadastrar
     * Cria um novo aviso
     */
    public function cadastrar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/avisos');
            exit;
        }

        $titulo = isset($_POST['avis_titulo']) ? trim($_POST['avis_titulo']) : '';
        $texto = isset($_POST['avis_texto']) ? trim($_POST['avis_texto']) : '';
        $situacao = isset($_POST['avis_situacao']) ? trim($_POST['avis_situacao']) : 'ATIVO';
        $publicaEm = isset($_POST['avis_publica_em']) && !empty($_POST['avis_publica_em']) ? $_POST['avis_publica_em'] : null;
        $expiraEm = isset($_POST['avis_expira_em']) && !empty($_POST['avis_expira_em']) ? $_POST['avis_expira_em'] : null;
        $usuaId = (int) $_SESSION['user_id'];
            // determinar empr_id do usuário que está criando o aviso
            try {
                $db = $this->requireDb();
                $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
                $db->bind(':id', $usuaId);
                $urow = $db->resultado();
                $emprId = ($urow && !empty($urow->empr_id)) ? intval($urow->empr_id) : null;
            } catch (Throwable $t) {
                $emprId = null;
            }

        // Validações
        if (empty($texto)) {
            header('Location: ' . URL . '/paginas/cadastro_aviso?erro=' . urlencode('Texto do aviso é obrigatório.'));
            exit;
        }

        if (!in_array($situacao, ['ATIVO', 'INATIVO'])) {
            $situacao = 'ATIVO';
        }

        // Converter datas para formato MySQL
        if ($publicaEm) {
            $publicaEm = date('Y-m-d H:i:s', strtotime($publicaEm));
        } else {
            $publicaEm = date('Y-m-d H:i:s');
        }

        if ($expiraEm) {
            $expiraEm = date('Y-m-d H:i:s', strtotime($expiraEm));
        }

        $db = $this->requireDb();

        try {
            $db->query("
                    INSERT INTO aviso (avis_titulo, avis_texto, usua_id, empr_id, avis_situacao, avis_publica_em, avis_expira_em)
                VALUES (:titulo, :texto, :usua_id, :empr_id, :situacao, :publica_em, :expira_em)
            ");
            $db->bind(':titulo', $titulo ?: null);
            $db->bind(':texto', $texto);
            $db->bind(':usua_id', $usuaId);
                $db->bind(':empr_id', $emprId);
            $db->bind(':situacao', $situacao);
            $db->bind(':publica_em', $publicaEm);
            $db->bind(':expira_em', $expiraEm);
            $db->executa();

            header('Location: ' . URL . '/avisos?ok=' . urlencode('Aviso publicado com sucesso!'));
            exit;
        } catch (Throwable $t) {
            @file_put_contents(
                dirname(__DIR__) . '/debug_register.txt',
                '[' . date('Y-m-d H:i:s') . "] Erro ao cadastrar aviso: " . $t->getMessage() . "\n",
                FILE_APPEND
            );
            header('Location: ' . URL . '/paginas/cadastro_aviso?erro=' . urlencode('Erro ao publicar aviso.'));
            exit;
        }
    }

    /**
     * POST /avisos/atualizar
     * Atualiza um aviso existente
     */
    public function atualizar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['avis_id'])) {
            header('Location: ' . URL . '/avisos');
            exit;
        }

        $avisId = (int) $_POST['avis_id'];
        $adminId = (int) $_SESSION['user_id'];
        
        // Verificar que o aviso pertence à empresa do admin
        $db = $this->requireDb();
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $adminRow = $db->resultado();
        $adminEmprId = ($adminRow && !empty($adminRow->empr_id)) ? intval($adminRow->empr_id) : 0;
        
        $db->query("SELECT avis_id, empr_id FROM aviso WHERE avis_id = :avis_id LIMIT 1");
        $db->bind(':avis_id', $avisId);
        $avisRow = $db->resultado();
        
        if (!$avisRow || intval($avisRow->empr_id) !== $adminEmprId) {
            header('Location: ' . URL . '/avisos');
            exit;
        }
        
        $titulo = isset($_POST['avis_titulo']) ? trim($_POST['avis_titulo']) : '';
        $texto = isset($_POST['avis_texto']) ? trim($_POST['avis_texto']) : '';
        $situacao = isset($_POST['avis_situacao']) ? trim($_POST['avis_situacao']) : 'ATIVO';
        $publicaEm = isset($_POST['avis_publica_em']) && !empty($_POST['avis_publica_em']) ? $_POST['avis_publica_em'] : null;
        $expiraEm = isset($_POST['avis_expira_em']) && !empty($_POST['avis_expira_em']) ? $_POST['avis_expira_em'] : null;

        // Validações
        if (empty($texto)) {
            header('Location: ' . URL . '/paginas/editar_aviso?id=' . $avisId . '&erro=' . urlencode('Texto do aviso é obrigatório.'));
            exit;
        }

        if (!in_array($situacao, ['ATIVO', 'INATIVO'])) {
            $situacao = 'ATIVO';
        }

        // Converter datas para formato MySQL
        if ($publicaEm) {
            $publicaEm = date('Y-m-d H:i:s', strtotime($publicaEm));
        }

        if ($expiraEm) {
            $expiraEm = date('Y-m-d H:i:s', strtotime($expiraEm));
        }

        try {
            $db->query("
                UPDATE aviso
                SET avis_titulo = :titulo,
                    avis_texto = :texto,
                    avis_situacao = :situacao,
                    avis_publica_em = :publica_em,
                    avis_expira_em = :expira_em
                WHERE avis_id = :id
            ");
            $db->bind(':titulo', $titulo ?: null);
            $db->bind(':texto', $texto);
            $db->bind(':situacao', $situacao);
            $db->bind(':publica_em', $publicaEm);
            $db->bind(':expira_em', $expiraEm);
            $db->bind(':id', $avisId);
            $db->executa();

            header('Location: ' . URL . '/avisos?ok=' . urlencode('Aviso atualizado com sucesso!'));
            exit;
        } catch (Throwable $t) {
            @file_put_contents(
                dirname(__DIR__) . '/debug_register.txt',
                '[' . date('Y-m-d H:i:s') . "] Erro ao atualizar aviso: " . $t->getMessage() . "\n",
                FILE_APPEND
            );
            header('Location: ' . URL . '/paginas/editar_aviso?id=' . $avisId . '&erro=' . urlencode('Erro ao atualizar aviso.'));
            exit;
        }
    }

    /**
     * GET /avisos/excluir?id=X
     * Deleta um aviso
     */
    public function excluir() {
        $this->requireAdmin();

        if (empty($_GET['id'])) {
            header('Location: ' . URL . '/avisos');
            exit;
        }

        $avisId = (int) $_GET['id'];
        $adminId = (int) $_SESSION['user_id'];
        
        $db = $this->requireDb();
        
        // Verificar que o aviso pertence à empresa do admin
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $adminRow = $db->resultado();
        $adminEmprId = ($adminRow && !empty($adminRow->empr_id)) ? intval($adminRow->empr_id) : 0;
        
        $db->query("SELECT avis_id, empr_id FROM aviso WHERE avis_id = :avis_id LIMIT 1");
        $db->bind(':avis_id', $avisId);
        $avisRow = $db->resultado();
        
        if (!$avisRow || intval($avisRow->empr_id) !== $adminEmprId) {
            header('Location: ' . URL . '/avisos');
            exit;
        }

        try {
            $db->query("DELETE FROM aviso WHERE avis_id = :id");
            $db->bind(':id', $avisId);
            $db->executa();

            header('Location: ' . URL . '/avisos?ok=' . urlencode('Aviso deletado com sucesso!'));
            exit;
        } catch (Throwable $t) {
            @file_put_contents(
                dirname(__DIR__) . '/debug_register.txt',
                '[' . date('Y-m-d H:i:s') . "] Erro ao deletar aviso: " . $t->getMessage() . "\n",
                FILE_APPEND
            );
            header('Location: ' . URL . '/avisos?erro=' . urlencode('Erro ao deletar aviso.'));
            exit;
        }
    }
}
?>

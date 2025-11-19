<?php
class Viagens extends Controllers {

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

    // GET /viagens/minhas
    public function minhas() {
        $this->requireAdmin();

        if (session_status() === PHP_SESSION_NONE) session_start();
        $adminId = (int) $_SESSION['user_id'];

        $db = $this->requireDb();

        // pegar empresa do admin
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $u = $db->resultado();
        $horarios = [];
        $onibusList = [];

        if ($u && !empty($u->empr_id)) {
            $emprId = intval($u->empr_id);

            // Buscar horários
            $db->query(
                "SELECT h.*, o.onib_modelo, o.onib_placa, o.onib_foto
                 FROM horario h
                 LEFT JOIN onibus o ON o.onib_id = h.onib_id
                 WHERE h.empr_id = :empr
                 ORDER BY h.hori_dth_criacao DESC"
            );
            $db->bind(':empr', $emprId);
            $horarios = $db->resultados() ?: [];

            // Buscar ônibus da empresa
            $db->query("SELECT * FROM onibus WHERE empr_id = :empr ORDER BY onib_situacao, onib_modelo");
            $db->bind(':empr', $emprId);
            $onibusList = $db->resultados() ?: [];
        }

        $this->view('paginas/viagens_minhas', ['horarios' => $horarios, 'onibusList' => $onibusList]);
    }

    // GET /viagens/cadastro  (form new)
    public function cadastro() {
        $this->requireAdmin();
        // exibe formulário em branco
        // buscar onibus disponíveis para a empresa do admin
        if (session_status() === PHP_SESSION_NONE) session_start();
        $adminId = (int) $_SESSION['user_id'];
        $db = $this->requireDb();
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $u = $db->resultado();
        $onibus = [];
        if ($u && !empty($u->empr_id)) {
            $emprId = intval($u->empr_id);
            // Corrige sessão para permitir cadastro de ônibus via viagens
            if (!isset($_SESSION['empresa_id']) || $_SESSION['empresa_id'] != $emprId) {
                $_SESSION['empresa_id'] = $emprId;
            }
            $db->query("SELECT onib_id, onib_modelo, onib_placa, onib_foto FROM onibus WHERE empr_id = :empr AND onib_situacao = 'ATIVO' ORDER BY onib_modelo");
            $db->bind(':empr', $emprId);
            $onibus = $db->resultados() ?: [];
        }

        $this->view('paginas/viagens_cadastro', ['dados' => [], 'onibusList' => $onibus]);
    }

    // GET /viagens/editar?id=XX
    public function editar() {
        $this->requireAdmin();
        if (empty($_GET['id'])) {
            header('Location: ' . URL . '/viagens/minhas');
            exit;
        }

        $horiId = (int) $_GET['id'];

        if (session_status() === PHP_SESSION_NONE) session_start();
        $adminId = (int) $_SESSION['user_id'];

        $db = $this->requireDb();
        // garantir que o horário pertence à mesma empresa do admin
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $u = $db->resultado();
        if (!$u || empty($u->empr_id)) {
            header('Location: ' . URL . '/viagens/minhas');
            exit;
        }
        $emprId = intval($u->empr_id);

        $db->query("SELECT * FROM horario WHERE hori_id = :id AND empr_id = :empr LIMIT 1");
        $db->bind(':id', $horiId);
        $db->bind(':empr', $emprId);
        $h = $db->resultado();

        if (!$h) {
            header('Location: ' . URL . '/viagens/minhas?erro=' . urlencode('Horário não encontrado.'));
            exit;
        }

        // transformar em array para a view reutilizar campos
            $dados = [];
            foreach ($h as $k => $v) {
                $dados[$k] = $v;
            }

        // buscar onibus da empresa para popular select
        $db->query("SELECT onib_id, onib_modelo, onib_placa, onib_foto FROM onibus WHERE empr_id = :empr ORDER BY onib_modelo");
        $db->bind(':empr', $emprId);
        $onibus = $db->resultados() ?: [];

        $this->view('paginas/viagens_cadastro', ['dados' => $dados, 'hori_id' => $horiId, 'onibusList' => $onibus]);
    }

    // POST /viagens/salvar
    public function salvar() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/viagens/minhas');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $adminId = (int) $_SESSION['user_id'];

        $horiId = isset($_POST['hori_id']) ? intval($_POST['hori_id']) : 0;
        $titulo = isset($_POST['hori_titulo']) ? trim($_POST['hori_titulo']) : '';
        $turno = isset($_POST['hori_turno']) ? trim($_POST['hori_turno']) : 'MANHA';
        $horaIda = isset($_POST['hori_hora_ida']) ? $_POST['hori_hora_ida'] : null;
        $horaVolta = isset($_POST['hori_hora_volta']) && $_POST['hori_hora_volta'] !== '' ? $_POST['hori_hora_volta'] : null;
        $ponto = isset($_POST['hori_ponto']) ? trim($_POST['hori_ponto']) : null;
        $dias = isset($_POST['hori_dias']) ? trim($_POST['hori_dias']) : null;
        $onibId = isset($_POST['onib_id']) && $_POST['onib_id'] !== '' ? intval($_POST['onib_id']) : null;
        $situacao = isset($_POST['hori_situacao']) ? trim($_POST['hori_situacao']) : 'ATIVO';

        if (empty($titulo) || empty($horaIda)) {
            header('Location: ' . URL . '/viagens/cadastro?erro=' . urlencode('Título e hora de saída são obrigatórios.'));
            exit;
        }

        $db = $this->requireDb();
        // pegar empr_id do admin
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $u = $db->resultado();
        $emprId = ($u && !empty($u->empr_id)) ? intval($u->empr_id) : null;

        if (!$emprId) {
            header('Location: ' . URL . '/viagens/minhas?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        try {
            if ($horiId > 0) {
                $db->query(
                    "UPDATE horario SET onib_id = :onib, hori_titulo = :titulo, hori_turno = :turno, hori_hora_ida = :ida, hori_hora_volta = :volta, hori_ponto = :ponto, hori_dias = :dias, hori_situacao = :sit WHERE hori_id = :id AND empr_id = :empr"
                );
                $db->bind(':onib', $onibId);
                $db->bind(':titulo', $titulo);
                $db->bind(':turno', $turno);
                $db->bind(':ida', $horaIda);
                $db->bind(':volta', $horaVolta);
                $db->bind(':ponto', $ponto);
                $db->bind(':dias', $dias);
                $db->bind(':sit', $situacao);
                $db->bind(':id', $horiId);
                $db->bind(':empr', $emprId);
                $db->executa();

                header('Location: ' . URL . '/viagens/minhas?ok=' . urlencode('Horário atualizado com sucesso.'));
                exit;
            } else {
                $db->query(
                    "INSERT INTO horario (empr_id, onib_id, hori_titulo, hori_turno, hori_hora_ida, hori_hora_volta, hori_ponto, hori_dias, hori_situacao) VALUES (:empr, :onib, :titulo, :turno, :ida, :volta, :ponto, :dias, :sit)"
                );
                $db->bind(':empr', $emprId);
                $db->bind(':onib', $onibId);
                $db->bind(':titulo', $titulo);
                $db->bind(':turno', $turno);
                $db->bind(':ida', $horaIda);
                $db->bind(':volta', $horaVolta);
                $db->bind(':ponto', $ponto);
                $db->bind(':dias', $dias);
                $db->bind(':sit', $situacao);
                $db->executa();

                header('Location: ' . URL . '/viagens/minhas?ok=' . urlencode('Horário cadastrado com sucesso.'));
                exit;
            }
        } catch (Throwable $t) {
            @file_put_contents(dirname(__DIR__) . '/debug_register.txt', '[' . date('Y-m-d H:i:s') . "] Viagens salvar error: " . $t->getMessage() . "\n", FILE_APPEND);
            header('Location: ' . URL . '/viagens/minhas?erro=' . urlencode('Erro ao salvar horário.'));
            exit;
        }
    }

    // GET /viagens/excluir?id=XX
    public function excluir() {
        $this->requireAdmin();
        if (empty($_GET['id'])) {
            header('Location: ' . URL . '/viagens/minhas');
            exit;
        }

        $horiId = (int) $_GET['id'];
        if (session_status() === PHP_SESSION_NONE) session_start();
        $adminId = (int) $_SESSION['user_id'];

        $db = $this->requireDb();
        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $u = $db->resultado();
        $emprId = ($u && !empty($u->empr_id)) ? intval($u->empr_id) : null;

        if (!$emprId) {
            header('Location: ' . URL . '/viagens/minhas?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        try {
            $db->query("DELETE FROM horario WHERE hori_id = :id AND empr_id = :empr");
            $db->bind(':id', $horiId);
            $db->bind(':empr', $emprId);
            $db->executa();

            header('Location: ' . URL . '/viagens/minhas?ok=' . urlencode('Horário excluído.'));
            exit;
        } catch (Throwable $t) {
            header('Location: ' . URL . '/viagens/minhas?erro=' . urlencode('Erro ao excluir horário.'));
            exit;
        }
    }

}
?>

<?php
class Onibus extends Controllers {

    private function requireAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user_id']) ||
            empty($_SESSION['user_tipo']) ||
            intval($_SESSION['user_tipo']) !== 2) {

            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
    }

    private function getEmpresaId() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Se já tiver em sessão, reaproveita
        if (!empty($_SESSION['empresa_id'])) {
            return (int) $_SESSION['empresa_id'];
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $_SESSION['user_id']);
        $u = $db->resultado();

        if (!$u || empty($u->empr_id)) {
            return null;
        }

        $_SESSION['empresa_id'] = (int) $u->empr_id;
        return (int) $u->empr_id;
    }

    public function index() {
        $this->requireAdmin();
        $emprId = $this->getEmpresaId();

        if (!$emprId) {
            header('Location: ' . URL . '/paginas/index_app?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        $db->query("SELECT * FROM onibus WHERE empr_id = :e ORDER BY onib_situacao, onib_modelo");
        $db->bind(':e', $emprId);
        $onibus = $db->resultados();

        $dados = ['onibus' => $onibus ?: []];
        $this->view('paginas/onibus_lista', $dados);
    }

    public function novo() {
        $this->requireAdmin();
        $emprId = $this->getEmpresaId();

        if (!$emprId) {
            header('Location: ' . URL . '/paginas/index_app?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        $dados = ['id' => null, 'onibus' => null];
        $this->view('paginas/onibus_form', $dados);
    }

    public function editar($id) {
        $this->requireAdmin();
        $emprId = $this->getEmpresaId();

        if (!$emprId) {
            header('Location: ' . URL . '/onibus?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // garante que o ônibus é da empresa do admin
        $db->query("SELECT * FROM onibus WHERE onib_id = :id AND empr_id = :empr LIMIT 1");
        $db->bind(':id', $id);
        $db->bind(':empr', $emprId);
        $onib = $db->resultado();

        if (!$onib) {
            header('Location: ' . URL . '/onibus?erro=' . urlencode('Ônibus não encontrado.'));
            exit;
        }

        $dados = ['id' => $id, 'onibus' => $onib];
        $this->view('paginas/onibus_form', $dados);
    }

    public function salvar() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header('Location: ' . URL . '/onibus');
            exit;
        }

        $emprId = $this->getEmpresaId();
        if (!$emprId) {
            header('Location: ' . URL . '/onibus?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        $id     = $_POST['onib_id']       ?? null;
        $modelo = trim($_POST['onib_modelo'] ?? '');
        $placa  = trim($_POST['onib_placa']  ?? '');
        $sit    = $_POST['onib_situacao']    ?? 'ATIVO';

        // Upload da foto do ônibus (opcional)
        $fotoPath = null;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/onibus_img';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        if (!empty($_FILES['onib_foto']['name'])) {
            $f = $_FILES['onib_foto'];
            if ($f['error'] === UPLOAD_ERR_OK) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg','image/png','image/webp'];
                if (in_array($mime, $allowed) && $f['size'] <= 5 * 1024 * 1024) {
                    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                    $newName = time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
                    $dest = $uploadDir.'/'.$newName;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $fotoPath = 'public/uploads/onibus_img/'.$newName;
                    }
                }
            }
        }

        if ($modelo === '' || $placa === '') {
            header('Location: ' . URL . '/onibus?erro=' . urlencode('Modelo e placa são obrigatórios.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        if ($id) {
            // update garantindo empresa
            $sql = "UPDATE onibus SET onib_modelo = :m, onib_placa = :p, onib_situacao = :s";
            if ($fotoPath) {
                $sql .= ", onib_foto = :f";
            }
            $sql .= " WHERE onib_id = :id AND empr_id = :e";
            $db->query($sql);
            $db->bind(':id', $id);
            $db->bind(':e',  $emprId);
        } else {
            // insert com empr_id correto
            if ($fotoPath) {
                $db->query("INSERT INTO onibus (empr_id, onib_modelo, onib_placa, onib_situacao, onib_foto) VALUES (:e, :m, :p, :s, :f)");
            } else {
                $db->query("INSERT INTO onibus (empr_id, onib_modelo, onib_placa, onib_situacao) VALUES (:e, :m, :p, :s)");
            }
            $db->bind(':e', $emprId);
        }

        $db->bind(':m', $modelo);
        $db->bind(':p', $placa);
        $db->bind(':s', $sit);
        if ($fotoPath) {
            $db->bind(':f', $fotoPath);
        }

        $db->executa();

        header('Location: ' . URL . '/viagens/minhas?ok=' . urlencode('Ônibus salvo com sucesso.'));
        exit;
    }

    public function deletar($id) {
        $this->requireAdmin();
        $emprId = $this->getEmpresaId();

        if (!$emprId) {
            header('Location: ' . URL . '/onibus?erro=' . urlencode('Empresa não encontrada.'));
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // deleta só se for da empresa
        $db->query("DELETE FROM onibus WHERE onib_id = :id AND empr_id = :e");
        $db->bind(':id', $id);
        $db->bind(':e',  $emprId);
        $db->executa();

        header('Location: ' . URL . '/viagens/minhas?ok=' . urlencode('Ônibus excluído.'));
        exit;
    }
}

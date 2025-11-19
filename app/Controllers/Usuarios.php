<?php
require_once dirname(__DIR__) . '/Libraries/Database.php';
require_once dirname(__DIR__) . '/Controllers/Usuario.php';

class Usuarios {
    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome'      => $_POST['nome'] ?? null,
                'cpf'       => $_POST['cpf'] ?? null,
                'senha'     => $_POST['senha'] ?? null,
                'tius_id'   => $_POST['tius_id'] ?? 3,
                'curso'     => $_POST['curso'] ?? null,
                'turma'     => $_POST['turma'] ?? null,
                'turno'     => $_POST['turno'] ?? null,
                'endereco'  => $_POST['endereco'] ?? null,
                'escola'    => $_POST['escola'] ?? null
            ];

            if (isset($dados['tius_id']) && intval($dados['tius_id']) === 2) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
                    header('Location: ' . URL . '/paginas/entrar?error=forbidden');
                    exit;
                }
            }

            if (isset($dados['tius_id']) && intval($dados['tius_id']) === 3) {
                $missing = null;
                $required = [
                    'nome','escola','curso','telefone','turma','turno','endereco','email','senha'
                ];
                foreach ($required as $r) {
                    $v = trim((string)($_POST[$r] ?? ''));
                    if ($v === '') { $missing = $r; break; }
                }
                if ($missing) {
                    header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Campo obrigatório ausente: ' . $missing));
                    exit;
                }
                if (empty($_FILES['comprovante']) || !isset($_FILES['comprovante']['error']) || $_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
                    header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Por favor, envie o comprovante de matrícula.'));
                    exit;
                }
            }
            
            $logPath = dirname(__DIR__) . '/debug_register.txt';
            $log = "[" . date('Y-m-d H:i:s') . "] Dados recebidos:\n";
            $log .= "POST: " . print_r($_POST, true) . "\n";
            $log .= "FILES: " . print_r($_FILES, true) . "\n";
            file_put_contents($logPath, $log, FILE_APPEND);

            $uploadBase = dirname(__DIR__, 2) . '/public/uploads';
            if (!is_dir($uploadBase)) mkdir($uploadBase, 0755, true);
            
            $fotosDir = $uploadBase . '/usuarios_img';
            if (!is_dir($fotosDir)) mkdir($fotosDir, 0755, true);
            
            $docsDir = $uploadBase . '/documentos';
            if (!is_dir($docsDir)) mkdir($docsDir, 0755, true);
            
            $logPath = dirname(__DIR__) . '/debug_register.txt';
            $log = "[" . date('Y-m-d H:i:s') . "] Diretórios criados:\n";
            $log .= "Base: " . $uploadBase . " (" . (is_dir($uploadBase) ? "OK" : "FALHA") . ")\n";
            $log .= "Fotos: " . $fotosDir . " (" . (is_dir($fotosDir) ? "OK" : "FALHA") . ")\n";
            $log .= "Docs: " . $docsDir . " (" . (is_dir($docsDir) ? "OK" : "FALHA") . ")\n";
            file_put_contents($logPath, $log, FILE_APPEND);

            if (!empty($_FILES['foto']) && isset($_FILES['foto']['error'])) {
                if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                    $log = "[" . date('Y-m-d H:i:s') . "] Upload error for foto: code " . $_FILES['foto']['error'] . "\n";
                    $log .= "FILES: " . print_r($_FILES['foto'], true) . "\n";
                    file_put_contents(dirname(__DIR__) . '/debug_register.txt', $log, FILE_APPEND);
                }
            }
            if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['foto'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $f['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg','image/png','image/webp'];
                $maxBytes = 10 * 1024 * 1024;
                if (in_array($mime, $allowed) && $f['size'] <= $maxBytes) {
                    $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                    $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    $dest = $fotosDir . '/' . $name;
                    if (move_uploaded_file($f['tmp_name'], $dest)) {
                        $dados['foto'] = 'public/uploads/usuarios_img/' . $name;
                    } else {
                        $log = "[" . date('Y-m-d H:i:s') . "] move_uploaded_file failed for foto.\n";
                        $log .= "Dest: $dest\nFile array: " . print_r($f, true) . "\n";
                        file_put_contents(dirname(__DIR__) . '/debug_register.txt', $log, FILE_APPEND);
                    }
                }
            }

            if (!empty($_FILES['comprovante']) && isset($_FILES['comprovante']['error'])) {
                if ($_FILES['comprovante']['error'] !== UPLOAD_ERR_OK) {
                    $log = "[" . date('Y-m-d H:i:s') . "] Upload error for comprovante: code " . $_FILES['comprovante']['error'] . "\n";
                    $log .= "FILES: " . print_r($_FILES['comprovante'], true) . "\n";
                    file_put_contents(dirname(__DIR__) . '/debug_register.txt', $log, FILE_APPEND);
                }
            }
            if (!empty($_FILES['comprovante']) && $_FILES['comprovante']['error'] === UPLOAD_ERR_OK) {
                $f = $_FILES['comprovante'];
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $docsDir . '/' . $name;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $dados['comprovante'] = 'public/uploads/documentos/' . $name;
                } else {
                    $log = "[" . date('Y-m-d H:i:s') . "] move_uploaded_file failed for comprovante.\n";
                    $log .= "Dest: $dest\nFile array: " . print_r($f, true) . "\n";
                    file_put_contents(dirname(__DIR__) . '/debug_register.txt', $log, FILE_APPEND);
                }
            }

            try {
                $usuario = new Usuario();
                $result = $usuario->cadastrar($dados);
                if (isset($result['erro'])) {
                    if (str_contains($_SERVER['HTTP_REFERER'] ?? '', 'admin')) {
                        header('Location: ' . URL . '/paginas/cadastro_admin?erro=' . urlencode($result['erro']));
                    } else {
                        header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode($result['erro']));
                    }
                    exit;
                }
                $newId = $result['id'] ?? null;
            } catch (Throwable $t) {
                $logPath = dirname(__DIR__) . '/debug_register.txt';
                $log = "[" . date('Y-m-d H:i:s') . "] Exception during registration:\n" . $t->getMessage() . "\n" . $t->getTraceAsString() . "\nPOST:" . print_r($_POST, true) . "\nFILES:" . print_r($_FILES, true) . "\n\n";
                file_put_contents($logPath, $log, FILE_APPEND);
                echo '<p>Erro ao cadastrar usuário. Detalhes registrados em app/debug_register.txt</p>';
                return;
            }

            if ($newId) {
                $db = new Database();
                
                if (!empty($_POST['email'])) {
                    $db->query("SELECT 1 FROM email WHERE emai_endereco = :email LIMIT 1");
                    $db->bind(':email', $_POST['email']);
                    $emailExiste = $db->resultado();
                    
                    if ($emailExiste) {
                        if (str_contains($_SERVER['HTTP_REFERER'] ?? '', 'admin')) {
                            header('Location: ' . URL . '/paginas/cadastro_admin?erro=' . urlencode('Este e-mail já está cadastrado.'));
                        } else {
                            header('Location: ' . URL . '/paginas/cadastro_aluno?erro=' . urlencode('Este e-mail já está cadastrado.'));
                        }
                        exit;
                    }

                    $db->query("INSERT INTO email (emai_endereco, usua_id, is_principal) VALUES (:email, :uid, 1)");
                    $db->bind(':email', $_POST['email']);
                    $db->bind(':uid', $newId);
                    $db->executa();
                }
                
                if (!empty($_POST['email_recuperacao'])) {
                    $db->query("INSERT INTO email (emai_endereco, usua_id, is_principal) VALUES (:email, :uid, 0)");
                    $db->bind(':email', $_POST['email_recuperacao']);
                    $db->bind(':uid', $newId);
                    $db->executa();
                }

                if (!empty($_POST['telefone'])) {
                    $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:num, :uid, 'CEL', 1)");
                    $db->bind(':num', $_POST['telefone']);
                    $db->bind(':uid', $newId);
                    $db->executa();
                }

                if (!empty($_POST['telefone_resp'])) {
                    $db->query("INSERT INTO telefone (tele_numero, usua_id, tipo, is_principal) VALUES (:num, :uid, 'RESP', 0)");
                    $db->bind(':num', $_POST['telefone_resp']);
                    $db->bind(':uid', $newId);
                    $db->executa();
                }

                if (isset($dados['tius_id']) && intval($dados['tius_id']) === 2 && 
                    isset($_SESSION['user_tipo']) && intval($_SESSION['user_tipo']) === 1) {
                    if (session_status() === PHP_SESSION_NONE) session_start();
                    $_SESSION['admin_cadastrado'] = true;
                    header('Location: ' . URL . '/paginas/index_app');
                } else {
                    header('Location: ' . URL . '/paginas/entrar');
                }
                exit;
            } else {
                $logPath = dirname(__DIR__) . '/debug_register.txt';
                $log = "[" . date('Y-m-d H:i:s') . "] Registration failed (no exception).\nPOST:" . print_r($_POST, true) . "\nFILES:" . print_r($_FILES, true) . "\n\n";
                file_put_contents($logPath, $log, FILE_APPEND);
                echo '<p>Erro ao cadastrar usuário. Detalhes registrados em app/debug_register.txt</p>';
            }
        } else {
            header('Location: ' . URL . '/paginas/cadastro_aluno');
            exit;
        }
    }

    public function atualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/perfil');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $uid = $_POST['usuario_id'] ?? $_SESSION['user_id'] ?? null;
        if (!$uid) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        $curso = $_POST['curso'] ?? null;
        $turma = $_POST['turma'] ?? null;
        $turno = $_POST['turno'] ?? null;
        $endereco = $_POST['endereco'] ?? null;

        $uploadBase = dirname(__DIR__, 2) . '/public/uploads';
        $fotosDir = $uploadBase . '/usuarios_img';
        if (!is_dir($fotosDir)) mkdir($fotosDir, 0755, true);

        $fotoPath = null;
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['foto'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $f['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg','image/png','image/webp'];
            $maxBytes = 10 * 1024 * 1024;
            if (in_array($mime, $allowed) && $f['size'] <= $maxBytes) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $dest = $fotosDir . '/' . $name;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $fotoPath = 'public/uploads/usuarios_img/' . $name;
                } else {
                    file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] move_uploaded_file failed in atualizar for foto. Dest: $dest\n" . print_r($f, true), FILE_APPEND);
                }
            }
        }

        $db = new Database();
        $sets = [];
        $params = [];
        if ($curso !== null) { $sets[] = 'usua_curso = :curso'; $params[':curso'] = $curso; }
        if ($turma !== null) { $sets[] = 'usua_turma = :turma'; $params[':turma'] = $turma; }
        if ($turno !== null) { $sets[] = 'usua_turno = :turno'; $params[':turno'] = $turno; }
        if ($endereco !== null) { $sets[] = 'usua_endereco = :endereco'; $params[':endereco'] = $endereco; }
        if ($fotoPath !== null) { $sets[] = 'usua_foto = :foto'; $params[':foto'] = $fotoPath; }

        if (!empty($sets)) {
            $sql = 'UPDATE usuario SET ' . implode(', ', $sets) . ' WHERE usua_id = :id';
            $db->query($sql);
            foreach ($params as $k => $v) $db->bind($k, $v);
            $db->bind(':id', $uid);
            try {
                $db->executa();
            } catch (Throwable $t) {
                file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao atualizar usuario: " . $t->getMessage() . "\n", FILE_APPEND);
            }
        }

        header('Location: ' . URL . '/paginas/perfil');
        exit;
    }

    public function atualizarFoto() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/perfil');
            exit;
        }

        $uploadBase = dirname(__DIR__, 2) . '/public/uploads';
        $fotosDir = $uploadBase . '/usuarios_img';
        if (!is_dir($fotosDir)) mkdir($fotosDir, 0755, true);

        if (empty($_FILES['usua_foto']) || $_FILES['usua_foto']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . URL . '/paginas/perfil');
            exit;
        }

        $f = $_FILES['usua_foto'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $f['tmp_name']);
        finfo_close($finfo);
        $allowed = ['image/jpeg','image/png','image/webp'];
        $maxBytes = 2 * 1024 * 1024; // 2 MB as suggested in view
        $fotoPath = null;
        if (in_array($mime, $allowed) && $f['size'] <= $maxBytes) {
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $name = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $fotosDir . '/' . $name;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $fotoPath = 'public/uploads/usuarios_img/' . $name;
            } else {
                file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] move_uploaded_file failed in atualizarFoto. Dest: $dest\n" . print_r($f, true), FILE_APPEND);
            }
        } else {
            file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] Invalid foto upload in atualizarFoto: mime=$mime size={$f['size']}\n", FILE_APPEND);
        }

        if ($fotoPath) {
            $db = new Database();
            $db->query("UPDATE usuario SET usua_foto = :foto WHERE usua_id = :id LIMIT 1");
            $db->bind(':foto', $fotoPath);
            $db->bind(':id', $_SESSION['user_id']);
            try {
                $db->executa();
            } catch (Throwable $t) {
                file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao salvar usua_foto: " . $t->getMessage() . "\n", FILE_APPEND);
            }
        }

        header('Location: ' . URL . '/paginas/perfil');
        exit;
    }

    public function alterarSenha() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/perfil');
            exit;
        }

        $atual = $_POST['senha_atual'] ?? '';
        $nova  = $_POST['nova_senha'] ?? '';
        $conf  = $_POST['confirma_senha'] ?? '';

        if ($nova === '' || $conf === '' || $nova !== $conf || strlen($nova) < 8) {
            header('Location: ' . URL . '/paginas/perfil?error=senha_invalida');
            exit;
        }

        $db = new Database();
        $db->query("SELECT usua_senha_hash FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $_SESSION['user_id']);
        $res = $db->resultado();
        $hash = $res->usua_senha_hash ?? '';

        $ok = false;
        if ($hash && password_verify($atual, $hash)) {
            $ok = true;
        } else {
            // fallback: unhashed legacy check
            if ($hash !== '' && hash_equals($hash, $atual)) {
                $ok = true;
            }
        }

        if (!$ok) {
            header('Location: ' . URL . '/paginas/perfil?error=senha_atual_incorreta');
            exit;
        }

        $novoHash = password_hash($nova, PASSWORD_DEFAULT);
        $db->query("UPDATE usuario SET usua_senha_hash = :h WHERE usua_id = :id LIMIT 1");
        $db->bind(':h', $novoHash);
        $db->bind(':id', $_SESSION['user_id']);
        try {
            $db->executa();
        } catch (Throwable $t) {
            file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] Erro ao atualizar senha: " . $t->getMessage() . "\n", FILE_APPEND);
            header('Location: ' . URL . '/paginas/perfil?error=salvar_falha');
            exit;
        }

        header('Location: ' . URL . '/paginas/perfil?success=senha_atualizada');
        exit;
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';

        if (!$email || !$senha) {
            header('Location: ' . URL . '/paginas/entrar?error=missing');
            exit;
        }

        $usuarioModel = new Usuario();
        $user = $usuarioModel->buscarPorEmail($email);
        if (!$user) {
            header('Location: ' . URL . '/paginas/entrar?error=notfound');
            exit;
        }

        $hashNoDb = $user['usua_senha_hash'] ?? '';
        $loginOk = false;
        if ($hashNoDb && password_verify($senha, $hashNoDb)) {
            $loginOk = true;
        } else {
            if ($hashNoDb !== '' && hash_equals($hashNoDb, $senha)) {
                try {
                    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                    $dbUpd = new Database();
                    $dbUpd->query("UPDATE usuario SET usua_senha_hash = :h WHERE usua_id = :id");
                    $dbUpd->bind(':h', $novoHash);
                    $dbUpd->bind(':id', $user['usua_id']);
                    $dbUpd->executa();
                    $loginOk = true;
                } catch (Throwable $t) {
                    @file_put_contents(dirname(__DIR__) . '/debug_register.txt', "[" . date('Y-m-d H:i:s') . "] Falha ao atualizar hash de senha para usua_id {$user['usua_id']}: " . $t->getMessage() . "\n", FILE_APPEND);
                    $loginOk = true;
                }
            }
        }

        if (!$loginOk) {
            header('Location: ' . URL . '/paginas/entrar?error=badpass');
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user_id'] = $user['usua_id'];
        $_SESSION['user_name'] = $user['usua_nome'];
        $_SESSION['user_tipo'] = $user['tius_id'];

        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: ' . URL . '/paginas/entrar');
        exit;
    }

    public function trocarSituacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // só admin
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/index_app');
            exit;
        }

        $alunoId  = isset($_POST['usua_id']) ? intval($_POST['usua_id']) : 0;
        $novaSit  = $_POST['nova_situacao'] ?? 'I';
        $redirect = !empty($_POST['redirect']) ? $_POST['redirect'] : (URL . '/paginas/index_app');

        if ($alunoId <= 0 || !in_array($novaSit, ['A','I','B'], true)) {
            header('Location: ' . $redirect);
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // garante que o admin só mexe em aluno da empresa dele
        $adminId = intval($_SESSION['user_id']);

        $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
        $db->bind(':id', $adminId);
        $admRow = $db->resultado();

        if (!$admRow || empty($admRow->empr_id)) {
            header('Location: ' . $redirect);
            exit;
        }

        $emprId = intval($admRow->empr_id);

        // Atualiza situação somente se o aluno for tipo 3 e da mesma empresa
        $db->query("
            UPDATE usuario
            SET usua_situacao = :sit
            WHERE usua_id = :uid
              AND empr_id = :empr
              AND tius_id = 3
            LIMIT 1
        ");
        $db->bind(':sit', $novaSit);
        $db->bind(':uid', $alunoId);
        $db->bind(':empr', $emprId);
        $db->executa();

        header('Location: ' . $redirect);
        exit;
    }

}
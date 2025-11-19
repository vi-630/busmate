<?php
// Model de Usuario

if (!class_exists('Database')) {
    $maybe = dirname(__DIR__) . '/Libraries/Database.php';
    if (file_exists($maybe)) require_once $maybe;
}

class Usuario {
    /**
     * cadastrar($dados):
     *  Obrigatórios: nome, cpf, senha
     *  Opcionais: tius_id (default 2), foto, empr_id
     */
    public function cadastrar(array $dados) {
        try {
            // garante dependencia Database
            if (!class_exists('Database')) {
                $maybe = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR . 'Database.php';
                if (file_exists($maybe)) require_once $maybe;
                if (!class_exists('Database')) return ['erro' => 'Dependência Database ausente.'];
            }

            // normaliza/valida campos
            $nome  = trim((string)($dados['nome'] ?? ''));
            $cpf   = trim((string)($dados['cpf'] ?? ''));
            $senha = (string)($dados['senha'] ?? '');
            if ($nome === '' || $cpf === '' || $senha === '') {
                return ['erro' => 'Campos obrigatórios ausentes (nome, cpf ou senha).'];
            }

            // normaliza cpf (mantém pontos/traços opcionais no BD se desejar, aqui removemos para checagem)
            $cpfDigits = preg_replace('/\D+/', '', $cpf);

            $db = new Database();

            // checa duplicidade de CPF (se a coluna existe)
            $db->query("SHOW COLUMNS FROM usuario LIKE 'usua_cpf'");
            $hasCpfCol = $db->resultado();
            if ($hasCpfCol) {
                $db->query("SELECT 1 FROM usuario WHERE REPLACE(REPLACE(REPLACE(usua_cpf,'.',''),'-',''),'/','') = :cpf LIMIT 1");
                $db->bind(':cpf', $cpfDigits);
                if ($db->resultado()) {
                    return ['erro' => 'CPF já cadastrado.'];
                }
            }

            // detecta coluna de senha disponível: usua_senha ou usua_senha_hash
            $pwdCol = 'usua_senha';
            $db->query("SHOW COLUMNS FROM usuario LIKE 'usua_senha'");
            if (!$db->resultado()) {
                $db->query("SHOW COLUMNS FROM usuario LIKE 'usua_senha_hash'");
                if ($db->resultado()) $pwdCol = 'usua_senha_hash';
            }

            // hash da senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $tius_id = intval($dados['tius_id'] ?? 2);
            $foto    = $dados['foto'] ?? null;
            $empr_id = isset($dados['empr_id']) && $dados['empr_id'] !== '' ? intval($dados['empr_id']) : null;

            // monta INSERT incluindo empr_id se fornecido
            $cols = ['usua_nome', 'usua_cpf', $pwdCol, 'tius_id', 'usua_foto'];
            $vals = [':nome', ':cpf', ':senha', ':tius', ':foto'];

            if ($empr_id !== null) {
                $cols[] = 'empr_id';
                $vals[] = ':empr';
            }

            $sql = "INSERT INTO usuario (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
            $db->query($sql);
            $db->bind(':nome', $nome);
            $db->bind(':cpf', $cpf); // armazena conforme o formato desejado no BD (a coluna pode ter máscara)
            $db->bind(':senha', $senhaHash);
            $db->bind(':tius', $tius_id);
            $db->bind(':foto', $foto);
            if ($empr_id !== null) $db->bind(':empr', $empr_id);

            $db->executa();
            $id = $db->ultimoIdInserido();
            if (empty($id)) return ['erro' => 'Falha ao inserir usuário.'];

            return ['id' => $id];
        } catch (Throwable $t) {
            error_log('[Usuario] Erro cadastrar: ' . $t->getMessage());
            // Retorna mensagem genérica para view; detalhes ficam no log
            return ['erro' => 'Erro ao cadastrar usuário.'];
        }
    }
}

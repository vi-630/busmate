<?php

require_once dirname(__DIR__) . '/Libraries/Database.php';

class Usuario{
	private $db;

	public function __construct() {
		$this->db = new Database();
	}

	/**
	 * @param string 
	 * @return int|null
	 */
	private function resolverEmpresaPorCNPJ($cnpj) {
		if (empty($cnpj)) return null;
		$cnpj = preg_replace('/\D/', '', $cnpj);
		$this->db->query("SELECT empr_id FROM empresa WHERE REPLACE(REPLACE(REPLACE(empr_cnpj, '.', ''), '/', ''), '-', '') = :cnpj LIMIT 1");
		$this->db->bind(':cnpj', $cnpj);
		$res = $this->db->resultado();
		return $res ? $res->empr_id : null;
	}

	/**
	 * @param string 
	 * @return int|null 
	 */

	public function cadastrar($dados) {
		$erro = null;

		$logPath = dirname(__DIR__) . '/debug_register.txt';
		$log = "[" . date('Y-m-d H:i:s') . "] Dados recebidos para cadastro:\n" . print_r($dados, true) . "\n";
		file_put_contents($logPath, $log, FILE_APPEND);

		if (!empty($dados['cnpj'])) {
			$empr_id = $this->resolverEmpresaPorCNPJ($dados['cnpj']);
			if ($empr_id) {
				$dados['empr_id'] = $empr_id;
				$log = "[" . date('Y-m-d H:i:s') . "] CNPJ {$dados['cnpj']} resolvido para empr_id: $empr_id\n";
			} else {
				$erro = "CNPJ não encontrado. Verifique se o CNPJ está correto ou entre em contato com o suporte.";
				$log = "[" . date('Y-m-d H:i:s') . "] AVISO: CNPJ {$dados['cnpj']} não encontrado no banco\n";
			}
			file_put_contents($logPath, $log, FILE_APPEND);
		}

		$map = [
			'nome' => 'usua_nome',
			'cpf' => 'usua_cpf',
			'tius_id' => 'tius_id',
			'senha' => 'usua_senha_hash',
			'turma' => 'usua_turma',
			'curso' => 'usua_curso',
			'cnpj' => 'usua_cnpj',
			'turno' => 'usua_turno',
			'endereco' => 'usua_endereco',
			'foto' => 'usua_foto',
			'comprovante' => 'usua_matricula',
			'escola' => 'usua_escola',
			'empr_id' => 'empr_id'
		];

		$this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuario'");
		$colsRes = $this->db->resultados();
		$existing = [];
		foreach ($colsRes as $c) $existing[] = $c->COLUMN_NAME;

		$logPath = dirname(__DIR__) . '/debug_register.txt';
		$log = "[" . date('Y-m-d H:i:s') . "] Colunas existentes na tabela usuario: " . implode(', ', $existing) . "\n";
		file_put_contents($logPath, $log, FILE_APPEND);

		$insertCols = [];
		$placeholders = [];
		$values = [];

		foreach ($map as $key => $col) {
			if (!in_array($col, $existing)) continue;
			if ($key === 'senha') {
				$val = password_hash($dados[$key] ?? '', PASSWORD_DEFAULT);
			} else {
				$val = $dados[$key] ?? null;
			}
			$insertCols[] = $col;
			$placeholders[] = ':' . $col;
			$values[':' . $col] = $val;
		}

		if (empty($insertCols)) {
			$logPath = dirname(__DIR__) . '/debug_register.txt';
			$log = "[" . date('Y-m-d H:i:s') . "] Erro: Nenhuma coluna válida para inserir.\nDados recebidos:" . print_r($dados, true) . "\n";
			file_put_contents($logPath, $log, FILE_APPEND);
			return false;
		}

		$sql = 'INSERT INTO usuario (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
		$this->db->query($sql);
		foreach ($values as $ph => $val) {
			$this->db->bind($ph, $val);
		}

		$logPath = dirname(__DIR__) . '/debug_register.txt';
		$log = "[" . date('Y-m-d H:i:s') . "] SQL gerado: " . $sql . "\nValores:" . print_r($values, true) . "\n";
		file_put_contents($logPath, $log, FILE_APPEND);

		try {
			if ($erro) {
				$log = "[" . date('Y-m-d H:i:s') . "] Erro de validação: $erro\n";
				file_put_contents($logPath, $log, FILE_APPEND);
				return ['erro' => $erro];
			}
			if ($this->db->executa()) {
				return ['id' => $this->db->ultimoIdInserido()];
			}
		} catch (PDOException $e) {
			$erro = "Erro ao salvar usuário. Por favor, tente novamente.";
			$log = "[" . date('Y-m-d H:i:s') . "] Erro PDO: " . $e->getMessage() . "\n";
			file_put_contents($logPath, $log, FILE_APPEND);
			return ['erro' => $erro];
		}
		return ['erro' => "Erro ao salvar usuário. Por favor, tente novamente."];
	}

	public function buscarPorEmail($email) {
		$this->db->query("
			SELECT u.* 
			FROM usuario u
			INNER JOIN email e ON e.usua_id = u.usua_id 
			WHERE e.emai_endereco = :email 
			AND e.is_principal = 1 
			LIMIT 1
		");
		$this->db->bind(':email', $email);
		$res = $this->db->resultado();
		if ($res) return (array)$res;
		return null;
	}
}
?>
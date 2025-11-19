<?php

class Post {
	private $pdo;

	public function __construct() {
		$host = 'localhost';
		$db   = 'busmate';
		$user = 'root';
		$pass = '';
		$charset = 'utf8mb4';

		$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
		$options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		try {
			$this->pdo = new PDO($dsn, $user, $pass, $options);
		} catch (PDOException $e) {
			throw new PDOException($e->getMessage(), (int)$e->getCode());
		}
	}

	public function cadastrarAluno($dados) {
		$sql = "INSERT INTO alunos (nome, curso, telefone, turma, turno, endereco, comprovante, email, senha, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $this->pdo->prepare($sql);
		return $stmt->execute([
			$dados['nome'],
			$dados['curso'],
			$dados['telefone'],
			$dados['turma'],
			$dados['turno'],
			$dados['endereco'],
			$dados['comprovante'],
			$dados['email'],
			password_hash($dados['senha'], PASSWORD_DEFAULT),
			$dados['foto'] 
		]);
	}
}
?>
<?php
require_once dirname(__DIR__) . '/Libraries/Controller.php';



class Paginas extends Controller{
    public function sobre(){
        $this->view('paginas/sobre');    
    }//fim do método sobre
    public function index(){
        $this->view('paginas/home');
    }//fim da fução index
    public function contato(){
        $this->view('paginas/contato');
    }
    public function entrar(){
        $this->view('paginas/entrar');
    }
    public function index_app(){
        // carrega empresas quando for ROOT
        $empresas = [];
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!empty($_SESSION['user_tipo']) && intval($_SESSION['user_tipo']) === 1) {
            require_once dirname(__DIR__) . '/Libraries/Database.php';
            $db = new Database();

            // filtro de busca opcional (?q=)
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $where = '';
            if ($q !== '') {
                $like = "%{$q}%";
                $where = "WHERE e.empr_nome LIKE :q OR e.empr_cnpj LIKE :q OR e.empr_razao LIKE :q";
            }

            // busca empresas + contagem de admins (tius_id=2)
            $sql = "
                SELECT 
                    e.empr_id,
                    e.empr_nome,
                    e.empr_cnpj,
                    e.empr_razao,
                    e.empr_logo,
                    COUNT(u.usua_id) AS admins
                FROM empresa e
                LEFT JOIN usuario u
                    ON u.empr_id = e.empr_id
                AND u.tius_id = 2
                $where
                GROUP BY e.empr_id, e.empr_nome, e.empr_cnpj, e.empr_razao, e.empr_logo
                ORDER BY e.empr_dth_criacao DESC
            ";


            $db->query($sql);
            if ($q !== '') $db->bind(':q', $like);

            $rows = $db->resultados(); // retorna array de objetos

            // mapeia para o formato que a view já espera
            foreach ($rows as $r) {
                $empresas[] = [
                    'id'     => $r->empr_id,
                    'nome'   => $r->empr_nome,
                    'cnpj'   => $r->empr_cnpj,
                    'razao'  => $r->empr_razao,
                    'logo'   => $r->empr_logo,
                    'admins' => (int)$r->admins,
                    // 'status' => 'Ativa', // se quiser exibir um rótulo, senão deixe sem
                ];
            }
        }

        // passa o array para a view
        $this->view('paginas/index_app', ['empresas' => $empresas]);
    }

    public function perfil(){
        $usuario = [];
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user_id'])) {
            require_once dirname(__DIR__) . '/Libraries/Database.php';
            try {
                $db = new Database();
                $db->query("SELECT usua_id, usua_nome, usua_turma, usua_turno, usua_endereco, usua_curso, usua_foto, usua_situacao FROM usuario WHERE usua_id = :id LIMIT 1");
                $db->bind(':id', $_SESSION['user_id']);
                $res = $db->resultado();
                if ($res) {
                    $usuario = [
                        'id' => $res->usua_id,
                        'nome' => $res->usua_nome,
                        'turma' => $res->usua_turma,
                        'turno' => $res->usua_turno,
                        'endereco' => $res->usua_endereco,
                        'curso' => $res->usua_curso,
                        'foto' => $res->usua_foto,
                        'situacao' => $res->usua_situacao,
                        'situacao_mensalidade' => '—'
                    ];
                }
            } catch (Throwable $t) {
                $usuario = [];
            }
        }
        $this->view('paginas/perfil', ['usuario' => $usuario]);
    }
    public function forum(){
        $usuario = [];
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!empty($_SESSION['user_id'])) {
            require_once dirname(__DIR__) . '/Libraries/Database.php';
            try {
                $db = new Database();
                $db->query("SELECT usua_id, usua_nome FROM usuario WHERE usua_id = :id LIMIT 1");
                $db->bind(':id', $_SESSION['user_id']);
                $res = $db->resultado();
                if ($res) {
                    $usuario = [
                        'id' => $res->usua_id,
                        'nome' => $res->usua_nome
                    ];
                }
            } catch (Throwable $t) {
                $usuario = [];
            }
        }
        $this->view('paginas/forum', ['usuario' => $usuario]);
    }
    public function manha(){
        $this->view('paginas/manha');
    }
    public function tarde(){
        $this->view('paginas/tarde');
    }
    public function contrato(){
        $this->view('paginas/contrato');
    }
    public function termo_uso(){
        $this->view('paginas/termo_uso');
    }
    public function politica_privacidade(){
        $this->view('paginas/politica_privacidade');
    }
    public function central_ajuda(){
        $this->view('paginas/central_ajuda');
    }
    public function cadastro_aluno(){
        $this->view('paginas/cadastro_aluno');
    }
    public function cadastro_admin(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
        $this->view('paginas/cadastro_admin');
    }

    public function cadastro_empresa() {
    require_once dirname(__DIR__, 1) . '/Views/paginas/cadastro_empresa.php';
}

public function cadastro_admin_empresa() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Apenas ROOT (tipo 1) pode cadastrar empresas e seus admins
    if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
        header('Location: ' . URL . '/paginas/entrar');
        exit;
    }

    // carrega a view de cadastro de administrador vinculado à empresa
    $this->view('paginas/cadastro_admin');
}


public function detalhe_empresa() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 1) {
        header('Location: ' . URL . '/paginas/entrar');
        exit;
    }

    $empresa = null;
    $admins = [];

    if (!empty($_GET['id'])) {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // Empresa + criador
        $db->query("
            SELECT e.*, u.usua_nome AS criador_nome, u.usua_id AS criador_id
            FROM empresa e
            LEFT JOIN usuario u ON u.usua_id = e.empr_criado_por
            WHERE e.empr_id = :id
            LIMIT 1
        ");
        $db->bind(':id', intval($_GET['id']));
        $empresa = $db->resultado();

        // Admins vinculados
        $db->query("
            SELECT 
                u.usua_id, u.usua_nome, u.usua_foto,
                (SELECT emai_endereco FROM email WHERE usua_id = u.usua_id AND is_principal = 1 LIMIT 1) AS email_principal,
                (SELECT tele_numero   FROM telefone WHERE usua_id = u.usua_id AND is_principal = 1 LIMIT 1) AS telefone_principal
            FROM usuario u
            WHERE u.empr_id = :id AND u.tius_id = 2
            ORDER BY u.usua_nome
        ");
        $db->bind(':id', intval($_GET['id']));
        $admins = $db->resultados();
    }

    $this->view('paginas/detalhe_empresa', [
        'empresa' => $empresa,
        'admins'  => $admins
    ]);
}

    // Adicionar novos métodos para o fluxo de solicitação
    public function escolher_empresa() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['solicitacao_aluno'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno');
            exit;
        }
        $this->view('paginas/escolher_empresa');
    }

    public function solicitacao_status() {
        if (empty($_GET['token'])) {
            header('Location: ' . URL . '/paginas/cadastro_aluno');
            exit;
        }
        $this->view('paginas/solicitacao_status');
    }

}//fim da classe Paginas
?>

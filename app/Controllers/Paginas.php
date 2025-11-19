<?php
class Paginas extends Controllers{
    public function sobre(){
        $this->view('paginas/sobre');    
    }//fim do método sobre
    public function index(){
        $this->view('paginas/home');
    }//fim da fução index

    public function contato(){
        $this->view('paginas/contato');
    }//fim do método contato
    
    public function contrato() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['user_id'])) {
        header('Location: ' . URL . '/paginas/entrar');
        exit;
    }

    $userId = (int) $_SESSION['user_id'];

    require_once dirname(__DIR__) . '/Libraries/Database.php';
    $db = new Database();

    // 1) BUSCAR O CONTRATO MAIS RECENTE DO ALUNO (QUALQUER SITUAÇÃO)
    $db->query("
        SELECT 
            c.*,
            e.empr_nome,
            e.empr_cnpj,
            e.empr_contrato_url,
            e.empr_pix_url,
            e.empr_chave_pix,
            e.empr_vlr_mensalidade
        FROM contrato c
        INNER JOIN empresa e ON e.empr_id = c.empr_id
        WHERE c.usua_id = :uid
        ORDER BY c.cont_dth_criacao DESC
        LIMIT 1
    ");
    $db->bind(':uid', $userId);
    $contrato = $db->resultado();   // stdClass ou null

    // 2) DIZER SE ESTÁ VIGENTE OU NÃO
    $contrato_vigente = false;
    if ($contrato) {
        $hoje = date('Y-m-d');
        if ($contrato->cont_situacao === 'ATIVO') {
            $inicio = $contrato->cont_inicio_vigencia ?: null;
            $fim    = $contrato->cont_fim_vigencia ?: null;

            $okInicio = !$inicio || $inicio <= $hoje;
            $okFim    = !$fim    || $fim    >= $hoje;

            if ($okInicio && $okFim) {
                $contrato_vigente = true;
            }
        }
    }

    // 3) COMPETÊNCIA ATUAL (AAAA-MM)
    $competencia = date('Y-m');

    // 4) BUSCAR PAGAMENTO DA COMPETÊNCIA ATUAL (se houver contrato)
    $pagamento = null;
    if ($contrato) {
        $db->query("
            SELECT *
            FROM pagamento
            WHERE usua_id = :uid
              AND cont_id = :cid
              AND paga_competencia = :comp
            LIMIT 1
        ");
        $db->bind(':uid', $userId);
        $db->bind(':cid', $contrato->cont_id);
        $db->bind(':comp', $competencia);
        $pagamento = $db->resultado();
    }

    // 5) JOGAR TUDO PRA VIEW
    $this->view('paginas/contrato', [
        'contrato'         => $contrato,
        'contrato_vigente' => $contrato_vigente,
        'competencia'      => $competencia,
        'pagamento'        => $pagamento,
    ]);
}

    /**
     * POST /paginas/salvarContratoAssinado
     * Aluno envia contrato assinado na página contrato.php
     */
    public function salvarContratoAssinado() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        // validação básica do arquivo
        if (empty($_FILES['contrato_assinado']) || $_FILES['contrato_assinado']['error'] === UPLOAD_ERR_NO_FILE) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Arquivo não selecionado.'));
            exit;
        }

        $file = $_FILES['contrato_assinado'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Erro ao fazer upload. Tente novamente.'));
            exit;
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Arquivo muito grande (máx 5MB).'));
            exit;
        }

        $okTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $okTypes)) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Formato não permitido. Use PDF, JPG ou PNG.'));
            exit;
        }

        // criar diretório se não existir
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/usuarios_contrato';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

        // gerar nome único
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $nomeArquivo = $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destino = $uploadDir . '/' . $nomeArquivo;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            header('Location: ' . URL . '/paginas/contrato?erro=' . urlencode('Falha ao salvar arquivo.'));
            exit;
        }

        // salvar no banco de dados
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // atualizar contrato: cont_assinado_url + situação para ANALISE
        $caminhoRelativo = 'public/uploads/usuarios_contrato/' . $nomeArquivo;
        $db->query("
            UPDATE contrato
            SET cont_assinado_url = :url,
                cont_situacao = 'ANALISE',
                cont_dth_assinatura = NOW(),
                cont_motivo_recusa = NULL
            WHERE usua_id = :uid
            ORDER BY cont_dth_criacao DESC
            LIMIT 1
        ");
        $db->bind(':url', $caminhoRelativo);
        $db->bind(':uid', $userId);
        $db->executa();

        // redirecionar com mensagem de sucesso
        header('Location: ' . URL . '/paginas/contrato?ok=' . urlencode('Contrato enviado. Aguarde a análise do administrador.'));
        exit;
    }

    /**
     * POST /paginas/cancelarContrato
     * Aluno cancela seu contrato ativo: marca CANCELADO e preenche cont_fim_vigencia
     */
    public function cancelarContrato() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL . '/paginas/contrato');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // Atualiza o contrato ATIVO mais recente do aluno
        $db->query('
            UPDATE contrato
            SET cont_situacao = "CANCELADO",
                cont_fim_vigencia = CURDATE()
            WHERE cont_id = (
                SELECT cont_id FROM (
                    SELECT cont_id FROM contrato
                    WHERE usua_id = :uid AND cont_situacao = "ATIVO"
                    ORDER BY cont_dth_criacao DESC
                    LIMIT 1
                ) AS sub
            )
        ');
        $db->bind(':uid', $userId);
        $db->executa();

        header('Location: ' . URL . '/paginas/contrato?ok=' . urlencode('Contrato cancelado.'));
        exit;
    }

    public function entrar(){
        $this->view('paginas/entrar');
    }
    public function index_app() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $tipo = isset($_SESSION['user_tipo']) ? intval($_SESSION['user_tipo']) : null;
    $empresas = [];
    $alunos   = [];

    // =======================
    // ROOT (tipo 1) - empresas
    // =======================
    if ($tipo === 1) {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        // filtro de busca opcional (?q=)
        $q = isset($_GET['q']) ? trim($_GET['q']) : '';
        $where = '';
        if ($q !== '') {
            $like  = "%{$q}%";
            $where = "WHERE e.empr_nome LIKE :q OR e.empr_cnpj LIKE :q OR e.empr_razao LIKE :q";
        }

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
        if ($q !== '') {
            $db->bind(':q', $like);
        }

        $rows = $db->resultados() ?: [];
        foreach ($rows as $r) {
            $empresas[] = [
                'id'     => $r->empr_id,
                'nome'   => $r->empr_nome,
                'cnpj'   => $r->empr_cnpj,
                'razao'  => $r->empr_razao,
                'logo'   => $r->empr_logo,
                'admins' => (int)$r->admins,
            ];
        }

        $this->view('paginas/index_app', [
            'empresas' => $empresas,
            'alunos'   => $alunos
        ]);
        return;
    }

    // =======================
    // ADMIN (tipo 2) - alunos
    // =======================
    if ($tipo === 2) {
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        $adminId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        $emprId  = null;

        if ($adminId > 0) {
            // pega empresa do admin
            $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
            $db->bind(':id', $adminId);
            $u = $db->resultado();
            if ($u && !empty($u->empr_id)) {
                $emprId = (int)$u->empr_id;
            }
        }

        if ($emprId) {
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $extra = '';
            if ($q !== '') {
                $like  = "%{$q}%";
                $extra = " AND (u.usua_nome  LIKE :q
                            OR u.usua_curso LIKE :q
                            OR u.usua_escola LIKE :q)";
            }

            $sql = "
                SELECT 
                    u.usua_id,
                    u.usua_nome,
                    u.usua_curso,
                    u.usua_escola,
                    u.usua_turno,
                    u.usua_foto
                FROM usuario u
                WHERE u.empr_id = :empr
                  AND u.tius_id = 3
                  $extra
                ORDER BY u.usua_nome ASC
            ";

            $db->query($sql);
            $db->bind(':empr', $emprId);
            if (!empty($extra)) {
                $db->bind(':q', $like);
            }

            $rows = $db->resultados() ?: [];
            foreach ($rows as $r) {
                $alunos[] = [
                    'id'     => $r->usua_id,
                    'nome'   => $r->usua_nome,
                    'curso'  => $r->usua_curso,
                    'escola' => $r->usua_escola,
                    'turno'  => $r->usua_turno,
                    'foto'   => $r->usua_foto,
                ];
            }
        }

        $this->view('paginas/index_app', [
            'empresas' => $empresas,
            'alunos'   => $alunos
        ]);
        return;
    }

    // =======================
    // ALUNO (tipo 3)
    // Buscar até 3 últimos avisos ativos da empresa do aluno
    // respeitando datas de publicação/expiração
    // =======================
    $viewData = [
        'empresas' => $empresas,
        'alunos'   => $alunos
    ];

    if ($tipo === 3) {
        $avisos = [];
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
        if ($userId > 0) {
            require_once dirname(__DIR__) . '/Libraries/Database.php';
            $db = new Database();

            // Pegar a empresa do aluno
            $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
            $db->bind(':id', $userId);
            $u = $db->resultado();
            if ($u && !empty($u->empr_id)) {
                $emprId = intval($u->empr_id);

                // Buscar avisos ativos da empresa do aluno,
                // publicados já e não expirados (ou sem expiração)
                // COM NOME DO ADMIN QUE CRIOU
                $db->query(
                    "SELECT a.*, u.usua_nome AS admin_nome
                     FROM aviso a
                     LEFT JOIN usuario u ON u.usua_id = a.usua_id
                     WHERE a.empr_id = :empr
                       AND a.avis_situacao = 'ATIVO'
                       AND a.avis_publica_em <= DATE_ADD(NOW(), INTERVAL 6 HOUR)
                       AND (a.avis_expira_em IS NULL OR a.avis_expira_em > DATE_ADD(NOW(), INTERVAL 6 HOUR))
                     ORDER BY a.avis_publica_em DESC, a.avis_dth_criacao DESC
                     LIMIT 3"
                );
                $db->bind(':empr', $emprId);
                $avisos = $db->resultados() ?: [];

                // Buscar horários ativos da empresa do aluno
                $db->query(
                    "SELECT h.hori_id, h.hori_titulo AS titulo, h.hori_turno AS turno,
                            h.hori_hora_ida AS hora_ida, h.hori_hora_volta AS hora_volta,
                            h.hori_ponto AS ponto, h.hori_dias AS dias, h.hori_situacao AS situacao,
                            o.onib_modelo, o.onib_placa, o.onib_foto
                     FROM horario h
                     LEFT JOIN onibus o ON o.onib_id = h.onib_id
                     WHERE h.empr_id = :empr
                       AND h.hori_situacao = 'ATIVO'
                     ORDER BY h.hori_dth_criacao DESC"
                );
                $db->bind(':empr', $emprId);
                $horaRows = $db->resultados() ?: [];
                $horariosAluno = [];
                foreach ($horaRows as $r) {
                    $horariosAluno[] = [
                        'hori_id' => $r->hori_id,
                        'titulo' => $r->titulo,
                        'turno' => $r->turno,
                        'hora_ida' => $r->hora_ida,
                        'hora_volta' => $r->hora_volta,
                        'ponto' => $r->ponto,
                        'dias' => $r->dias,
                        'situacao' => $r->situacao,
                        'onib_modelo' => $r->onib_modelo,
                        'onib_placa' => $r->onib_placa,
                        'onib_foto' => $r->onib_foto,
                    ];
                }
                $viewData['horariosAluno'] = $horariosAluno;
            }
        }

        $viewData['avisos'] = $avisos;
    }

    $this->view('paginas/index_app', $viewData);
}


    public function perfil(){
        if (session_status() === PHP_SESSION_NONE) session_start();
        $usuario = null;
        $aluno = null;
        $docs = [];

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        require_once dirname(__DIR__) . '/Libraries/Database.php';
        try {
            $db = new Database();
            // Carrega usuário com possíveis dados da empresa
            $db->query("SELECT u.*, e.empr_nome FROM usuario u LEFT JOIN empresa e ON e.empr_id = u.empr_id WHERE u.usua_id = :id LIMIT 1");
            $db->bind(':id', $_SESSION['user_id']);
            $res = $db->resultado();

            if ($res) {
                // Anexa e-mail principal (tabela email) ao objeto retornado, se existir.
                try {
                    $db->query("SELECT emai_endereco FROM email WHERE usua_id = :id AND is_principal = 1 LIMIT 1");
                    $db->bind(':id', $_SESSION['user_id']);
                    $emr = $db->resultado();
                    $res->usua_email = $emr && !empty($emr->emai_endereco) ? $emr->emai_endereco : '';

                    // fallback: se não houver e-mail principal, pega qualquer e-mail cadastrado
                    if (empty($res->usua_email)) {
                        $db->query("SELECT emai_endereco FROM email WHERE usua_id = :id LIMIT 1");
                        $db->bind(':id', $_SESSION['user_id']);
                        $ef = $db->resultado();
                        $res->usua_email = $ef && !empty($ef->emai_endereco) ? $ef->emai_endereco : '';
                    }

                    // busca telefone principal
                    $db->query("SELECT tele_numero FROM telefone WHERE usua_id = :id AND is_principal = 1 LIMIT 1");
                    $db->bind(':id', $_SESSION['user_id']);
                    $tr = $db->resultado();
                    $res->usua_telefone = $tr && !empty($tr->tele_numero) ? $tr->tele_numero : '';
                } catch (Throwable $t) {
                    $res->usua_email = $res->usua_email ?? '';
                    $res->usua_telefone = $res->usua_telefone ?? '';
                }

                // Se for aluno (tipo 3) a view espera $aluno e $docs
                $userTipo = !empty($_SESSION['user_tipo']) ? (int)$_SESSION['user_tipo'] : 0;
                if ($userTipo === 3) {
                    $aluno = $res; // objeto retornado pelo Database

                    // Busca documentos do aluno
                    $db->query("SELECT docu_tipo, docu_url FROM documento WHERE usua_id = :uid");
                    $db->bind(':uid', $_SESSION['user_id']);
                    $docRows = $db->resultados() ?: [];
                    foreach ($docRows as $d) {
                        $docs[$d->docu_tipo] = $d->docu_url;
                    }
                } else {
                    // Admin / Root / outros
                    $usuario = $res;
                }
            }
        } catch (Throwable $t) {
            // em caso de erro, apenas garantir que a view receba algo válido
            $usuario = $usuario ?? null;
            $aluno = $aluno ?? null;
            $docs = $docs ?? [];
        }

        $viewData = [];
        if ($aluno) {
            $viewData['aluno'] = $aluno;
            $viewData['docs'] = $docs;
        }
        if ($usuario) $viewData['usuario'] = $usuario;

        $this->view('paginas/perfil', $viewData);
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
        // Buscar tópicos do fórum da mesma empresa do usuário
        $topics = [];
        $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $pageSize = 20;
        $offset = ($currentPage - 1) * $pageSize;
        $totalPages = 1;

        try {
            require_once dirname(__DIR__) . '/Libraries/Database.php';
            $db = new Database();

            // Descobrir empresa do usuário logado
            $emprId = null;
            if (!empty($_SESSION['user_id'])) {
                $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
                $db->bind(':id', $_SESSION['user_id']);
                $u = $db->resultado();
                if ($u && !empty($u->empr_id)) {
                    $emprId = intval($u->empr_id);
                }
            }

            // Contar total de tópicos da empresa
            $totalTopics = 0;
            if ($emprId) {
                $db->query("SELECT COUNT(*) AS total FROM forum f LEFT JOIN usuario u ON u.usua_id = f.usua_id WHERE f.foru_situacao = 'ABERTO' AND u.empr_id = :empr");
                $db->bind(':empr', $emprId);
                $countRes = $db->resultado();
                $totalTopics = intval($countRes->total ?? 0);
                $totalPages = max(1, ceil($totalTopics / $pageSize));

                // Buscar tópicos da empresa
                $db->query(
                    "SELECT f.foru_id, f.foru_titulo, f.foru_texto, f.foru_dth_criacao, f.foru_situacao,
                            u.usua_id AS autor_id, u.usua_nome AS autor_nome, u.usua_foto AS autor_foto,
                            (SELECT COUNT(*) FROM forum_resposta fr WHERE fr.foru_id = f.foru_id) AS respostas_count
                     FROM forum f
                     LEFT JOIN usuario u ON u.usua_id = f.usua_id
                     WHERE f.foru_situacao = 'ABERTO' AND u.empr_id = :empr
                     ORDER BY f.foru_dth_criacao DESC
                     LIMIT :limit OFFSET :offset"
                );
                $db->bind(':empr', $emprId);
                $db->bind(':limit', $pageSize);
                $db->bind(':offset', $offset);
                $rows = $db->resultados() ?: [];
                foreach ($rows as $r) {
                    $topics[] = [
                        'id' => $r->foru_id,
                        'titulo' => $r->foru_titulo,
                        'texto' => $r->foru_texto,
                        'dth_criacao' => $r->foru_dth_criacao,
                        'situacao' => $r->foru_situacao,
                        'autor_id' => $r->autor_id,
                        'autor_nome' => $r->autor_nome,
                        'autor_foto' => $r->autor_foto,
                        'respostas_count' => intval($r->respostas_count),
                    ];
                }
            }
        } catch (Throwable $t) {
            // não bloquear a página por erro no fórum
            $topics = [];
        }

        // =======================
        // FILTRO DE BUSCA (search)
        // =======================
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        if ($search !== '') {
            $search = '%' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '%'; // Sanitiza o valor de entrada
        }

        // Modificar consulta para incluir busca
        $query = "SELECT f.foru_id, f.foru_titulo, f.foru_texto, f.foru_dth_criacao, f.foru_situacao,
                            u.usua_id AS autor_id, u.usua_nome AS autor_nome, u.usua_foto AS autor_foto,
                            (SELECT COUNT(*) FROM forum_resposta fr WHERE fr.foru_id = f.foru_id) AS respostas_count
                     FROM forum f
                     LEFT JOIN usuario u ON u.usua_id = f.usua_id
                     WHERE f.foru_situacao = 'ABERTO' AND u.empr_id = :empr";

        if ($search !== '') {
            $query .= " AND (f.foru_titulo LIKE :search OR f.foru_texto LIKE :search)";
        }

        $query .= " ORDER BY f.foru_dth_criacao DESC
                     LIMIT :limit OFFSET :offset";

        $db->query($query);
        $db->bind(':empr', $emprId);
        if ($search !== '') {
            $db->bind(':search', $search);
        }
        $db->bind(':limit', $pageSize);
        $db->bind(':offset', $offset);

        $rows = $db->resultados() ?: [];
        $topics = []; // Reinicializa para a nova consulta
        foreach ($rows as $r) {
            $topics[] = [
                'id' => $r->foru_id,
                'titulo' => $r->foru_titulo,
                'texto' => $r->foru_texto,
                'dth_criacao' => $r->foru_dth_criacao,
                'situacao' => $r->foru_situacao,
                'autor_id' => $r->autor_id,
                'autor_nome' => $r->autor_nome,
                'autor_foto' => $r->autor_foto,
                'respostas_count' => intval($r->respostas_count),
            ];
        }

        $this->view('paginas/forum', ['usuario' => $usuario, 'topics' => $topics, 'currentPage' => $currentPage, 'totalPages' => $totalPages]);
    }
    public function manha(){
        $this->view('paginas/manha');
    }
    public function tarde(){
        $this->view('paginas/tarde');
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
        // usar view helper em vez de require_once direto
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

public function detalhe_aluno()
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Somente ADMIN (tipo 2)
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    $adminId = intval($_SESSION['user_id']);
    $alunoId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($alunoId <= 0) {
        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    require_once dirname(__DIR__) . '/Libraries/Database.php';
    $db = new Database();

    // Empresa do ADMIN
    $db->query("SELECT empr_id FROM usuario WHERE usua_id = :id LIMIT 1");
    $db->bind(':id', $adminId);
    $admRow = $db->resultado();

    if (!$admRow || empty($admRow->empr_id)) {
        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    $emprId = intval($admRow->empr_id);

    // Carrega ALUNO já aprovado (tipo 3) desta mesma empresa
    $db->query("
        SELECT 
            u.*,
            e.empr_nome,
            e.empr_cnpj
        FROM usuario u
        INNER JOIN empresa e ON e.empr_id = u.empr_id
        WHERE u.usua_id = :uid
          AND u.empr_id = :empr
          AND u.tius_id = 3
        LIMIT 1
    ");
    $db->bind(':uid', $alunoId);
    $db->bind(':empr', $emprId);
    $aluno = $db->resultado();

    if (!$aluno) {
        header('Location: ' . URL . '/paginas/index_app');
        exit;
    }

    // Adiciona emails e telefones ao objeto aluno
    try {
        // Email principal
        $db->query("SELECT emai_endereco FROM email WHERE usua_id = :uid AND is_principal = 1 LIMIT 1");
        $db->bind(':uid', $alunoId);
        $emailRow = $db->resultado();
        $aluno->email_principal = $emailRow && !empty($emailRow->emai_endereco) ? $emailRow->emai_endereco : '';

        // Email secundário
        $db->query("SELECT emai_endereco FROM email WHERE usua_id = :uid AND is_principal = 0 LIMIT 1");
        $db->bind(':uid', $alunoId);
        $emailSecRow = $db->resultado();
        $aluno->email_secundario = $emailSecRow && !empty($emailSecRow->emai_endereco) ? $emailSecRow->emai_endereco : '';

        // Telefone principal (aluno)
        $db->query("SELECT tele_numero FROM telefone WHERE usua_id = :uid AND is_principal = 1 LIMIT 1");
        $db->bind(':uid', $alunoId);
        $teleRow = $db->resultado();
        $aluno->telefone = $teleRow && !empty($teleRow->tele_numero) ? $teleRow->tele_numero : '';

        // Telefone responsável
        $db->query("SELECT tele_numero FROM telefone WHERE usua_id = :uid AND is_principal = 0 LIMIT 1");
        $db->bind(':uid', $alunoId);
        $teleRespRow = $db->resultado();
        $aluno->telefone_responsavel = $teleRespRow && !empty($teleRespRow->tele_numero) ? $teleRespRow->tele_numero : '';
    } catch (Throwable $t) {
        // Em caso de erro, apenas garante que os campos existam
        $aluno->email_principal = $aluno->email_principal ?? '';
        $aluno->email_secundario = $aluno->email_secundario ?? '';
        $aluno->telefone = $aluno->telefone ?? '';
        $aluno->telefone_responsavel = $aluno->telefone_responsavel ?? '';
    }

    // ================================
    // DOCUMENTOS (tabela documento)
    // ================================
    $db->query("
        SELECT docu_tipo, docu_url
        FROM documento
        WHERE usua_id = :uid
    ");
    $db->bind(':uid', $alunoId);
    $docRows = $db->resultados() ?: [];

    $docs = [];
    foreach ($docRows as $d) {
        // Ex: MATRÍCULA, RESIDENCIA, RG, CPF, RESPONSAVEL
        $docs[$d->docu_tipo] = $d->docu_url;
    }

    // ================================
    // CONTRATO (mais recente do aluno)
    // ================================
    $db->query("
        SELECT 
            c.*,
                        e.empr_contrato_url,
                        e.empr_nome,
                        e.empr_cnpj,
                        e.empr_pix_url,
                        e.empr_chave_pix,
                        e.empr_vlr_mensalidade
        FROM contrato c
        INNER JOIN empresa e ON e.empr_id = c.empr_id
        WHERE c.usua_id = :uid
          AND c.empr_id = :empr
        ORDER BY c.cont_dth_criacao DESC
        LIMIT 1
    ");
    $db->bind(':uid', $alunoId);
    $db->bind(':empr', $emprId);
    $contrato = $db->resultado();

    // ================================
    // MENSALIDADE (competência atual)
    // ================================
    $competencia = date('Y-m'); // Ex.: 2025-11
    $pagamento   = null;

    if ($contrato) {
        $db->query("
            SELECT *
            FROM pagamento
            WHERE usua_id = :uid
              AND cont_id = :cid
              AND paga_competencia = :comp
            LIMIT 1
        ");
        $db->bind(':uid', $alunoId);
        $db->bind(':cid', $contrato->cont_id);
        $db->bind(':comp', $competencia);
        $pagamento = $db->resultado();
    }

    $this->view('paginas/detalhe_aluno', [
        'aluno'       => $aluno,
        'docs'        => $docs,
        'contrato'    => $contrato,
        'competencia' => $competencia,
        'pagamento'   => $pagamento
    ]);
}






    /**
     * GET /paginas/cadastro_aviso
     * Exibe formulário para criar novo aviso
     */
    public function cadastro_aviso() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }
        
        $this->view('paginas/cadastro_aviso', []);
    }

    /**
     * GET /paginas/editar_aviso?id=X
     * Exibe formulário para editar aviso existente
     */
    public function editar_aviso() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || empty($_SESSION['user_tipo']) || intval($_SESSION['user_tipo']) !== 2) {
            header('Location: ' . URL . '/paginas/entrar');
            exit;
        }

        if (empty($_GET['id'])) {
            header('Location: ' . URL . '/avisos');
            exit;
        }

        $avisId = (int) $_GET['id'];
        require_once dirname(__DIR__) . '/Libraries/Database.php';
        $db = new Database();

        $db->query("SELECT * FROM aviso WHERE avis_id = :id LIMIT 1");
        $db->bind(':id', $avisId);
        $aviso = $db->resultado();

        if (!$aviso) {
            header('Location: ' . URL . '/avisos?erro=' . urlencode('Aviso não encontrado.'));
            exit;
        }

        $this->view('paginas/cadastro_aviso', ['aviso' => $aviso]);
    }

}//fim da classe Paginas
?>


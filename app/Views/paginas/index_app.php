<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
</head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<section class="app-layout">
  <aside class="sidebar">
    <div class="profile">
      <?php
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userPhoto = null;
        if (!empty($_SESSION['user_id'])) {
          require_once dirname(__DIR__,2) . '/Libraries/Database.php';
          try {
            $db = new Database();
            $db->query('SELECT usua_foto FROM usuario WHERE usua_id = :id LIMIT 1');
            $db->bind(':id', $_SESSION['user_id']);
            $r = $db->resultado();
            if ($r && !empty($r->usua_foto)) $userPhoto = $r->usua_foto;
          } catch (Throwable $t) {
            $userPhoto = null;
          }
        }
      ?>
      <div class="avatar">
        <?php if ($userPhoto): ?>
          <img src="<?= URL ?>/<?= htmlspecialchars($userPhoto) ?>" alt="Avatar" />
        <?php else: ?>
          <i class="bi bi-person-fill"></i>
        <?php endif; ?>
      </div>
      <strong class="user-name"><?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?></strong>
    </div>

    <?php if (!empty($_SESSION['user_tipo'])): ?>
    <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

    <nav class="menu">
      <!-- ======== ROOT ======== -->
      <?php if ($tipoUsuario === 1): ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-house-door-fill"></i>
          <span>Início</span>
        </a>
        <a href="<?=URL?>/paginas/#" class="item">
          <i class="bi bi-buildings-fill"></i>
          <span>Empresas</span>
        </a>
        <a href="<?=URL?>/paginas/#" class="item">
          <i class="bi bi-people-fill"></i>
          <span>Administradores</span>
        </a>
        <a href="<?=URL?>/paginas/#" class="item">
          <i class="bi bi-gear-fill"></i>
          <span>Alunos</span>
        </a>

      <!-- ======== ADMIN ======== -->
      <?php elseif ($tipoUsuario === 2): ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-house-door-fill"></i>
          <span>Início</span>
        </a>
        <a href="<?=URL?>/paginas/solicitacoes" class="item">
          <i class="bi bi-person-check-fill"></i>
          <span>Solicitações</span>
        </a>
        <a href="<?=URL?>/paginas/onibus" class="item">
          <i class="bi bi-bus-front-fill"></i>
          <span>Ônibus</span>
        </a>
        <a href="<?=URL?>/paginas/motoristas" class="item">
          <i class="bi bi-person-vcard-fill"></i>
          <span>Motoristas</span>
        </a>
        <a href="<?=URL?>/paginas/relatorios" class="item">
          <i class="bi bi-clipboard-data-fill"></i>
          <span>Relatórios</span>
        </a>

      <!-- ======== ALUNO ======== -->
      <?php else: ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-house-door-fill"></i>
          <span>Início</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
      <?php endif; ?>
    </nav>
  <?php endif; ?>

  </aside>

  <main class="content">
  <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

  <?php if (!empty($_SESSION['admin_cadastrado'])): ?>
    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
      Administrador cadastrado com sucesso!
    </div>
    <?php unset($_SESSION['admin_cadastrado']); ?>
  <?php endif; ?>

  <header class="page-head">
    <h1>👋 Bem-vindo, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?>!</h1>
  </header>

  <?php
    // garante variável para a view
    $empresas = isset($empresas) && is_array($empresas) ? $empresas : [];
  ?>

  <?php if (!empty($_SESSION['user_tipo'])): ?>
    <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

    <?php if ($tipoUsuario === 1): ?>
      <!-- =======================
           ÁREA DO ROOT (Tipo 1)
           ======================= -->
      <section class="root-section">
        <div class="root-head">
          <h2>Empresas cadastradas</h2>
        </div>

        <!-- Barra de busca + Cadastrar empresa -->
        <form class="company-search" method="get" action="<?=URL?>/paginas/index_app">
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
                   placeholder="Pesquisar por nome da empresa, CNPJ ou razão social...">
          </div>

          <div class="action-wrap">
            <button class="btn-outline" type="submit">Buscar</button>
            <a class="btn-primary" href="<?=URL?>/paginas/cadastro_empresa">
              <i class="bi bi-building-add"></i> Cadastrar empresa
            </a>
          </div>
        </form>

        <!-- Grid de empresas -->
        <!-- Grid de empresas -->
        <div class="company-grid">
          <?php if (empty($empresas)): ?>
            <div class="company-empty">
              <i class="bi bi-inboxes"></i>
              <p>Nenhuma empresa encontrada.</p>
              <!-- (sem botão extra aqui) -->
            </div>
          <?php else: ?>
            <?php foreach ($empresas as $e): ?>
              <a class="company-card link-card" href="<?=URL?>/paginas/detalhe_empresa?id=<?= urlencode($e['id'] ?? '') ?>">
                <div class="company-logo">
                  <?php if (!empty($e['logo'])): ?>
                    <img src="<?= URL.'/'.$e['logo'] ?>" alt="Logo da empresa <?= htmlspecialchars($e['nome'] ?? '') ?>">
                  <?php else: ?>
                    <div class="no-logo"><i class="bi bi-buildings"></i></div>
                  <?php endif; ?>
                </div>

                <div class="company-body">
                  <h3 class="company-name"><?= htmlspecialchars($e['nome'] ?? '—') ?></h3>
                  <div class="company-meta">
                    <span><i class="bi bi-file-earmark-text"></i> CNPJ: <?= 
                      preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', 
                        preg_replace('/\D/', '', $e['cnpj'] ?? '')) 
                    ?></span>
                    <span><i class="bi bi-card-text"></i> Razão: <?= htmlspecialchars($e['razao'] ?? '—') ?></span>
                  </div>

                  <div class="company-footer">
                    <span class="badge">
                      <i class="bi bi-people-fill"></i>
                      <?= intval($e['admins'] ?? 0) ?> admin<?= (intval($e['admins'] ?? 0) === 1 ? '' : 's') ?>
                    </span>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </section>

    <?php elseif ($tipoUsuario === 2): ?>
      <!-- =======================
           ÁREA DO ADMIN (Tipo 2)
           ======================= -->
      <section class="admin-section">
        <h2>Área do Administrador</h2>
        <div class="admin-tools">
          <!-- futuras ferramentas do administrador da empresa -->
        </div>
      </section>

    <?php else: ?>
      <!-- =======================
           ÁREA DO ALUNO (Tipo 3)
           ======================= -->
      <section class="student-section">
        <h2>Área do Aluno</h2>

        <section class="avisos">
          <h2>Avisos:</h2>
          <div class="box">• •</div>
        </section>

        <section class="horarios">
          <h2>Horários</h2>
          <div class="cards-2col">
            <article class="bus-card">
              <a href="<?=URL?>/paginas/manha" class="card-link"><h3>Manhã</h3></a>
              <a href="<?=URL?>/paginas/manha" class="card-link"><img src="<?=URL?>/public/img/onibus-escolar.png" alt="Ônibus no turno da manhã"></a>
            </article>

            <article class="bus-card">
              <a href="<?=URL?>/paginas/tarde" class="card-link"><h3>Tarde</h3></a>
              <a href="<?=URL?>/paginas/tarde" class="card-link"><img src="<?=URL?>/public/img/onibus-escolar.png" alt="Ônibus no turno da tarde"></a>
            </article>
          </div>
        </section>
      </section>
    <?php endif; ?>
  <?php endif; ?>
</main>
</section>

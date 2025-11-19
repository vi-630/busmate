<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Carrega foto e nome do admin
$userPhoto = null;
$userName  = $_SESSION['user_name'] ?? 'Admin';

if (!empty($_SESSION['user_id'])) {
    require_once dirname(__DIR__,2) . '/Libraries/Database.php';
    try {
        $db = new Database();
        $db->query('SELECT usua_foto, usua_nome FROM usuario WHERE usua_id = :id LIMIT 1');
        $db->bind(':id', $_SESSION['user_id']);
        $r = $db->resultado();

        if ($r) {
            if (!empty($r->usua_foto)) $userPhoto = $r->usua_foto;
            if (!empty($r->usua_nome)) $userName  = $r->usua_nome;
        }
    } catch (Throwable $t) {}
}

// garante $avisos como array
$avisos = isset($avisos) && is_array($avisos) ? $avisos : [];
?>
<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/avisos.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile">
      <div class="avatar">
        <?php if ($userPhoto): ?>
          <img src="<?= URL ?>/<?= htmlspecialchars($userPhoto) ?>" alt="Avatar" />
        <?php else: ?>
          <i class="bi bi-person-fill"></i>
        <?php endif; ?>
      </div>
      <strong class="user-name"><?= htmlspecialchars($userName) ?></strong>
    </div>

    <nav class="menu">
      <a href="<?=URL?>/paginas/index_app" class="item">
          <i class="bi bi-people-fill"></i>
          <span>Alunos</span>
        </a>
        <a href="<?=URL?>/avisos" class="item active">
          <i class="bi bi-bell-fill"></i>
          <span>Avisos</span>
        </a>
        <a href="<?=URL?>/viagens/minhas" class="item">
          <i class="bi bi-bus-front-fill"></i>
          <span>Viagens</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
        <a href="<?=URL?>/solicitacoes/index" class="item">
          <i class="bi bi-list-check"></i>
          <span>Solicitações</span>
      </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
    </nav>
  </aside>

  <!-- CONTEÚDO -->
  <main class="content">
    <div class="avisos-wrapper">

      <!-- ALERTAS -->
      <?php if (!empty($_GET['erro'])): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle"></i>
          <?= htmlspecialchars($_GET['erro']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle"></i>
          <?= htmlspecialchars($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <!-- CABEÇALHO -->
      <header class="avisos-head">
        <h1 class="avisos-title">Avisos</h1>

        <form class="avisos-search" method="get" action="<?=URL?>/avisos">
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input
              type="text"
              name="q"
              value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
              placeholder="Buscar avisos..."
            >
          </div>

          <a href="<?=URL?>/paginas/cadastro_aviso" class="btn-primary btn-novo-aviso">
            <i class="bi bi-plus-lg"></i> Novo aviso
          </a>
        </form>
      </header>

      <!-- LISTA DE AVISOS -->
      <?php if (empty($avisos)): ?>
        <div class="avisos-empty">
          <i class="bi bi-inbox"></i>
          <p>Nenhum aviso cadastrado ainda.</p>
        </div>
      <?php else: ?>
        <section class="avisos-list">
          <?php foreach ($avisos as $aviso): ?>
            <?php
              $statusClass = $aviso->avis_situacao === 'ATIVO' ? 'badge-ativo' : 'badge-inativo';
            ?>
            <article class="aviso-card">
              <header class="aviso-card-head">
                <div>
                  <h2 class="aviso-title">
                    <?= htmlspecialchars($aviso->avis_titulo ?: '(Sem título)') ?>
                  </h2>
                  <span class="badge <?= $statusClass ?>">
                    <?= htmlspecialchars($aviso->avis_situacao) ?>
                  </span>
                </div>
                <small class="aviso-time">
                  Criado em <?= date('d/m/Y H:i', strtotime($aviso->avis_dth_criacao)) ?>
                </small>
              </header>

              <p class="aviso-text">
                <?= nl2br(htmlspecialchars($aviso->avis_texto)) ?>
              </p>

              <footer class="aviso-card-footer">
                <div class="aviso-meta">
                  <?php if (!empty($aviso->avis_publica_em)): ?>
                    <span><i class="bi bi-clock"></i>
                      Publicado em <?= date('d/m/Y H:i', strtotime($aviso->avis_publica_em)) ?>
                    </span>
                  <?php endif; ?>

                  <?php if (!empty($aviso->avis_expira_em)): ?>
                    <span><i class="bi bi-hourglass-end"></i>
                      Expira em <?= date('d/m/Y H:i', strtotime($aviso->avis_expira_em)) ?>
                    </span>
                  <?php endif; ?>
                </div>

                <div class="aviso-actions">
                  <a href="<?=URL?>/paginas/editar_aviso?id=<?= (int)$aviso->avis_id ?>" class="btn-link">
                    <i class="bi bi-pencil-square"></i> Editar
                  </a>
                  <a href="<?=URL?>/avisos/excluir?id=<?= (int)$aviso->avis_id ?>"
                     class="btn-link btn-danger"
                     onclick="return confirm('Tem certeza que deseja excluir este aviso?');">
                    <i class="bi bi-trash3"></i> Excluir
                  </a>
                </div>
              </footer>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>

    </div>
  </main>
</section>

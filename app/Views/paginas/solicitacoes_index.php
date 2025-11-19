<?php
/* recebe:
   - $solis  (array de objetos da solicitacao_aluno)
   - $filtro ('pendente'|'aceita'|'recusada')
*/
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/solicitacoes.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout"><!-- 🔥 layout padrão: sidebar + conteúdo -->

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile">
      <?php
        $userPhoto = null;
        $userName  = $_SESSION['user_name'] ?? 'Fulano';

        if (!empty($_SESSION['user_id'])) {
          require_once dirname(__DIR__,2) . '/Libraries/Database.php';
          try {
            $db = new Database();
            $db->query('SELECT usua_foto, usua_nome FROM usuario WHERE usua_id = :id LIMIT 1');
            $db->bind(':id', $_SESSION['user_id']);
            $r = $db->resultado();
            if ($r) {
              if (!empty($r->usua_foto)) $userPhoto = $r->usua_foto;
              if (!empty($r->usua_nome)) {
                $userName = $r->usua_nome;
                $_SESSION['user_name'] = $r->usua_nome;
              }
            }
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
      <strong class="user-name"><?= htmlspecialchars($userName) ?></strong>
    </div>

    <?php if (!empty($_SESSION['user_tipo'])): ?>
      <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

      <nav class="menu">
        <a href="<?=URL?>/paginas/index_app" class="item">
          <i class="bi bi-people-fill"></i>
          <span>Alunos</span>
        </a>
        <a href="<?=URL?>/avisos" class="item">
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
        <a href="<?=URL?>/solicitacoes/index" class="item active">
          <i class="bi bi-list-check"></i>
          <span>Solicitações</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
      </nav>
    <?php endif; ?>
  </aside>

  <!-- CONTEÚDO -->
  <main class="content">
    <section class="soli-section">
      <div class="soli-hero"></div>
      <div class="soli-wrap">

        <header class="soli-head">
          <a class="back-link" href="<?=URL?>/paginas/index_app">
            <i class="bi bi-arrow-left"></i><span>Voltar</span>
          </a>
          <h1>Solicitações de alunos</h1>
        </header>

        <nav class="soli-tabs">
          <a class="tab <?= $filtro==='pendente'?'active':'' ?>" href="<?=URL?>/solicitacoes/index?f=pendente">Pendentes</a>
          <a class="tab <?= $filtro==='aceita'?'active':'' ?>"   href="<?=URL?>/solicitacoes/index?f=aceita">Aceitas</a>
          <a class="tab <?= $filtro==='recusada'?'active':'' ?>" href="<?=URL?>/solicitacoes/index?f=recusada">Recusadas</a>
        </nav>

        <?php if (empty($solis)): ?>
          <div class="empty-box">
            <i class="bi bi-inboxes"></i>
            <p>Nenhuma solicitação <?= htmlspecialchars($filtro) ?>.</p>
          </div>
        <?php else: ?>
          <div class="soli-grid">
            <?php foreach ($solis as $s): ?>
              <a class="soli-card" href="<?=URL?>/solicitacoes/ver?id=<?= urlencode($s->soli_id) ?>">
                <div class="soli-avatar">
                  <?php if (!empty($s->soli_foto_url)): ?>
                    <img src="<?= URL . '/' . htmlspecialchars($s->soli_foto_url) ?>" alt="Foto de <?= htmlspecialchars($s->soli_nome) ?>">
                  <?php else: ?>
                    <i class="bi bi-person-circle"></i>
                  <?php endif; ?>
                </div>
                <div class="soli-body">
                  <h3 class="soli-name"><?= htmlspecialchars($s->soli_nome) ?></h3>
                  <p class="soli-meta">
                    <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($s->soli_email) ?>
                    <?php if (!empty($s->soli_tel)): ?>
                      &nbsp;&middot;&nbsp;<i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($s->soli_tel) ?>
                    <?php endif; ?>
                  </p>
                  <div class="soli-foot">
                    <span class="badge badge-<?= strtolower($s->soli_status) ?>"><?= htmlspecialchars($s->soli_status) ?></span>
                    <span class="date">
                      <i class="bi bi-calendar-event"></i>
                      <?php
                        $dt = $s->soli_dth_criacao ? strtotime($s->soli_dth_criacao) : null;
                        echo $dt ? date('d/m/Y H:i', $dt) : '—';
                      ?>
                    </span>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </section>
  </main>
</section>

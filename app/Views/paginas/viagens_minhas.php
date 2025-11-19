<?php
if (session_status() === PHP_SESSION_NONE) session_start();

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

// garante arrays vindos do controller
$horarios    = isset($horarios)    && is_array($horarios)    ? $horarios    : [];
$onibusList  = isset($onibusList)  && is_array($onibusList)  ? $onibusList  : [];
?>
<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/viagens.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

  <!-- SIDEBAR ADMIN -->
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
      <a href="<?=URL?>/avisos" class="item">
          <i class="bi bi-bell-fill"></i>
          <span>Avisos</span>
      </a>
        <a href="<?=URL?>/viagens/minhas" class="item active">
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

  <main class="content">

    <header class="page-head">
      <h1>Viagens</h1>
    </header>

    <section class="viagens-wrapper">

      <!-- ALERTAS GERAIS -->
      <?php if (!empty($_GET['ok'])): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($_GET['ok']) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['erro'])): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_GET['erro']) ?>
        </div>
      <?php endif; ?>


      <!-- ========== BLOCO ÔNIBUS ========== -->
      <section class="viagens-panel">
        <header class="panel-head">
          <h2 class="panel-title">Ônibus</h2>

          <a href="<?=URL?>/onibus/novo" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Novo
          </a>
        </header>

        <?php if (empty($onibusList)): ?>
          <div class="panel-empty">
            <i class="bi bi-bus-front"></i>
            <p>Nenhum ônibus cadastrado ainda.</p>
            <p class="panel-help">Cadastre um ônibus para associar aos horários.</p>
          </div>
        <?php else: ?>
          <div class="onibus-grid">
            <?php foreach ($onibusList as $o): ?>
              <article class="onibus-card">
                <div class="onibus-img">
                  <img
                    src="<?= URL ?>/<?= htmlspecialchars($o->onib_foto ?? 'public/img/onibus-escolar.png') ?>"
                    alt="Ônibus"
                  >
                </div>

                <div class="onibus-main">
                  <h3 class="onibus-title">
                    <?= htmlspecialchars($o->onib_modelo ?? 'Ônibus') ?>
                  </h3>

                  <p class="onibus-meta">
                    <?php if (!empty($o->onib_placa)): ?>
                      <span><i class="bi bi-credit-card-2-front"></i> Placa <?= htmlspecialchars($o->onib_placa) ?></span>
                    <?php endif; ?>
                  </p>

                  <footer class="onibus-footer">
                    <span class="badge <?= ($o->onib_situacao ?? 'ATIVO') === 'ATIVO' ? 'badge-ativo' : 'badge-inativo' ?>">
                      <?= htmlspecialchars($o->onib_situacao ?? 'ATIVO') ?>
                    </span>

                    <div class="onibus-actions">
                      <a href="<?=URL?>/onibus/editar/<?= (int)$o->onib_id ?>" class="btn-link">
                        <i class="bi bi-pencil"></i> Editar
                      </a>
                      <a
                        href="<?=URL?>/onibus/deletar/<?= (int)$o->onib_id ?>"
                        class="btn-link btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir este ônibus?');"
                      >
                        <i class="bi bi-trash"></i> Excluir
                      </a>
                    </div>
                  </footer>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>


      <!-- ========== BLOCO VIAGENS / HORÁRIOS ========== -->
      <section class="viagens-panel">
        <header class="panel-head">
          <h2 class="panel-title">Horários</h2>

          <?php if (empty($onibusList)): ?>
            <!-- Só front: avisa que precisa de ônibus; regra real fica no backend -->
            <button type="button" class="btn-primary btn-disabled" title="Cadastre ao menos um ônibus para criar horários" disabled>
              <i class="bi bi-plus-lg"></i> Novo
            </button>
          <?php else: ?>
            <a href="<?=URL?>/viagens/cadastro" class="btn-primary">
              <i class="bi bi-plus-lg"></i> Novo
            </a>
          <?php endif; ?>
        </header>

        <?php if (empty($horarios)): ?>
          <div class="panel-empty">
            <i class="bi bi-clock-history"></i>
            <p>Nenhum horário cadastrado ainda.</p>
          </div>
        <?php else: ?>
          <div class="viagens-list">
            <?php foreach ($horarios as $h): ?>
              <article class="viagem-card">
                <div class="viagem-img">
                  <img
                    src="<?= URL ?>/<?= htmlspecialchars($h->onib_foto ?? 'public/img/onibus-escolar.png') ?>"
                    alt="Ônibus"
                  >
                </div>

                <div class="viagem-main">
                  <header class="viagem-head">
                    <div>
                      <h3 class="viagem-title">
                        <?= htmlspecialchars($h->hori_titulo ?? ($h->hori_turno ?? 'Horário')) ?>
                      </h3>
                      <p class="viagem-sub">
                        <?php if (!empty($h->onib_modelo) || !empty($h->onib_placa)): ?>
                          <i class="bi bi-truck-front"></i>
                          <?= htmlspecialchars($h->onib_modelo ?? '') ?>
                          <?php if (!empty($h->onib_placa)): ?>
                            — Placa <?= htmlspecialchars($h->onib_placa) ?>
                          <?php endif; ?>
                        <?php else: ?>
                          <span>Ônibus não informado</span>
                        <?php endif; ?>
                      </p>
                    </div>

                    <span class="badge <?= ($h->hori_situacao ?? 'ATIVO') === 'ATIVO' ? 'badge-ativo' : 'badge-inativo' ?>">
                      <?= htmlspecialchars($h->hori_situacao ?? 'ATIVO') ?>
                    </span>
                  </header>

                  <div class="viagem-body">
                    <p class="viagem-info">
                      <i class="bi bi-clock"></i>
                      Saída: <?= substr($h->hori_hora_ida, 0, 5) ?>
                      <?php if (!empty($h->hori_hora_volta)): ?>
                        • Volta: <?= substr($h->hori_hora_volta, 0, 5) ?>
                      <?php endif; ?>
                    </p>

                    <?php if (!empty($h->hori_ponto)): ?>
                      <p class="viagem-extra">
                        <i class="bi bi-geo-alt"></i>
                        Ponto principal: <?= htmlspecialchars($h->hori_ponto) ?>
                      </p>
                    <?php endif; ?>

                    <?php if (!empty($h->hori_dias)): ?>
                      <p class="viagem-extra">
                        <i class="bi bi-calendar2-week"></i>
                        Dias: <?= htmlspecialchars($h->hori_dias) ?>
                      </p>
                    <?php endif; ?>
                  </div>

                  <footer class="viagem-footer">
                    <div class="viagem-actions">
                      <a href="<?=URL?>/viagens/editar?id=<?= (int)$h->hori_id ?>" class="btn-link">
                        <i class="bi bi-pencil"></i> Editar
                      </a>
                      <a
                        href="<?=URL?>/viagens/excluir?id=<?= (int)$h->hori_id ?>"
                        class="btn-link btn-danger"
                        onclick="return confirm('Tem certeza que deseja excluir este horário?');"
                      >
                        <i class="bi bi-trash"></i> Excluir
                      </a>
                    </div>
                  </footer>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

    </section>

  </main>
</section>

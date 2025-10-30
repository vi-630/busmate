<?php
// Espera receber de Paginas::detalhe_empresa():
// $empresa -> objeto com: empr_id, empr_nome, empr_cnpj, empr_razao, empr_logo, empr_dth_criacao, criador_nome, criador_id
// $admins  -> array de objetos com: usua_id, usua_nome, usua_foto, email_principal, telefone_principal
?>
<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/detalhe_empresa.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="company-detail-section">
  <div class="company-hero-bg"></div>

  <div class="company-wrap">
    <!-- Cabeçalho -->
    <header class="cd-head">
      <a class="back-link" href="<?=URL?>/paginas/index_app">
        <i class="bi bi-arrow-left"></i><span>Voltar</span>
      </a>
      <h1 class="cd-title">Detalhes da empresa</h1>
    </header>

    <!-- Card principal (layout igual ao mock) -->
    <article class="cd-card">
      <!-- LOGO -->
      <div class="logo-box <?= empty($empresa->empr_logo) ? 'no-logo' : '' ?>" aria-label="Logo da empresa">
        <?php if (!empty($empresa->empr_logo)): ?>
          <img src="<?= URL . '/' . htmlspecialchars($empresa->empr_logo) ?>" alt="Logo da empresa">
        <?php else: ?>
          <i class="bi bi-buildings"></i>
        <?php endif; ?>
      </div>

      <!-- LADO DIREITO (título + Editar, campos) -->
      <div class="cd-right">
        <div class="cd-topline">
          <h2 class="company-name"><?= htmlspecialchars($empresa->empr_nome ?? '—') ?></h2>
          <a class="btn-edit" href="<?=URL?>/paginas/editar_empresa?id=<?= urlencode($empresa->empr_id ?? '') ?>">
            <i class="bi bi-pencil"></i> Editar
          </a>
        </div>

        <div class="cd-fields">
          <p class="field-chip"><strong>CNPJ:</strong> <?= htmlspecialchars($empresa->empr_cnpj ?? '—') ?></p>
          <p class="field-chip"><strong>Razão social:</strong> <?= htmlspecialchars($empresa->empr_razao ?? '—') ?></p>
        </div>
      </div>

      <!-- PÍLULAS INFERIORES (spana as 2 colunas) -->
      <div class="meta-pills">
        <span class="pill">
          <i class="bi bi-calendar-event"></i>
          <span>Criado em:</span>
          <strong>
            <?php
              $dt = !empty($empresa->empr_dth_criacao) ? strtotime($empresa->empr_dth_criacao) : null;
              echo $dt ? date('d/m/Y H:i', $dt) : '—';
            ?>
          </strong>
        </span>

        <span class="pill">
          <i class="bi bi-person-badge"></i>
          <span>Cadastrada por:</span>
          <strong><?= formatarNome($empresa->criador_nome ?? '—') ?></strong>
        </span>

        <span class="pill">
          <i class="bi bi-people"></i>
          <span>Admins atuais:</span>
          <strong><?= is_array($admins) ? count($admins) : 0 ?></strong>
        </span>
      </div>
    </article>

    <!-- Lista de Administradores (mantida como cards clicáveis) -->
    <section class="admin-list-section">
      <div class="als-head">
        <h3>Administradores</h3>
        <a class="btn-primary btn-sm" href="<?=URL?>/paginas/cadastro_admin_empresa?id=<?= urlencode($empresa->empr_id ?? '') ?>&faltam=1">
          <i class="bi bi-person-plus-fill"></i> Adicionar
        </a>
      </div>

      <?php if (empty($admins)): ?>
        <div class="empty-box">
          <i class="bi bi-inboxes"></i>
          <p>Nenhum administrador cadastrado.</p>
        </div>
      <?php else: ?>
        <div class="admin-grid">
          <?php foreach ($admins as $a): ?>
            <a class="admin-card link-card" href="<?=URL?>/paginas/detalhe_admin?id=<?= urlencode($a->usua_id ?? '') ?>">
              <div class="admin-avatar">
                <?php if (!empty($a->usua_foto)): ?>
                  <img src="<?= URL . '/' . htmlspecialchars($a->usua_foto) ?>" alt="Foto de <?= htmlspecialchars($a->usua_nome ?? '') ?>">
                <?php else: ?>
                  <i class="bi bi-person-circle"></i>
                <?php endif; ?>
              </div>

              <div class="admin-body">
                <h4 class="admin-name"><?= htmlspecialchars($a->usua_nome ?? '—') ?></h4>
                <div class="admin-meta">
                  <span><i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($a->email_principal ?? '—') ?></span>
                  <span><i class="bi bi-telephone-fill"></i> <?= htmlspecialchars($a->telefone_principal ?? '—') ?></span>
                </div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</section>

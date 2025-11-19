<?php /* app/Views/paginas/escolher_empresa.php */ ?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/escolher_empresa.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="choose-section">
  <div class="choose-wrap">
    <header class="choose-head">
      <a class="back-link" href="<?=URL?>/paginas/cadastro_aluno">
        <i class="bi bi-arrow-left"></i> <span>Voltar</span>
      </a>
      <h1>Escolha a empresa</h1>
      <form class="search" method="get" action="<?=URL?>/alunos/escolher_empresa">
        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar por nome, CNPJ ou razão social">
        <button type="submit"><i class="bi bi-search"></i></button>
      </form>
    </header>

    <?php if (empty($empresas)): ?>
      <div class="empty-box">
        <i class="bi bi-inboxes"></i>
        <p>Nenhuma empresa encontrada.</p>
      </div>
    <?php else: ?>
      <div class="company-grid">
        <?php foreach ($empresas as $e): ?>
          <form class="company-card" action="<?=URL?>/alunos/criar_solicitacao" method="post" onsubmit="return confirm('Confirmar solicitação para &quot;<?= htmlspecialchars($e->empr_nome) ?>&quot;?');">
            <input type="hidden" name="empr_id" value="<?= (int)$e->empr_id ?>">
            <div class="logo">
              <?php if (!empty($e->empr_logo)): ?>
                <img src="<?= URL . '/' . htmlspecialchars($e->empr_logo) ?>" alt="Logo">
              <?php else: ?>
                <i class="bi bi-buildings"></i>
              <?php endif; ?>
            </div> 
            <div class="body">
              <h3><?= htmlspecialchars($e->empr_nome) ?></h3>
              <p><strong>CNPJ:</strong> <?= htmlspecialchars($e->empr_cnpj) ?></p>
              <p><strong>Razão:</strong> <?= htmlspecialchars($e->empr_razao) ?></p>
              <p><strong>Administradores ativos:</strong> <?= htmlspecialchars($e->admins ?? '—') ?></p>
            </div>
            <div class="actions">
              <button class="btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Solicitar cadastro</button>
            </div>
          </form>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
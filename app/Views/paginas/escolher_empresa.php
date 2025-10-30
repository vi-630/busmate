<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/escolher_empresa.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="company-pick">
  <div class="pick-wrap">
    <header class="pick-head">
      <a class="btn-text" href="<?=URL?>/paginas/cadastro_aluno"><i class="bi bi-arrow-left"></i> Voltar</a>
      <h1>Selecione a empresa</h1>
      <div></div>
    </header>

    <?php if (!empty($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="<?=URL?>/alunos/criar_solicitacao" method="post" onsubmit="return confirm('Confirmar solicitação com a empresa selecionada?');">
      <div class="company-grid">
        <?php foreach ($empresas as $e): ?>
          <label class="company-card">
            <input type="radio" name="empr_id" value="<?=intval($e->empr_id)?>" required>
            <div class="logo">
              <?php if (!empty($e->empr_logo)): ?>
                <img src="<?=URL?>/<?=htmlspecialchars($e->empr_logo)?>" alt="Logo">
              <?php else: ?>
                <i class="bi bi-buildings"></i>
              <?php endif; ?>
            </div>
            <div class="body">
              <strong class="name"><?=htmlspecialchars($e->empr_nome)?></strong>
              <span class="meta"><i class="bi bi-file-earmark-text"></i> CNPJ: <?=htmlspecialchars($e->empr_cnpj)?></span>
              <span class="meta"><i class="bi bi-card-text"></i> Razão: <?=htmlspecialchars($e->empr_razao)?></span>
            </div>
          </label>
        <?php endforeach; ?>
      </div>

      <div class="pick-actions">
        <button class="btn-primary" type="submit">
          Solicitar cadastro <i class="bi bi-check2-circle"></i>
        </button>
      </div>
    </form>
  </div>
</section>

<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?=URL?>/public/css/onibus.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
  <?php require_once 'menu_lateral.php'; ?>

  <main class="content">
    <header class="page-head">
      <h1>Novo ônibus</h1>
    </header>

    <section class="onibus-form-card">
      <form class="onibus-form" action="<?=URL?>/onibus/salvar" method="post">
        <div class="form-row">
          <label>Modelo *</label>
          <input type="text" name="onib_modelo" required>
        </div>
        <div class="form-row">
          <label>Placa *</label>
          <input type="text" name="onib_placa" required>
        </div>
        <div class="form-row">
          <label>Situação</label>
          <select name="onib_situacao">
            <option value="ATIVO">Ativo</option>
            <option value="INATIVO">Inativo</option>
          </select>
        </div>
        <div class="form-actions">
          <a href="<?=URL?>/onibus" class="btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
          </a>
          <button class="btn-primary" type="submit">
            <i class="bi bi-check-lg"></i> Salvar
          </button>
        </div>
      </form>
    </section>
  </main>
</section>

<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/contrato.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
  <aside class="sidebar">
    <div class="profile">
      <div class="avatar"><i class="bi bi-person-fill"></i></div>
      <strong class="user-name">Fulano</strong>
    </div>

    <nav class="menu">
      <a href="<?=URL?>/paginas/index_app" class="item">
        <i class="bi bi-house-door-fill"></i><span>Início</span>
      </a>
      <a href="<?=URL?>/paginas/perfil" class="item">
        <i class="bi bi-person-badge-fill"></i><span>Perfil</span>
      </a>
      <a href="<?=URL?>/paginas/forum" class="item">
        <i class="bi bi-chat-dots-fill"></i><span>Fórum</span>
      </a>
    </nav>
  </aside>

  <main class="content">
    <header class="page-head">
      <h1>Contrato</h1>
    </header>

    <section class="contract-card">
      <div class="top-actions">
        <a class="btn btn-outline" href="<?=URL?>/public/docs/Contrato.pdf" target="_blank">
          <i class="bi bi-file-earmark-text"></i> Visualizar contrato
        </a>

        <a class="btn btn-outline" href="<?=URL?>/public/docs/contrato.pdf" download="Contrato.pdf">
          <i class="bi bi-download"></i> Baixar contrato
        </a>
      </div>

      <form class="upload-form" action="<?=URL?>/contratos/enviarTudo" method="post" enctype="multipart/form-data">
        <label class="file-field">
          <i class="bi bi-upload"></i>
          <span id="docsLabel">Anexar Documentos</span>
          <input type="file" name="documentos[]" id="docsInput" multiple accept=".pdf,.jpg,.jpeg,.png">
        </label>

        <label class="file-field">
          <i class="bi bi-upload"></i>
          <span id="fileLabel">Anexar Contrato Assinado</span>
          <input type="file" name="contrato_assinado" accept=".pdf,.jpg,.jpeg,.png" id="fileInput">
        </label>

        <button type="submit" class="btn btn-primary">Enviar</button>
      </form>
    </section>
  </main>
</section>

<script>
  (function(){
    const docsInput = document.getElementById('docsInput');
    const docsLabel = document.getElementById('docsLabel');
    const fileInput = document.getElementById('fileInput');
    const fileLabel = document.getElementById('fileLabel');

    if (docsInput) {
      docsInput.addEventListener('change', () => {
        if (docsInput.files.length === 1) {
          docsLabel.textContent = docsInput.files[0].name;
        } else if (docsInput.files.length > 1) {
          docsLabel.textContent = docsInput.files.length + ' arquivos selecionados';
        } else {
          docsLabel.textContent = 'Anexar Documentos';
        }
      });
    }

    if (fileInput) {
      fileInput.addEventListener('change', () => {
        const f = fileInput.files && fileInput.files[0];
        fileLabel.textContent = f ? f.name : 'Anexar Contrato Assinado';
      });
    }
  })();
</script>
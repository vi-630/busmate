<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/perfil.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
  <aside class="sidebar">
    <div class="profile">
      <div class="avatar"><i class="bi bi-person-fill"></i></div>
      <strong class="user-name"><?= htmlspecialchars($usuario['nome'] ?? 'Fulano') ?></strong>
    </div>

    <nav class="menu">
      <a href="<?=URL?>/paginas/index_app" class="item">
        <i class="bi bi-house-door-fill"></i><span>Início</span>
      </a>
      <a href="<?=URL?>/paginas/perfil" class="item active">
        <i class="bi bi-person-badge-fill"></i><span>Perfil</span>
      </a>
      <a href="<?=URL?>/paginas/forum" class="item">
        <i class="bi bi-chat-dots-fill"></i><span>Fórum</span>
      </a>
    </nav>
  </aside>

  <main class="content">
    <header class="page-head">
      <h1><?= htmlspecialchars($usuario['nome'] ?? 'Fulano de Tal') ?></h1>
    </header>

    <section class="perfil-grid">
      <form class="dados-form" id="perfilForm"
            action="<?=URL?>/usuarios/atualizar"
            method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="usuario_id" value="<?= (int)($usuario['id'] ?? 0) ?>">

        <label>Curso:
          <input
            type="text" name="curso" id="curso"
            value="<?= htmlspecialchars($usuario['curso'] ?? 'Técnico em Informática') ?>"
            data-original="<?= htmlspecialchars($usuario['curso'] ?? 'Técnico em Informática') ?>"
            readonly>
        </label>

        <label>Turma:
          <input
            type="text" name="turma" id="turma"
            value="<?= htmlspecialchars($usuario['turma'] ?? '3º Ano') ?>"
            data-original="<?= htmlspecialchars($usuario['turma'] ?? '3º Ano') ?>"
            readonly>
        </label>

        <label>Turno:
          <input
            type="text" name="turno" id="turno"
            value="<?= htmlspecialchars($usuario['turno'] ?? 'Matutino') ?>"
            data-original="<?= htmlspecialchars($usuario['turno'] ?? 'Matutino') ?>"
            readonly>
        </label>

        <div class="mensalidade-card">
          <div class="mc-head">
            <strong>MENSALIDADE</strong>
            <a href="<?=URL?>/paginas/contrato" class="btn btn-primary">Efetuar Pagamento</a>
          </div>
          <p><strong>Situação:</strong> <?= htmlspecialchars($usuario['situacao_mensalidade'] ?? '—') ?></p>
        </div>

        <div class="form-actions">
          <button type="button" id="btnCancelar" class="btn btn-outline" hidden>Cancelar</button>
          <button type="submit" id="btnSalvar" class="btn btn-primary" hidden>Salvar</button>
        </div>

        <a id="btnContrato" href="<?=URL?>/paginas/contrato" class="btn btn-secondary">CONTRATO</a>
      </form>

      <section class="foto-wrap">
  <div class="foto">
    <div class="avatar-lg">
      <i class="bi bi-person-circle"></i>
    </div>

    <button type="button" id="btnEditar" class="btn btn-outline">Editar Perfil</button>
  </div>
</section>
    </section>
  </main>
</section>

<script>
(function(){
  const form = document.getElementById('perfilForm');
  const inputs = form.querySelectorAll('input[type="text"]:not([type="hidden"])');
  const btnEditar = document.getElementById('btnEditar');
  const btnSalvar = document.getElementById('btnSalvar');
  const btnCancelar = document.getElementById('btnCancelar');
  const btnContrato = document.getElementById('btnContrato');

  const btnTrocarFoto = document.getElementById('btnTrocarFoto');
  const fotoInput     = document.getElementById('fotoInput');
  const fotoPreview   = document.getElementById('fotoPreview');
  const fotoIcon      = document.getElementById('fotoIcon');
  const fotoHint      = document.getElementById('fotoHint');

  const originalPhoto = fotoPreview && fotoPreview.src ? fotoPreview.src : '';

  function setEditing(isEditing){
    inputs.forEach(el => {
      el.readOnly = !isEditing;
      el.classList.toggle('is-editing', isEditing);
    });
    btnSalvar.hidden   = !isEditing;
    btnCancelar.hidden = !isEditing;
    btnEditar.hidden   =  isEditing;

    if (btnContrato) btnContrato.hidden = isEditing;

    if (btnTrocarFoto) btnTrocarFoto.hidden = !isEditing;
    if (fotoHint)      fotoHint.hidden      = !isEditing;
  }

  function restoreOriginals(){
    inputs.forEach(el => {
      if(el.hasAttribute('data-original')){
        el.value = el.getAttribute('data-original');
      }
    });
    if (originalPhoto) {
      if (fotoPreview){
        fotoPreview.src = originalPhoto;
        fotoPreview.style.display = 'block';
      }
      if (fotoIcon) fotoIcon.style.display = 'none';
    } else {
      if (fotoPreview){
        fotoPreview.src = '';
        fotoPreview.style.display = 'none';
      }
      if (fotoIcon) fotoIcon.style.display = 'block';
    }
    if (fotoInput) fotoInput.value = '';
  }

  setEditing(false);

  btnEditar.addEventListener('click', () => setEditing(true));

  btnCancelar.addEventListener('click', () => { restoreOriginals(); setEditing(false); });

  if (btnTrocarFoto && fotoInput) {
    btnTrocarFoto.addEventListener('click', () => fotoInput.click());

    fotoInput.addEventListener('change', () => {
      const f = fotoInput.files && fotoInput.files[0];
      if(!f) return;

      const okType = /image\/(png|jpeg|jpg|webp)/i.test(f.type);
      if(!okType){ alert('Envie uma imagem PNG, JPG ou WEBP.'); fotoInput.value=''; return; }
      if(f.size > 2 * 1024 * 1024){ alert('Tamanho máximo: 2 MB.'); fotoInput.value=''; return; }

      const reader = new FileReader();
      reader.onload = e => {
        if (fotoPreview){
          fotoPreview.src = e.target.result;
          fotoPreview.style.display = 'block';
        }
        if (fotoIcon) fotoIcon.style.display = 'none';
      };
      reader.readAsDataURL(f);
    });
  }

  form.addEventListener('submit', (e) => {
    const changedFields = Array.from(inputs).some(el => el.value !== el.getAttribute('data-original'));
    const changedPhoto  = fotoInput && fotoInput.files && fotoInput.files.length > 0;

    if(!changedFields && !changedPhoto){
      e.preventDefault();
      alert('Nenhuma alteração para salvar.');
      return;
    }
  });
})();
</script>

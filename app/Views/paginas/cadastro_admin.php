<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?=URL?>/public/css/cadastro_admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

  <!-- SIDEBAR ADMIN -->
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
    <nav class="menu">
      <!-- ======== ROOT ======== -->
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-buildings-fill"></i>
          <span>Empresas</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
    </nav>
  </aside>

  <main class="content">

<section class="register-section">
  <div class="register-hero-bg"></div>

  <div class="register-card">
    <h2 class="register-title">Cadastrar Administrador</h2>
    <p style="text-align:center; color:#555; margin-bottom:12px;">
      Preencha os dados do administrador responsável pela empresa.
    </p>

    <?php if (!empty($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="rf-photo">
      <div class="rf-avatar">
        <svg id="rfPreviewSvg" viewBox="0 0 48 48" class="avatar-svg">
          <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="3"></circle>
          <circle cx="24" cy="18" r="8" fill="currentColor"></circle>
          <path d="M8 40c4-7 10-10 16-10s12 3 16 10" fill="currentColor"></path>
        </svg>
        <img id="rfPreviewImg" style="display:none;" alt="Pré-visualização da foto">
      </div>

      <button type="button" id="rfPick" class="rf-file-btn">
        <i class="bi bi-camera-fill"></i> Anexar foto
      </button>
      <small class="rf-hint">PNG/JPG até 10&nbsp;MB • mínimo 256×256</small>
    </div>

    <form class="register-form" action="<?=URL?>/empresas/cadastrar_admin" method="post" autocomplete="on" enctype="multipart/form-data">
      <input type="hidden" name="empr_id" value="<?= htmlspecialchars($_GET['id'] ?? '') ?>">
      <input type="hidden" name="faltam"  value="<?= htmlspecialchars($_GET['faltam'] ?? 1) ?>">
      <input type="hidden" name="tius_id" value="2">
      <input id="rfFile" type="file" name="foto" accept="image/*" hidden>

      <label class="rf-label" for="nome">Nome completo:</label>
      <input class="rf-input" type="text" id="nome" name="nome" placeholder="Nome completo" required>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="cpf">CPF:</label>
          <input class="rf-input" type="text" id="cpf" name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" required>
        </div>
        <div class="rf-field">
          <label class="rf-label" for="telefone">Telefone principal:</label>
          <input class="rf-input" type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
        </div>
      </div>

      <label class="rf-label" for="telefone_alt">Telefone alternativo (opcional):</label>
      <input class="rf-input" type="tel" id="telefone_alt" name="telefone_alt" placeholder="(00) 00000-0000">

      <label class="rf-label" for="email_recuperacao">E-mail de recuperação (opcional):</label>
      <input class="rf-input" type="email" id="email_recuperacao" name="email_recuperacao" placeholder="email alternativo para recuperação">

      <label class="rf-label" for="email">E-mail:</label>
      <input class="rf-input" type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>

      <label class="rf-label" for="senha">Senha:</label>
      <input class="rf-input" type="password" id="senha" name="senha" placeholder="Crie uma senha" required>

      <div class="form-actions" style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
        <a class="btn-text" href="<?=URL?>/paginas/index_app"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button class="register-btn" type="submit">
          Cadastrar <i class="bi bi-arrow-right-circle-fill"></i>
        </button>
      </div>

      <?php if (!empty($_GET['faltam'])): ?>
        <p style="text-align:center; margin-top:10px; color:#777;">
          Faltam <strong><?= intval($_GET['faltam']) ?></strong> administrador(es) a cadastrar.
        </p>
      <?php endif; ?>
    </form>
  </div>
</section>

  </main>

</section>

<script>
/* Foto - preview robusto */
(() => {
  const input = document.getElementById('rfFile'),
        pick  = document.getElementById('rfPick'),
        svg   = document.getElementById('rfPreviewSvg'),
        img   = document.getElementById('rfPreviewImg');
  if (!input || !pick) return;
  pick.addEventListener('click', () => input.click());
  input.addEventListener('change', () => {
    const f = input.files?.[0];
    if (!f) {
      // Se remover seleção, volta SVG
      if(img) img.style.display='none';
      if(svg) svg.style.display='block';
      return;
    }
    if(!/image\/(png|jpeg|jpg|webp)/i.test(f.type)){
      alert('Envie uma imagem PNG, JPG ou WEBP.');
      input.value = '';
      if(img) img.style.display='none';
      if(svg) svg.style.display='block';
      return;
    }
    if(f.size > 10*1024*1024){
      alert('Máx 10MB.');
      input.value = '';
      if(img) img.style.display='none';
      if(svg) svg.style.display='block';
      return;
    }
    const r = new FileReader();
    r.onload = e => {
      if(svg) svg.style.display='none';
      if(img){ img.style.display='block'; img.src=e.target.result; }
    };
    r.readAsDataURL(f);
  });
})();

/* Máscaras */
(() => {
  const cpf = document.getElementById('cpf');
  const tel1 = document.getElementById('telefone');
  const tel2 = document.getElementById('telefone_alt');

  function maskCPF(v) {
    return v.replace(/\D/g, '')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
      .slice(0,14);
  }

  function maskPhone(v) {
    const digits = v.replace(/\D/g, '').slice(0, 11); // limita a 11 dígitos (DDD + 9)
    if (digits.length === 0) return '';
    const ddd = digits.slice(0,2);
    const rest = digits.slice(2);
    // Formatação progressiva baseada no comprimento do número após o DDD
    if (rest.length === 0) return '(' + ddd + ') ';
    if (rest.length <= 4) {
      return '(' + ddd + ') ' + rest;
    }
    // separa os 4 últimos dígitos com hífen
    const last4 = rest.slice(-4);
    const lead = rest.slice(0, rest.length - 4);
    return '(' + ddd + ') ' + lead + '-' + last4;
  }

  [cpf, tel1, tel2].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => {
      el.value = el.id === 'cpf' ? maskCPF(el.value) : maskPhone(el.value);
    });
  });
})();
</script>

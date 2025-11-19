<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?=URL?>/public/css/onibus.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

  <!-- SIDEBAR ADMIN -->
  <aside class="sidebar">
    <div class="profile">
      <div class="avatar">
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
        ?>
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
      <a href="<?=URL?>/viagens/minhas" class="item">
        <i class="bi bi-bus-front-fill"></i>
        <span>Horários</span>
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
      <h1><?= !empty($dados['id']) ? 'Editar ônibus' : 'Novo ônibus' ?></h1>
    </header>

    <section class="onibus-form-card">
      <form class="onibus-form" action="<?=URL?>/onibus/salvar" method="post" enctype="multipart/form-data">

        <?php if (!empty($dados['id'])): ?>
          <input type="hidden" name="onib_id" value="<?= (int)$dados['id'] ?>">
        <?php endif; ?>

        <!-- FOTO DO ÔNIBUS -->
        <div class="bus-photo">
          <div class="bus-avatar">
            <?php
              $fotoOnibusPath = $dados['onibus']->onib_foto ?? null;
              $fotoOnibusUrl  = $fotoOnibusPath
                ? URL . '/' . htmlspecialchars($fotoOnibusPath)
                : URL . '/public/img/onibus-escolar.png';
            ?>
            <img id="busFotoPreview" src="<?= $fotoOnibusUrl ?>" alt="Foto do ônibus">
          </div>

          <button type="button" id="busFotoPick" class="btn-file">
            <i class="bi bi-camera-fill"></i> Alterar foto do ônibus
          </button>

          <small class="bus-hint">
            Imagem PNG/JPG/WEBP até 5&nbsp;MB • proporção aproximada 1:1 para melhor resultado
          </small>

          <input type="file" id="busFotoInput" name="onib_foto" accept="image/*" hidden>
        </div>

        <div class="form-row">
          <label>Modelo *</label>
          <input type="text" name="onib_modelo"
                 value="<?= htmlspecialchars($dados['onibus']->onib_modelo ?? '') ?>"
                 required>
        </div>

        <div class="form-row">
          <label>Placa *</label>
          <input type="text" name="onib_placa"
                 value="<?= htmlspecialchars($dados['onibus']->onib_placa ?? '') ?>"
                 required>
        </div>

        <div class="form-row">
          <label>Situação</label>
          <?php $sit = $dados['onibus']->onib_situacao ?? 'ATIVO'; ?>
          <select name="onib_situacao">
            <option value="ATIVO"   <?=$sit=='ATIVO'?'selected':''?>>Ativo</option>
            <option value="INATIVO" <?=$sit=='INATIVO'?'selected':''?>>Inativo</option>
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

<script>
(function() {
  const input  = document.getElementById('busFotoInput');
  const pick   = document.getElementById('busFotoPick');
  const preview = document.getElementById('busFotoPreview');

  if (!input || !pick || !preview) return;

  const MAX = 5 * 1024 * 1024; // 5MB
  const okTypes = ['image/png','image/jpeg','image/jpg','image/webp'];

  pick.addEventListener('click', () => input.click());

  input.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if (!f) return;

    if (!okTypes.includes(f.type)) {
      alert('Por favor, envie uma imagem PNG, JPG ou WEBP.');
      input.value = '';
      return;
    }

    if (f.size > MAX) {
      alert('Arquivo muito grande. Tamanho máximo: 5 MB.');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
    };
    reader.readAsDataURL(f);
  });
})();
</script>

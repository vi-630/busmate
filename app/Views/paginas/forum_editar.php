<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
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
      <strong class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Fulano') ?></strong>
    </div>

    <nav class="menu">
      <a href="<?=URL?>/paginas/index_app" class="item">
        <i class="bi bi-house-door-fill"></i><span>Início</span>
      </a>
      <a href="<?=URL?>/paginas/forum" class="item active">
        <i class="bi bi-chat-dots-fill"></i><span>Fórum</span>
      </a>
      <a href="<?=URL?>/paginas/perfil" class="item">
        <i class="bi bi-person-badge-fill"></i><span>Perfil</span>
      </a>
    </nav>
  </aside>

  <main class="content forum-content">
    <header class="page-head">
      <h1><?= isset($isTopic) && $isTopic ? 'Editar Tópico' : 'Editar Resposta' ?></h1>
    </header>

    <div style="max-width:700px;margin:0 auto">
      <form method="post" style="background:#fff;padding:30px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1)">
        <?php if (isset($isTopic) && $isTopic): ?>
          <div style="margin-bottom:20px">
            <label style="display:block;font-weight:bold;margin-bottom:8px">Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($titulo ?? '') ?>" readonly style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;background:#f5f5f5;cursor:not-allowed" />
            <small style="color:#666">Título não pode ser alterado</small>
          </div>
        <?php endif; ?>

        <div style="margin-bottom:20px">
          <label style="display:block;font-weight:bold;margin-bottom:8px">Conteúdo</label>
          <textarea name="texto" required rows="8" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;font-family:monospace;font-size:14px"><?= htmlspecialchars($texto ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end">
          <a href="<?=URL?>/paginas/forum" class="btn btn-outline" style="padding:10px 20px;text-decoration:none;border:1px solid #ddd;border-radius:4px;cursor:pointer">Cancelar</a>
          <button type="submit" class="btn btn-primary" style="padding:10px 20px;background:#007bff;color:white;border:none;border-radius:4px;cursor:pointer">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </main>
</section>

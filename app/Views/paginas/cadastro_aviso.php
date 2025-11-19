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

// se estiver editando, pode vir um $aviso do controller
$isEdit = !empty($aviso) && isset($aviso->avis_id);
$titlePage = $isEdit ? 'Editar aviso' : 'Novo aviso';
$actionUrl = $isEdit
  ? URL . '/avisos/atualizar'
  : URL . '/avisos/cadastrar';
?>
<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/avisos.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

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
        <i class="bi bi-people-fill"></i><span>Alunos</span>
      </a>
      <a href="<?=URL?>/avisos" class="item active">
        <i class="bi bi-bell-fill"></i><span>Avisos</span>
      </a>
      <a href="<?=URL?>/viagens/minhas" class="item">
        <i class="bi bi-people-fill"></i><span>Viagens</span>
      </a>
      <a href="<?=URL?>/solicitacoes/index" class="item">
        <i class="bi bi-list-check"></i><span>Solicitações</span>
      </a>
      <a href="<?=URL?>/paginas/perfil" class="item">
        <i class="bi bi-person-badge-fill"></i><span>Perfil</span>
      </a>
    </nav>
  </aside>

  <main class="content">
    <div class="avisos-wrapper">

      <?php if (!empty($_GET['erro'])): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($_GET['erro']) ?>
        </div>
      <?php endif; ?>

      <header class="avisos-head">
        <h1 class="avisos-title"><?= htmlspecialchars($titlePage) ?></h1>
        <a href="<?=URL?>/avisos" class="btn-text-back">
          <i class="bi bi-arrow-left"></i> Voltar para avisos
        </a>
      </header>

      <section class="aviso-form-card">
        <form action="<?= $actionUrl ?>" method="post" class="aviso-form">
          <?php if ($isEdit): ?>
            <input type="hidden" name="avis_id" value="<?= (int)$aviso->avis_id ?>">
          <?php endif; ?>

          <div class="form-row">
            <label for="avis_titulo">Título do aviso</label>
            <input
              type="text"
              id="avis_titulo"
              name="avis_titulo"
              maxlength="120"
              placeholder="Ex.: Início das aulas, mudança de horário..."
              value="<?= $isEdit ? htmlspecialchars($aviso->avis_titulo) : '' ?>"
            >
          </div>

          <div class="form-row">
            <label for="avis_texto">Texto do aviso <span class="required">*</span></label>
            <textarea
              id="avis_texto"
              name="avis_texto"
              required
              placeholder="Digite aqui o conteúdo que será exibido para os alunos..."
            ><?= $isEdit ? htmlspecialchars($aviso->avis_texto) : '' ?></textarea>
          </div>

          <div class="form-row form-row-inline">
            <div>
              <label for="avis_situacao">Situação</label>
              <select id="avis_situacao" name="avis_situacao">
                <?php
                  $sit = $isEdit ? ($aviso->avis_situacao ?? 'ATIVO') : 'ATIVO';
                ?>
                <option value="ATIVO"   <?= $sit === 'ATIVO'   ? 'selected' : '' ?>>Ativo</option>
                <option value="INATIVO" <?= $sit === 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
              </select>
            </div>

            <div class="agendar-wrap">
              <label class="checkbox-inline">
                <input type="checkbox" id="toggle-agendar">
                Agendar publicação / expiração
              </label>
              <small>Se não marcar, o aviso é publicado imediatamente e sem data de expiração.</small>
            </div>
          </div>

          <div id="agendar-campos" class="form-row agendar-campos" style="display: none;">
            <div class="form-row-inline">
              <div>
                <label for="avis_publica_em">Publicar em</label>
                <input
                  type="datetime-local"
                  id="avis_publica_em"
                  name="avis_publica_em"
                  value="<?= $isEdit && !empty($aviso->avis_publica_em) ? date('Y-m-d\TH:i', strtotime($aviso->avis_publica_em)) : '' ?>"
                >
              </div>

              <div>
                <label for="avis_expira_em">Expirar em (opcional)</label>
                <input
                  type="datetime-local"
                  id="avis_expira_em"
                  name="avis_expira_em"
                  value="<?= $isEdit && !empty($aviso->avis_expira_em) ? date('Y-m-d\TH:i', strtotime($aviso->avis_expira_em)) : '' ?>"
                >
              </div>
            </div>
          </div>

          <div class="form-actions">
            <a href="<?=URL?>/avisos" class="btn-secondary">
              Cancelar
            </a>
            <button type="submit" class="btn-primary">
              <i class="bi bi-check-lg"></i>
              <?= $isEdit ? 'Salvar alterações' : 'Publicar aviso' ?>
            </button>
          </div>
        </form>
      </section>
    </div>

    <script>
      (function(){
        const toggle = document.getElementById('toggle-agendar');
        const box    = document.getElementById('agendar-campos');
        if(!toggle || !box) return;

        // se já tiver valor em algum campo (edição), mostra automaticamente
        const pub = document.getElementById('avis_publica_em');
        const exp = document.getElementById('avis_expira_em');
        if ((pub && pub.value) || (exp && exp.value)) {
          toggle.checked = true;
          box.style.display = 'block';
        }

        toggle.addEventListener('change', function(){
          box.style.display = this.checked ? 'block' : 'none';
          if (!this.checked) {
            if (pub) pub.value = '';
            if (exp) exp.value = '';
          }
        });
      })();
    </script>
  </main>
</section>

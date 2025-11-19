<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/forum.css">
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
      <strong class="user-name"><?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?></strong>
    </div>

    <?php if (!empty($_SESSION['user_tipo'])): ?>
    <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

    <nav class="menu">
   
      <!-- ======== ADMIN ======== -->
      <?php if ($tipoUsuario === 2): ?>
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
          <span>Viagens</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item active">
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

      <!-- ======== ALUNO ======== -->
      <?php elseif ($tipoUsuario === 3): ?>
        <a href="<?=URL?>/paginas/index_app" class="item">
          <i class="bi bi-house-door-fill"></i>
          <span>Início</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item active">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
        <a href="<?=URL?>/paginas/contrato" class="item">
          <i class="bi bi-file-earmark-text-fill"></i><span>Contrato</span>
        </a>

      <?php endif; ?>
    </nav>
  <?php endif; ?>

  </aside>

  <main class="content forum-content">
    <header class="page-head">
      <h1>Fórum</h1>
    </header>

    <div class="forum-actions">
      <label class="search">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Buscar no Fórum" aria-label="Buscar no Fórum">
      </label>
      <button type="button" class="btn btn-gold" data-action="toggle-new">
        <i class="bi bi-plus-lg"></i> Novo Tópico
      </button>
    </div>

    <form id="composerTopico" class="topic-card composer" action="<?=URL?>/forum/criar" method="post" hidden>
      <header class="topic-head">
        <input class="composer-title" type="text" name="titulo" placeholder="Título do tópico" required>
      </header>
      <textarea class="composer-body" name="conteudo" placeholder="Escreva o conteúdo do tópico..." rows="5" required></textarea>
      <footer class="composer-actions">
        <button type="button" class="btn btn-outline" data-close="#composerTopico">Cancelar</button>
        <button type="submit" class="btn btn-primary">Publicar</button>
      </footer>
    </form>

    <section class="topics" id="topicsList">
      <?php
        $topics = isset($topics) && is_array($topics) ? $topics : [];
        if (empty($topics)):
      ?>
        <div class="company-empty">
          <i class="bi bi-inboxes"></i>
          <p>Nenhum tópico encontrado.</p>
        </div>
      <?php else: ?>
        <?php foreach ($topics as $t): ?>
          <article class="topic-card" id="topic-<?= intval($t['id']) ?>" data-topic-id="<?= intval($t['id']) ?>">
            <header class="topic-head">
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:44px;height:44px;border-radius:6px;overflow:hidden">
                  <?php if (!empty($t['autor_foto'])): ?>
                    <img src="<?= URL ?>/<?= htmlspecialchars($t['autor_foto']) ?>" alt="<?= htmlspecialchars($t['autor_nome'] ?? '') ?>" style="width:44px;height:44px;object-fit:cover" />
                  <?php else: ?>
                    <div style="width:44px;height:44px;background:#eee;display:flex;align-items:center;justify-content:center"> <i class="bi bi-person-fill"></i></div>
                  <?php endif; ?>
                </div>
                <div>
                  <h3 style="margin:0"><a href="#topic-<?= intval($t['id']) ?>"><?= htmlspecialchars($t['titulo']) ?></a></h3>
                  <div style="font-size:0.9em;color:#666">
                    <?= htmlspecialchars($t['autor_nome'] ?? 'Usuário') ?> • <?= date('d/m/Y H:i', strtotime($t['dth_criacao'])) ?>
                  </div>
                </div>
              </div>
            </header>

            <p class="topic-excerpt"><?= htmlspecialchars(mb_strlen($t['texto']) > 300 ? mb_substr($t['texto'],0,300).'…' : $t['texto']) ?></p>

            <footer class="topic-foot">
              <button type="button" class="link-reply" data-reply-toggle>Responder</button>
              <button type="button" class="link-count" data-replies-toggle><?= intval($t['respostas_count']) ?> Resposta<?= (intval($t['respostas_count']) === 1 ? '' : 's') ?></button>
              <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
              <?php if (!empty($_SESSION['user_id']) && intval($_SESSION['user_id']) === intval($t['autor_id'])): ?>
                <a href="<?= URL ?>/forum/editar/<?= intval($t['id']) ?>" class="link-edit" style="font-size:0.9em;cursor:pointer" title="Editar">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="<?= URL ?>/forum/excluir/<?= intval($t['id']) ?>" class="link-delete" style="font-size:0.9em;cursor:pointer;color:#dc3545" title="Excluir" onclick="return confirm('Excluir este tópico?')">
                  <i class="bi bi-trash"></i>
                </a>
              <?php endif; ?>
            </footer>

            <form class="reply-composer" action="<?=URL?>/forum/responder/<?= intval($t['id']) ?>" method="post" hidden>
              <textarea name="resposta" placeholder="Escreva sua resposta..." rows="4" required></textarea>
              <div class="composer-actions">
                <button type="button" class="btn btn-outline" data-close="parent">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar</button>
              </div>
            </form>

            <div class="replies" hidden id="replies-<?= intval($t['id']) ?>"></div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <!-- Paginação -->
      <?php
        $currentPage = isset($currentPage) ? intval($currentPage) : 1;
        $totalPages = isset($totalPages) ? intval($totalPages) : 1;
      ?>
      <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;gap:10px;margin-top:30px">
          <?php if ($currentPage > 1): ?>
            <a href="<?= URL ?>/paginas/forum?page=<?= $currentPage - 1 ?>" class="btn btn-outline">← Anterior</a>
          <?php endif; ?>
          <span style="padding:10px">Página <?= $currentPage ?> de <?= $totalPages ?></span>
          <?php if ($currentPage < $totalPages): ?>
            <a href="<?= URL ?>/paginas/forum?page=<?= $currentPage + 1 ?>" class="btn btn-outline">Próxima →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </section>
  </main>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = (sel, root=document) => root.querySelector(sel);

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action="toggle-new"]');
    if (!btn) return;
    e.preventDefault();
    const form = $('#composerTopico');
    form.toggleAttribute('hidden');
    if (!form.hasAttribute('hidden')) {
      form.querySelector('.composer-title')?.focus();
    }
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-reply-toggle]');
    if (!btn) return;
    e.preventDefault();
    const card = btn.closest('.topic-card');
    const form = card.querySelector('.reply-composer');
    form.toggleAttribute('hidden');
    if (!form.hasAttribute('hidden')) {
      form.querySelector('textarea')?.focus();
    }
  });

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-replies-toggle]');
    if (!btn) return;
    e.preventDefault();
    const card = btn.closest('.topic-card');
    const box  = card.querySelector('.replies');
    const topicId = card.getAttribute('data-topic-id');

    // se já tem respostas carregadas, só toggle
    if (box.innerHTML.trim() !== '') {
      box.toggleAttribute('hidden');
      return;
    }

    // carregar respostas via AJAX - usar ?url= formato
    const apiUrl = '<?= URL ?>/public/index.php?url=forum/respostas/' + topicId;
    console.log('Carregando respostas de:', apiUrl);
    
    fetch(apiUrl)
      .then(r => {
        console.log('Response status:', r.status);
        return r.text();
      })
      .then(text => {
        console.log('Response text (first 200 chars):', text.substring(0, 200));
        try {
          const data = JSON.parse(text);
          console.log('Response data:', data);
          
          if (data.erro) {
            alert('Erro: ' + data.erro);
            return;
          }
          
          // renderizar respostas
          let html = '';
          if (data.replies && data.replies.length > 0) {
            data.replies.forEach(rep => {
              const photoHtml = rep.autor_foto 
                ? `<img src="<?= URL ?>/${rep.autor_foto}" alt="${rep.autor_nome}" style="width:36px;height:36px;object-fit:cover" />`
                : `<div style="width:36px;height:36px;background:#eee;display:flex;align-items:center;justify-content:center"><i class="bi bi-person-fill"></i></div>`;
              
              const createdDate = new Date(rep.dth_criacao);
              const formattedDate = createdDate.toLocaleDateString('pt-BR') + ' ' + createdDate.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
              
              html += `
                <article class="reply" data-reply-id="${rep.id}">
                  <header class="reply-head" style="display:flex;gap:10px;align-items:center">
                    <div style="width:36px;height:36px;border-radius:6px;overflow:hidden">
                      ${photoHtml}
                    </div>
                    <div>
                      <strong>${rep.autor_nome || 'Usuário'}</strong>
                      <div style="font-size:0.85em;color:#666">${formattedDate}</div>
                    </div>
                  </header>
                  <p>${rep.texto.replace(/\n/g, '<br>')}</p>
                </article>
              `;
            });
          } else {
            html = '<p style="color:#999;text-align:center;padding:20px">Nenhuma resposta ainda.</p>';
          }
          
          box.innerHTML = html;
          box.toggleAttribute('hidden');
        } catch (jsonErr) {
          console.error('Erro ao fazer parse JSON:', jsonErr, 'texto:', text.substring(0, 500));
          alert('Erro ao carregar respostas (JSON inválido). Verifique o console.');
        }
      })
      .catch(e => {
        console.error('Erro ao carregar respostas:', e);
        alert('Erro ao carregar respostas. Verifique o console do navegador.');
      });
  });

  document.addEventListener('click', (e) => {
    const closer = e.target.closest('[data-close]');
    if (!closer) return;
    e.preventDefault();
    const target = closer.getAttribute('data-close');
    if (target === 'parent') {
      closer.closest('form')?.setAttribute('hidden', '');
    } else {
      document.querySelector(target)?.setAttribute('hidden', '');
    }
  });

  const searchInput = document.querySelector('.forum-actions .search input[type="search"]');

  if (searchInput) {
    searchInput.addEventListener('keydown', (event) => {
      if (event.key === 'Enter') {
        event.preventDefault(); // Evita o comportamento padrão
        const searchValue = searchInput.value.trim();
        const currentUrl = new URL(window.location.href);

        if (searchValue) {
          currentUrl.searchParams.set('search', searchValue);
        } else {
          currentUrl.searchParams.delete('search'); // Remove o parâmetro de busca
        }

        window.location.href = currentUrl.toString(); // Redireciona com ou sem o parâmetro de busca
      }
    });
  }
});
</script>

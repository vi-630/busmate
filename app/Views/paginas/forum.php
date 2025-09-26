<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/forum.css">
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
      <a href="<?=URL?>/paginas/perfil" class="item">
        <i class="bi bi-person-badge-fill"></i><span>Perfil</span>
      </a>
      <a href="<?=URL?>/paginas/forum" class="item active">
        <i class="bi bi-chat-dots-fill"></i><span>Fórum</span>
      </a>
    </nav>
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

      <article class="topic-card" data-topic-id="1">
        <header class="topic-head">
          <h3><a href="#">Título do Tópico</a></h3>
          <time datetime="2025-09-24T10:00">Há 2 horas</time>
        </header>

        <p class="topic-excerpt">
          "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor..."
        </p>

        <footer class="topic-foot">
          <button type="button" class="link-reply" data-reply-toggle>Responder</button>
          <button type="button" class="link-count" data-replies-toggle>2 Respostas</button>
        </footer>

        <form class="reply-composer" action="<?=URL?>/forum/responder/1" method="post" hidden>
          <textarea name="resposta" placeholder="Escreva sua resposta..." rows="4" required></textarea>
          <div class="composer-actions">
            <button type="button" class="btn btn-outline" data-close="parent">Cancelar</button>
            <button type="submit" class="btn btn-primary">Enviar</button>
          </div>
        </form>

        <div class="replies" hidden>
          <article class="reply">
            <header class="reply-head">
              <strong>Maria</strong> <time>Há 50 min</time>
            </header>
            <p>Concordo! …</p>
          </article>
          <article class="reply">
            <header class="reply-head">
              <strong>João</strong> <time>Há 30 min</time>
            </header>
            <p>Outra dica é…</p>
          </article>
        </div>
      </article>

      <article class="topic-card" data-topic-id="2">
        <header class="topic-head">
          <h3><a href="#">Outro Tópico</a></h3>
          <time datetime="2025-09-24T12:00">Há 10 minutos</time>
        </header>

        <p class="topic-excerpt">
          "Outro texto de exemplo do tópico..."
        </p>

        <footer class="topic-foot">
          <button type="button" class="link-reply" data-reply-toggle>Responder</button>
          <button type="button" class="link-count" data-replies-toggle>0 Respostas</button>
        </footer>

        <form class="reply-composer" action="<?=URL?>/forum/responder/2" method="post" hidden>
          <textarea name="resposta" placeholder="Escreva sua resposta..." rows="4" required></textarea>
          <div class="composer-actions">
            <button type="button" class="btn btn-outline" data-close="parent">Cancelar</button>
            <button type="submit" class="btn btn-primary">Enviar</button>
          </div>
        </form>

        <div class="replies" hidden></div>
      </article>

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
    box.toggleAttribute('hidden');
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
});
</script>

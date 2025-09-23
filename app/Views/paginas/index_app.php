<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
</head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<section class="app-layout">
  <aside class="sidebar">
    <div class="profile">
      <div class="avatar"><i class="bi bi-person-fill"></i></div>
      <strong class="user-name">Fulano</strong>
    </div>

    <nav class="menu">
      <a href="<?=URL?>/paginas/index_app" class="item active">
        <i class="bi bi-house-door-fill"></i>
        <span>Início</span>
      </a>
      <a href="<?=URL?>/paginas/perfil" class="item">
        <i class="bi bi-person-badge-fill"></i>
        <span>Perfil</span>
      </a>
      <a href="<?=URL?>/paginas/forum" class="item">
        <i class="bi bi-chat-dots-fill"></i>
        <span>Fórum</span>
      </a>
    </nav>
  </aside>

  <main class="content">
    <header class="page-head">
      <h1>👋 Bem-vindo, Fulano!</h1>
    </header>

    <section class="avisos">
      <h2>Avisos:</h2>
      <div class="box">• •</div>
    </section>

    <section class="horarios">
      <h2>Horários</h2>

      <div class="cards-2col">
        <article class="bus-card">
            <a href="<?=URL?>/paginas/manha" class="card-link"><h3>Manhã</h3></a>
            <a href="<?=URL?>/paginas/manha" class="card-link"><img src="<?=URL?>/public/img/onibus-escolar.png" alt="Ônibus no turno da tarde"></a>
        </article>

        <article class="bus-card">
            <a href="<?=URL?>/paginas/tarde" class="card-link"><h3>Tarde</h3></a>
            <a href="<?=URL?>/paginas/tarde" class="card-link"><img src="<?=URL?>/public/img/onibus-escolar.png" alt="Ônibus no turno da tarde"></a>
        </article>
      </div>
    </section>
  </main>
</section>

<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/horarios.css">
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

    <main class="manha-content">
  <header class="page-head">
    <h1>TARDE</h1>
  </header>

  <figure class="bus-hero">
    <img src="<?=URL?>/public/img/onibus-escolar.png" alt="Ônibus - Linha da tarde" class="bus-photo">
  </figure>

  <section class="info-card">
    <p class="motorista"><strong>Motorista:</strong> <span>Fulano de Tal</span></p>

    <div class="horarios-ponto">
      <h2>Horários de ponto:</h2>
      <ul>
        <li><strong>Saída do ponto:</strong> 11:00 AM</li>
        <li><strong>Chegada Destino:</strong> 12:20</li>
        <li><strong>Saída do Destino:</strong> 18:20</li>
      </ul>
    </div>
  </section>
</main>
</section>
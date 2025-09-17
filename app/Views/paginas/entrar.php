<head>
    <link rel="stylesheet" href="<?= URL ?>/public/css/entrar.css">
</head>
<section class="login-section">
  <div class="login-wrap container">
    <div class="login-card">
      <h2>Acesse sua conta</h2>

      <form class="login-form" onsubmit="event.preventDefault(); window.location.href='<?=URL?>/paginas/';">
        <label class="sr-only" for="email">E-mail</label>
        <input id="email" type="email" name="email" placeholder="E-mail" required>

        <label class="sr-only" for="senha">Senha</label>
        <input id="senha" type="password" name="senha" placeholder="Senha" required>

        <div class="aux-row">
          <a href="#" class="link">Esqueci minha senha</a>
        </div>

        <button type="submit" action="" class="btn-primary">Entrar</button>

        <p class="muted">
          Ainda não tem conta? <a href="<?=URL?>/paginas/cadastrar" class="link highlight">Cadastre-se</a>
        </p>
      </form>

      <div class="social">
        <span>Entre com</span>
        <a class="btn-google" href="#">
          <svg aria-hidden="true" viewBox="0 0 48 48" width="22" height="22">
            <path fill="#FFC107" d="M43.6 20.5h-1.9V20H24v8h11.3c-1.6 4.6-6 8-11.3 8-6.9 0-12.5-5.6-12.5-12.5S16.1 11 23 11c3.2 0 6.1 1.2 8.3 3.2l5.7-5.7C33.8 5.7 28.7 3.7 23 3.7 11.8 3.7 2.7 12.8 2.7 24S11.8 44.3 23 44.3 43.3 35.2 43.3 24c0-1.2-.1-2.4-.3-3.5z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 16 18.5 13.7 23 13.7c3.2 0 6.1 1.2 8.3 3.2l5.7-5.7C33.8 5.7 28.7 3.7 23 3.7c-7.8 0-14.4 4.4-16.7 11z"/>
            <path fill="#4CAF50" d="M23 44.3c5.2 0 9.9-2 13.3-5.2l-6.1-5c-2 1.4-4.6 2.2-7.3 2.2-5.3 0-9.8-3.4-11.4-8.1l-6.6 5.1C7.2 39.6 14.5 44.3 23 44.3z"/>
            <path fill="#1976D2" d="M43.6 20.5H24v8h11.3c-0.8 2.4-2.3 4.5-4.3 6l6.1 5C40.8 36.6 43.3 30.8 43.3 24c0-1.2-.1-2.4-.3-3.5z"/>
          </svg>
          Google
        </a>
      </div>
    </div>

    <div class="login-illustration">
      <img src="<?=URL?>/public/img/onibus.png" alt="Ônibus - BusMate">
    </div>
  </div>
</section>
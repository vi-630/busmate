<head>
    <link rel="stylesheet" href="<?= URL ?>/public/css/entrar.css">
</head>
<section class="login-section">
  <div class="login-wrap container">
    <div class="login-card">
      <h2>Acesse sua conta</h2>

  <form class="login-form" action="<?=URL?>/usuarios/login" method="post">
        <label class="sr-only" for="email">E-mail</label>
        <input id="email" type="email" name="email" placeholder="E-mail" required>

        <label class="sr-only" for="senha">Senha</label>
        <input id="senha" type="password" name="senha" placeholder="Senha" required>

        <button type="submit" class="btn-primary">Entrar</button>
        <?php if (isset($_GET['error'])): ?>
          <p class="error-msg" style="color:#c00;margin-top:8px;">
            <?php
              $err = $_GET['error'];
              if ($err === 'missing') echo 'Preencha e-mail e senha.';
              elseif ($err === 'notfound') echo 'Usuário não encontrado.';
              elseif ($err === 'badpass') echo 'Senha incorreta.';
              else echo 'Erro ao autenticar.';
            ?>
          </p>
        <?php endif; ?>

        <p class="muted">
          Ainda não tem conta? <a href="<?=URL?>/paginas/cadastro_aluno" class="link highlight">Cadastre-se</a>
        </p>
      </form>
    </div>

    <div class="login-illustration">
      <img src="<?=URL?>/public/img/onibus.png" alt="Ônibus - BusMate">
    </div>
  </div>
</section>
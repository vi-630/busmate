<head>
    <link rel="stylesheet" href="<?=URL?>/public/css/cadastrar.css">
</head>
<section class="register-section">
  <div class="register-hero-bg"></div>

  <div class="register-card">
    <h2 class="register-title">Cadastro</h2>

    <div class="register-avatar" aria-hidden="true">
      <svg viewBox="0 0 48 48" class="avatar-svg">
        <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="3"/>
        <circle cx="24" cy="18" r="8" fill="currentColor"/>
        <path d="M8 40c4-7 10-10 16-10s12 3 16 10" fill="currentColor"/>
      </svg>
    </div>

    <form class="register-form" action="#" method="post" autocomplete="on">
      <label class="rf-label" for="nome">Nome completo:</label>
      <input class="rf-input" type="text" id="nome" name="nome" placeholder="Seu nome completo" required>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="curso">Curso:</label>
          <input class="rf-input" type="text" id="curso" name="curso" placeholder="Ex.: Informática">
        </div>
        <div class="rf-field">
          <label class="rf-label" for="telefone">Telefone:</label>
          <input class="rf-input" type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000">
        </div>
      </div>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="turma">Turma:</label>
          <input class="rf-input" type="text" id="turma" name="turma" placeholder="Ex.: 3ºA">
        </div>
        <div class="rf-field">
          <label class="rf-label" for="turno">Turno:</label>
          <select class="rf-input rf-select" id="turno" name="turno">
            <option value="" selected disabled>Selecione</option>
            <option>Manhã</option>
            <option>Tarde</option>
            <option>Noite</option>
          </select>
        </div>
      </div>

      <label class="rf-label" for="email">E-mail:</label>
      <input class="rf-input" type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>

      <label class="rf-label" for="senha">Senha</label>
      <input class="rf-input" type="password" id="senha" name="senha" placeholder="Crie uma senha" required>

      <button class="register-btn" type="submit">Cadastrar</button>

      <p class="register-login">
        Já tem uma conta?
        <a href="<?=URL?>/paginas/entrar">Entrar</a>
      </p>
    </form>
  </div>
</section>

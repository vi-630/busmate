<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/cadastrar.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="register-section">
  <div class="register-hero-bg"></div>

  <div class="register-card">
    <h2 class="register-title">Cadastro</h2>

    <div class="rf-photo">
      <div class="rf-avatar">
        <svg id="rfPreviewSvg" viewBox="0 0 48 48" class="avatar-svg">
        <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="3"/>
        <circle cx="24" cy="18" r="8" fill="currentColor"/>
        <path d="M8 40c4-7 10-10 16-10s12 3 16 10" fill="currentColor"/>
        </svg>
        <img id="rfPreviewImg" style="display:none;" alt="Pré-visualização da foto">
      </div>


      <input id="rfFile" type="file" name="foto" accept="image/*" hidden>
      <button type="button" id="rfPick" class="rf-file-btn">
        <i class="bi bi-camera-fill"></i> Anexar foto
      </button>
      <small class="rf-hint">PNG/JPG até 2&nbsp;MB • mínimo 256×256</small>
    </div>

    <!-- IMPORTANTE: enctype para upload -->
    <form class="register-form" action="<?=URL?>/usuarios/cadastrar" method="post" autocomplete="on" enctype="multipart/form-data">
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

<script>
(function(){
  const input = document.getElementById('rfFile');
  const pick  = document.getElementById('rfPick');
  const svg   = document.getElementById('rfPreviewSvg');
  const img   = document.getElementById('rfPreviewImg');

  if(!input || !pick) return;

  pick.addEventListener('click', () => input.click());

  input.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if(!f) return;

    const okType = /image\/(png|jpeg|jpg|webp)/i.test(f.type);
    if(!okType){ alert('Envie uma imagem PNG, JPG ou WEBP.'); input.value=''; return; }
    if(f.size > 2 * 1024 * 1024){ alert('Tamanho máximo: 2 MB.'); input.value=''; return; }

    const reader = new FileReader();
    reader.onload = e => {
      svg.style.display = 'none';
      img.style.display = 'block';
      img.src = e.target.result;
    };
    reader.readAsDataURL(f);
  });
})();

(function () {
  const email = document.getElementById('email');
  const senha = document.getElementById('senha');
  const tel   = document.getElementById('telefone');

  // --- Validação de e-mail (mensagem amigável) ---
  if (email) {
    email.addEventListener('invalid', () => {
      if (email.validity.valueMissing) {
        email.setCustomValidity('Informe seu e-mail.');
      } else if (email.validity.typeMismatch) {
        email.setCustomValidity('Digite um e-mail válido (ex.: nome@dominio.com).');
      } else {
        email.setCustomValidity('');
      }
    });
    email.addEventListener('input', () => email.setCustomValidity(''));
  }

  // --- Validação de senha (4 a 8 caracteres) ---
  if (senha) {
    const senhaMsg = 'A senha deve ter entre 4 e 8 caracteres.';
    senha.addEventListener('input', () => {
      const len = senha.value.length;
      if (len < 4 || len > 8) {
        senha.setCustomValidity(senhaMsg);
      } else {
        senha.setCustomValidity('');
      }
    });
    senha.addEventListener('invalid', () => {
      senha.setCustomValidity(senhaMsg);
    });
  }

  // --- Máscara e validação de telefone ---
  if (tel) {
    const pat = /^\(\d{2}\)\s(?:9\d{4}-\d{4}|\d{4}-\d{4})$/;

    function formatPhone(val) {
      // mantém só dígitos e limita a 11
      const nums = val.replace(/\D/g, '').slice(0, 11);
      const ddd = nums.slice(0, 2);
      const rest = nums.slice(2);

      if (nums.length <= 6) {
        // (DD) XXXX
        const p1 = rest.slice(0, 4);
        const p2 = rest.slice(4);
        return ddd ? `(${ddd}) ${p1}${p2 ? '-' + p2 : ''}` : '';
      } else if (nums.length <= 10) {
        // (DD) XXXX-XXXX
        return `(${ddd}) ${rest.slice(0,4)}-${rest.slice(4,8)}`;
      } else {
        // 11 dígitos: (DD) 9XXXX-XXXX
        return `(${ddd}) ${rest.slice(0,5)}-${rest.slice(5,9)}`;
      }
    }

    function validateTel() {
      if (!tel.value) {
        tel.setCustomValidity('Informe seu telefone.');
        return;
      }
      if (!pat.test(tel.value)) {
        tel.setCustomValidity('Use (DD) 99999-9999 ou (DD) 9999-9999.');
      } else {
        tel.setCustomValidity('');
      }
    }

    tel.addEventListener('input', (e) => {
      const cursorPos = tel.selectionStart;
      const oldLen = tel.value.length;

      tel.value = formatPhone(tel.value);

      // tenta manter o cursor em posição natural
      const newLen = tel.value.length;
      const diff = newLen - oldLen;
      tel.setSelectionRange(cursorPos + diff, cursorPos + diff);

      validateTel();
    });

    tel.addEventListener('blur', validateTel);
    tel.addEventListener('invalid', () => {
      validateTel();
      tel.reportValidity();
    });
  }
})();
</script>

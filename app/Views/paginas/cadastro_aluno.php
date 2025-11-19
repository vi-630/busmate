<?php
?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/cadastro_aluno.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="register-section">
  <div class="register-hero-bg"></div>

  <div class="register-card">
    <a class="btn-text" href="<?=URL?>/paginas/home"><i class="bi bi-arrow-left"></i> Voltar</a>
    <h2 class="register-title">Solicitação de cadastro</h2>

    <?php if (!empty($_GET['erro'])): ?>
      <div class="alert alert-danger">
        <?= htmlspecialchars($_GET['erro']) ?>
      </div>
    <?php endif; ?>

    <!-- ADAPTADO: action agora aponta para /alunos/solicitar (mantém multipart) -->
    <form class="register-form" action="<?=URL?>/Alunos/solicitar" method="post" autocomplete="on" enctype="multipart/form-data">
      <div class="rf-photo">
      <div class="rf-avatar">
        <svg id="rfPreviewSvg" viewBox="0 0 48 48" class="avatar-svg">
          <circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="3"/>
          <circle cx="24" cy="18" r="8" fill="currentColor"/>
          <path d="M8 40c4-7 10-10 16-10s12 3 16 10" fill="currentColor"/>
        </svg>
        <img id="rfPreviewImg" style="display:none;" alt="Pré-visualização da foto">
      </div>

      <button type="button" id="rfPick" class="rf-file-btn">
        <i class="bi bi-camera-fill"></i> Adicionar foto
      </button>
      <small class="rf-hint">Imagem PNG/JPG até 10&nbsp;MB • tamanho mínimo 256×256 pixels</small>
    </div>
    
    
    <input id="rfFile" type="file" name="foto" accept="image/*" hidden>

      <!-- Mantido: vamos carregar esse valor junto no fluxo; o backend decide se usa já ou só depois -->
      <input type="hidden" name="tius_id" value="3">

      <label class="rf-label" for="nome">Nome completo:</label>
      <input class="rf-input" type="text" id="nome" name="nome" placeholder="Digite seu nome completo" required>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="escola">Escola:</label>
          <input class="rf-input" type="text" id="escola" name="escola" placeholder="Digite o nome da sua escola" required>
        </div>
        <div class="rf-field">
          <label class="rf-label" for="curso">Curso:</label>
          <input class="rf-input" type="text" id="curso" name="curso" placeholder="Ex.: Engenharia" required>
        </div>
      </div>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="telefone">Telefone:</label>
          <input class="rf-input" type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
        </div>
        <div class="rf-field">
          <label class="rf-label" for="telefone_resp">Telefone do responsável:</label>
          <input class="rf-input" type="tel" id="telefone_resp" name="telefone_resp" placeholder="(00) 00000-0000">
        </div>
      </div>

      <div class="rf-grid">
        <div class="rf-field">
          <label class="rf-label" for="turma">Turma:</label>
          <input class="rf-input" type="text" id="turma" name="turma" placeholder="Ex.: 3ºA" required>
        </div>
        <div class="rf-field">
          <label class="rf-label" for="turno">Turno:</label>
          <select class="rf-input rf-select" id="turno" name="turno" required>
            <option value="" selected disabled>Selecione</option>
            <option>Manhã</option>
            <option>Tarde</option>
            <option>Noite</option>
          </select>
        </div>
      </div>

      <label class="rf-label" for="endereco">Endereço:</label>
      <input class="rf-input" type="text" id="endereco" name="endereco" placeholder="Rua, número, bairro, cidade" required>

      <!-- RG/CPF em arquivos; inputs de texto removidos conforme solicitado -->

            <!-- Comprovante de matrícula -->
      <div class="rf-field">
        <label class="rf-label" for="comprovante_matricula">Comprovante de matrícula (obrigatório):</label>
        <div class="file-upload-box">
          <input id="comprovante_matricula" type="file" name="comprovante_matricula"
                 accept="application/pdf,image/*" required hidden>
          <button type="button" id="pickComprovanteMatricula" class="btn-file-upload">
            <i class="bi bi-paperclip"></i> Enviar comprovante de matrícula
          </button>
          <small class="file-hint">PDF, JPG ou PNG • máximo 5 MB</small>
          <div class="file-name" id="comprovanteMatriculaNome" style="display:none;"></div>
        </div>
        <div id="comprovanteMatriculaError" class="rf-error" style="display:none;color:#dc3545;font-size:0.875em;margin-top:4px;">
          Por favor, envie o comprovante de matrícula
        </div>
      </div>

      <!-- Comprovante de residência -->
      <div class="rf-field">
        <label class="rf-label" for="comprovante_residencia">Comprovante de residência (obrigatório):</label>
        <div class="file-upload-box">
          <input id="comprovante_residencia" type="file" name="comprovante_residencia"
                 accept="application/pdf,image/*" required hidden>
          <button type="button" id="pickComprovanteResidencia" class="btn-file-upload">
            <i class="bi bi-paperclip"></i> Enviar comprovante de residência
          </button>
          <small class="file-hint">PDF, JPG ou PNG • máximo 5 MB</small>
          <div class="file-name" id="comprovanteResidenciaNome" style="display:none;"></div>
        </div>
        <div id="comprovanteResidenciaError" class="rf-error" style="display:none;color:#dc3545;font-size:0.875em;margin-top:4px;">
          Por favor, envie o comprovante de residência
        </div>
      </div>

      <!-- RG (arquivo) - para menores -->
      <div class="rf-field">
        <label class="rf-label" for="rg_arquivo">Arquivo do RG (obrigatório):</label>
        <div class="file-upload-box">
          <input id="rg_arquivo" type="file" name="rg_arquivo"
                 accept="application/pdf,image/*" required hidden>
          <button type="button" id="pickRgArquivo" class="btn-file-upload">
            <i class="bi bi-paperclip"></i> Enviar RG
          </button>
          <small class="file-hint">PDF, JPG ou PNG • máximo 5 MB</small>
          <div class="file-name" id="rgArquivoNome" style="display:none;"></div>
        </div>
        <div id="rgArquivoError" class="rf-error" style="display:none;color:#dc3545;font-size:0.875em;margin-top:4px;">
          Por favor, envie o arquivo do RG
        </div>
      </div>

      <!-- CPF (arquivo) -->
      <div class="rf-field">
        <label class="rf-label" for="cpf_arquivo">Arquivo do CPF (obrigatório):</label>
        <div class="file-upload-box">
          <input id="cpf_arquivo" type="file" name="cpf_arquivo"
                 accept="application/pdf,image/*" required hidden>
          <button type="button" id="pickCpfArquivo" class="btn-file-upload">
            <i class="bi bi-paperclip"></i> Enviar CPF
          </button>
          <small class="file-hint">PDF, JPG ou PNG • máximo 5 MB</small>
          <div class="file-name" id="cpfArquivoNome" style="display:none;"></div>
        </div>
        <div id="cpfArquivoError" class="rf-error" style="display:none;color:#dc3545;font-size:0.875em;margin-top:4px;">
          Por favor, envie o arquivo do CPF
        </div>
      </div>
      <!-- Documento do responsável (para menores) -->
      <div class="rf-field">
        <label class="rf-label" for="doc_responsavel">Documento de identificação do responsável (caso de menor):</label>
        <div class="file-upload-box">
          <input id="doc_responsavel" type="file" name="doc_responsavel"
                 accept="application/pdf,image/*" hidden>
          <button type="button" id="pickDocResponsavel" class="btn-file-upload">
            <i class="bi bi-paperclip"></i> Enviar documento
          </button>
          <small class="file-hint">PDF, JPG ou PNG • máximo 5 MB (opcional)</small>
          <div class="file-name" id="docResponsavelNome" style="display:none;"></div>
        </div>
      </div>


      <label class="rf-label" for="email_recuperacao">E-mail de recuperação (opcional):</label>
      <input class="rf-input" type="email" id="email_recuperacao" name="email_recuperacao" placeholder="E-mail alternativo para recuperação de senha">

      <label class="rf-label" for="email">E-mail principal:</label>
      <input class="rf-input" type="email" id="email" name="email" placeholder="seu.email@exemplo.com" required>

      <label class="rf-label" for="senha">Senha:</label>
      <input class="rf-input" type="password" id="senha" name="senha" placeholder="Digite sua senha" required>

      <!-- Modificado: removido formaction do botão -->
      <button class="register-btn" type="submit" href="<?=URL?>/Alunos/escolher_empresa">Continuar</button>


      <p class="register-login">Já tem uma conta? <a href="<?=URL?>/paginas/entrar">Entrar</a></p>
    </form>
  </div>
</section>

<script>
(function(){
  // Pré-visualização da foto
  const input = document.getElementById('rfFile');
  const pick  = document.getElementById('rfPick');
  const svg   = document.getElementById('rfPreviewSvg');
  const img   = document.getElementById('rfPreviewImg');

  if(input && pick){
    pick.addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
      const f = input.files && input.files[0];
      if(!f) return;
      const okType = /image\/(png|jpeg|jpg|webp)/i.test(f.type);
      if(!okType){ alert('Por favor, envie uma imagem PNG, JPG ou WEBP.'); input.value=''; return; }
      if(f.size > 10 * 1024 * 1024){ alert('Tamanho máximo: 10 MB.'); input.value=''; return; }
      const reader = new FileReader();
      reader.onload = e => { if(svg) svg.style.display='none'; if(img){ img.style.display='block'; img.src = e.target.result; } };
      reader.readAsDataURL(f);
    });
  }

  // Comprovante de matrícula
  const comprovanteMatriculaInput = document.getElementById('comprovante_matricula');
  const pickComprovanteMatricula = document.getElementById('pickComprovanteMatricula');
  const comprovanteMatriculaNome = document.getElementById('comprovanteMatriculaNome');
  const comprovanteMatriculaError = document.getElementById('comprovanteMatriculaError');
  setupFileInput(comprovanteMatriculaInput, pickComprovanteMatricula, comprovanteMatriculaNome, comprovanteMatriculaError);

  // Comprovante de residência
  const comprovanteResidenciaInput = document.getElementById('comprovante_residencia');
  const pickComprovanteResidencia = document.getElementById('pickComprovanteResidencia');
  const comprovanteResidenciaNome = document.getElementById('comprovanteResidenciaNome');
  const comprovanteResidenciaError = document.getElementById('comprovanteResidenciaError');
  setupFileInput(comprovanteResidenciaInput, pickComprovanteResidencia, comprovanteResidenciaNome, comprovanteResidenciaError);

  // RG (arquivo)
  const rgArquivoInput = document.getElementById('rg_arquivo');
  const pickRgArquivo = document.getElementById('pickRgArquivo');
  const rgArquivoNome = document.getElementById('rgArquivoNome');
  const rgArquivoError = document.getElementById('rgArquivoError');
  setupFileInput(rgArquivoInput, pickRgArquivo, rgArquivoNome, rgArquivoError);

  // CPF (arquivo)
  const cpfArquivoInput = document.getElementById('cpf_arquivo');
  const pickCpfArquivo = document.getElementById('pickCpfArquivo');
  const cpfArquivoNome = document.getElementById('cpfArquivoNome');
  const cpfArquivoError = document.getElementById('cpfArquivoError');
  setupFileInput(cpfArquivoInput, pickCpfArquivo, cpfArquivoNome, cpfArquivoError);

  // Documento do responsável (opcional)
  const docResponsavelInput = document.getElementById('doc_responsavel');
  const pickDocResponsavel = document.getElementById('pickDocResponsavel');
  const docResponsavelNome = document.getElementById('docResponsavelNome');
  if (docResponsavelInput && pickDocResponsavel && docResponsavelNome) {
    const MAX = 5 * 1024 * 1024;
    const okTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
    pickDocResponsavel.addEventListener('click', () => docResponsavelInput.click());
    docResponsavelInput.addEventListener('change', () => {
      const f = docResponsavelInput.files && docResponsavelInput.files[0];
      if (!f) {
        docResponsavelNome.style.display = 'none';
        docResponsavelNome.textContent = '';
        return;
      }
      if (!okTypes.includes(f.type)) {
        alert('Por favor, envie PDF ou imagem (PNG/JPG/WEBP).');
        docResponsavelInput.value = '';
        docResponsavelNome.style.display = 'none';
        docResponsavelNome.textContent = '';
        return;
      }
      if (f.size > MAX) {
        alert('Arquivo muito grande. Máx 5 MB.');
        docResponsavelInput.value = '';
        docResponsavelNome.style.display = 'none';
        docResponsavelNome.textContent = '';
        return;
      }
      docResponsavelNome.textContent = f.name;
      docResponsavelNome.style.display = 'block';
    });
  }


  function setupFileInput(input, button, nameBox, errorSpan) {
    if (!input || !button || !nameBox) return;
    const MAX = 5 * 1024 * 1024;
    const okTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

    button.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
      const f = input.files && input.files[0];
      if (!f) {
        nameBox.style.display = 'none';
        nameBox.textContent = '';
        if (errorSpan) errorSpan.style.display = 'block';
        return;
      }

      if (!okTypes.includes(f.type)) {
        alert('Por favor, envie PDF ou imagem (PNG/JPG/WEBP).');
        input.value = '';
        nameBox.style.display = 'none';
        nameBox.textContent = '';
        if (errorSpan) errorSpan.style.display = 'block';
        return;
      }

      if (f.size > MAX) {
        alert('Arquivo muito grande. Máx 5 MB.');
        input.value = '';
        nameBox.style.display = 'none';
        nameBox.textContent = '';
        if (errorSpan) errorSpan.style.display = 'block';
        return;
      }

      nameBox.textContent = f.name;
      nameBox.style.display = 'block';
      if (errorSpan) errorSpan.style.display = 'none';
    });
  }


  // Comprovante (removido, mantido apenas para compatibilidade se necessário)
  const comprovanteInput = document.getElementById('comprovante');
  const pickComprovante = document.getElementById('pickComprovante');
  const comprovanteNome = document.getElementById('comprovanteNome');
  const comprovanteError = document.getElementById('comprovanteError');
  if(comprovanteInput && pickComprovante && comprovanteNome){
    const MAX = 5 * 1024 * 1024;
    const okTypes = ['application/pdf','image/png','image/jpeg','image/jpg','image/webp'];
    pickComprovante.addEventListener('click', () => comprovanteInput.click());
    comprovanteInput.addEventListener('change', () => {
      const f = comprovanteInput.files && comprovanteInput.files[0];
      if(!f){
        comprovanteNome.textContent = 'Arquivo PDF/JPG/PNG até 5 MB';
        if (comprovanteError) comprovanteError.style.display = 'block';
        return;
      }
      if(!okTypes.includes(f.type)){
        alert('Por favor, envie PDF ou imagem (PNG/JPG/WEBP).');
        comprovanteInput.value='';
        comprovanteNome.textContent='Arquivo PDF/JPG/PNG até 5 MB';
        if (comprovanteError) comprovanteError.style.display = 'block';
        return;
      }
      if(f.size > MAX){
        alert('Arquivo muito grande. Máx 5 MB.');
        comprovanteInput.value='';
        comprovanteNome.textContent='Arquivo PDF/JPG/PNG até 5 MB';
        if (comprovanteError) comprovanteError.style.display = 'block';
        return;
      }
      comprovanteNome.textContent = f.name;
      if (comprovanteError) comprovanteError.style.display = 'none';
    });
  }

  // Validações, máscaras, UX
  (function(){
    const form = document.querySelector('.register-form');
    const escola = document.getElementById('escola');
    if (form && escola) {
      form.addEventListener('submit', function(e){
        if (!escola.value.trim()) {
          e.preventDefault();
          alert('Por favor, digite o nome da sua escola.');
          escola.focus();
        }
      });
    }
    const tel = document.getElementById('telefone');
    const telResp = document.getElementById('telefone_resp');
    const email = document.getElementById('email');
    const emailRec = document.getElementById('email_recuperacao');
    const senha = document.getElementById('senha');

    if(email){
      email.addEventListener('invalid', () => {
        if (email.validity.valueMissing) email.setCustomValidity('Por favor, informe seu e-mail.');
        else if (email.validity.typeMismatch) email.setCustomValidity('Digite um e-mail válido (ex.: nome@dominio.com).');
        else email.setCustomValidity('');
      });
      email.addEventListener('input', () => email.setCustomValidity(''));
    }
    if(emailRec){
      emailRec.addEventListener('invalid', () => {
        if (emailRec.validity.valueMissing) emailRec.setCustomValidity('Por favor, informe um e-mail de recuperação.');
        else if (emailRec.validity.typeMismatch) emailRec.setCustomValidity('Digite um e-mail válido (ex.: nome@dominio.com).');
        else emailRec.setCustomValidity('');
      });
      emailRec.addEventListener('input', () => emailRec.setCustomValidity(''));
    }
    if(senha){
      const msg = 'A senha deve ter entre 4 e 8 caracteres.';
      senha.addEventListener('input', () => {
        const len = senha.value.length;
        senha.setCustomValidity((len<4||len>8)?msg:'');
      });
      senha.addEventListener('invalid', () => senha.setCustomValidity('A senha deve ter entre 4 e 8 caracteres.'));
    }

    // RG/CPF em texto removidos — apenas uploads de arquivos são usados para identificação

    function applyPhoneMask(el){
      if(!el) return;
      const pat = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
      function formatPhone(val){
        const nums = val.replace(/\D/g,'').slice(0,11);
        const ddd = nums.slice(0,2);
        const rest = nums.slice(2);
        if(!nums) return '';
        if(rest.length<=8) return `(${ddd}) ${rest.slice(0,4)}-${rest.slice(4,8)}`;
        return `(${ddd}) ${rest.slice(0,5)}-${rest.slice(5,9)}`;
      }
      function validateTel(el){
        if(!el.value && el.hasAttribute('required')){
          el.setCustomValidity('Por favor, informe um número de telefone.');
          return;
        }
        if(el.value && !pat.test(el.value)){
          el.setCustomValidity('Formato inválido. Use (DD) 99999-9999 ou (DD) 9999-9999.');
        } else {
          el.setCustomValidity('');
        }
      }
      el.addEventListener('input', ()=>{
        const pos = el.selectionStart, oldLen = el.value.length;
        el.value = formatPhone(el.value);
        const diff = el.value.length - oldLen;
        el.setSelectionRange(pos+diff,pos+diff);
        validateTel(el);
      });
      el.addEventListener('blur', ()=> validateTel(el));
      el.addEventListener('invalid', ()=>{ validateTel(el); el.reportValidity(); });
    }
    applyPhoneMask(tel);
    applyPhoneMask(telResp);
  })();

})();
</script>

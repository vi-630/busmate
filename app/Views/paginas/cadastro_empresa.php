<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/cadastro_empresa.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="company-section">
  <div class="company-hero-bg"></div>

  <div class="company-card">
    <header class="company-head">
      <h2 class="company-title">Cadastrar empresa</h2>
      <p class="company-subtitle">Preencha os dados para criar a empresa e informar quantos administradores ela terá.</p>
    </header>

    <!-- Formulário (AGORA inclui a logo dentro do form) -->
    <form class="company-form" action="<?=URL?>/empresas/cadastrar" method="post" enctype="multipart/form-data" autocomplete="on">
      <!-- Upload da logo (dentro do form) -->
      <div class="logo-upload">
        <div class="logo-box">
          <img id="logoPreview" alt="Pré-visualização da logo" style="display:none;">
          <i id="logoPlaceholder" class="bi bi-buildings"></i>
        </div>

        <input id="logoFile" type="file" name="logo" accept="image/*" hidden>
        <button type="button" id="logoPick" class="btn-file">
          <i class="bi bi-image-fill"></i> Anexar logo
        </button>
        <small class="file-hint">PNG/JPG até 5&nbsp;MB • recomendado fundo transparente</small>
      </div>

      <!-- Nome / CNPJ -->
      <div class="form-grid">
        <div class="form-field">
          <label class="cf-label" for="empr_nome">Nome da empresa:</label>
          <input class="cf-input" type="text" id="empr_nome" name="empr_nome" placeholder="Ex.: Juma Transportes" required>
        </div>

        <div class="form-field">
          <label class="cf-label" for="empr_cnpj">CNPJ:</label>
          <input class="cf-input" type="text" id="empr_cnpj" name="empr_cnpj" inputmode="numeric" maxlength="18"
                 placeholder="00.000.000/0000-00" required>
        </div>
      </div>

      <!-- Razão Social -->
      <label class="cf-label" for="empr_razao">Razão social:</label>
      <input class="cf-input" type="text" id="empr_razao" name="empr_razao" placeholder="Ex.: Juma Transportes Ltda." required>

      <!-- Quantidade de administradores (campo livre) -->
      <div class="form-field">
        <label class="cf-label" for="empr_qtd_admin">Quantidade de administradores:</label>
        <input class="cf-input" type="number" id="empr_qtd_admin" name="empr_qtd_admin" min="1" step="1" placeholder="Ex.: 2" required>
      </div>

      <!-- Ações -->
      <div class="form-actions">
        <a class="btn-text" href="<?=URL?>/paginas/index_app"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button class="btn-primary" type="submit">
          Cadastrar
          <i class="bi bi-arrow-right-circle-fill"></i>
        </button>
      </div>

      <p class="next-hint">
        Após cadastrar, você será direcionado para criar
        <strong>um administrador por vez</strong> até atingir a quantidade informada.
      </p>
    </form>
  </div>
</section>

<script>
/* ===== Pré-visualização da LOGO ===== */
(function(){
  const input = document.getElementById('logoFile');
  const pick  = document.getElementById('logoPick');
  const img   = document.getElementById('logoPreview');
  const ph    = document.getElementById('logoPlaceholder');
  if(!input || !pick) return;

  pick.addEventListener('click', () => input.click());

  input.addEventListener('change', () => {
    const f = input.files && input.files[0];
    if(!f) return;
    const okType = /image\/(png|jpeg|jpg|webp)/i.test(f.type);
    if(!okType){ alert('Envie uma imagem PNG, JPG ou WEBP.'); input.value=''; return; }
    if(f.size > 5 * 1024 * 1024){ alert('Tamanho máximo: 5 MB.'); input.value=''; return; }

    const reader = new FileReader();
    reader.onload = e => {
      img.src = e.target.result;
      img.style.display = 'block';
      ph.style.display = 'none';
    };
    reader.readAsDataURL(f);
  });
})();

/* ===== Máscara simples de CNPJ (00.000.000/0000-00) ===== */
(function(){
  const cnpj = document.getElementById('empr_cnpj');
  if(!cnpj) return;

  function formatCNPJ(v){
    const n = v.replace(/\D/g,'').slice(0,14);
    const p1=n.slice(0,2), p2=n.slice(2,5), p3=n.slice(5,8), p4=n.slice(8,12), p5=n.slice(12,14);
    let out='';
    if(p1) out = p1;
    if(p2) out += '.'+p2;
    if(p3) out += '.'+p3;
    if(p4) out += '/'+p4;
    if(p5) out += '-'+p5;
    return out;
  }
  function validateCNPJ(){
    const ok = /^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/.test(cnpj.value);
    cnpj.setCustomValidity(ok ? '' : 'Use o formato 00.000.000/0000-00.');
  }
  cnpj.addEventListener('input', ()=>{
    const pos = cnpj.selectionStart, old = cnpj.value.length;
    cnpj.value = formatCNPJ(cnpj.value);
    const diff = cnpj.value.length - old;
    cnpj.setSelectionRange(pos + diff, pos + diff);
    validateCNPJ();
  });
  cnpj.addEventListener('blur', validateCNPJ);
  cnpj.addEventListener('invalid', ()=>{ validateCNPJ(); cnpj.reportValidity(); });
})();
</script>

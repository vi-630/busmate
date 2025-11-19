<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Carrega foto e nome do aluno (igual index_app)
$userPhoto = null;
$userName  = $_SESSION['user_name'] ?? 'Aluno';

if (!empty($_SESSION['user_id'])) {
    require_once dirname(__DIR__,2) . '/Libraries/Database.php';
    try {
        $db = new Database();
        $db->query('SELECT usua_foto, usua_nome FROM usuario WHERE usua_id = :id LIMIT 1');
        $db->bind(':id', $_SESSION['user_id']);
        $r = $db->resultado();

        if ($r) {
            if (!empty($r->usua_foto)) $userPhoto = $r->usua_foto;
            if (!empty($r->usua_nome)) $userName  = $r->usua_nome;
        }
    } catch (Throwable $t) {}
}
?>
<head>
  
  <link rel="stylesheet" href="<?= URL ?>/public/css/contrato.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
      <div class="profile">
        <div class="avatar">
          <?php if ($userPhoto): ?>
            <img src="<?= URL ?>/<?= htmlspecialchars($userPhoto) ?>" alt="Avatar" />
          <?php else: ?>
            <i class="bi bi-person-fill"></i>
          <?php endif; ?>
        </div>
        <strong class="user-name"><?= htmlspecialchars($userName) ?></strong>
      </div>

      <nav class="menu">
        <a href="<?=URL?>/paginas/index_app" class="item"><i class="bi bi-house-door-fill"></i><span>Início</span></a>
        <a href="<?=URL?>/paginas/forum" class="item"><i class="bi bi-chat-dots-fill"></i><span>Fórum</span></a>
        <a href="<?=URL?>/paginas/perfil" class="item"><i class="bi bi-person-badge-fill"></i><span>Perfil</span></a>
        <a href="<?=URL?>/paginas/contrato" class="item active"><i class="bi bi-file-earmark-text-fill"></i><span>Contrato</span></a>
      </nav>
  </aside>

  <main class="content">

    <header class="page-head">
      <h1>Contrato e Mensalidade</h1>
    </header>


    <!-- ALERTAS -->
    <?php if (!empty($_GET['erro'])): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <?php ?>



    <!-- ======================================================== -->
    <!-- ======================== CONTRATO ======================= -->
    <!-- ======================================================== -->

    <section class="card-box">
      <h2>Contrato</h2>

      <?php if (!$contrato): ?>
        <p>Você ainda não possui um contrato gerado pela empresa.</p>
        <p>Aguarde a aprovação do seu cadastro.</p>
      <?php else: ?>
        <p><strong>Empresa:</strong> <?= htmlspecialchars($contrato->empr_nome ?? '') ?> (CNPJ <?= htmlspecialchars($contrato->empr_cnpj ?? '') ?>)</p>
        <p><strong>Status do contrato:</strong>
          <?php
            $cls = 'badge-inativo';
            if (isset($contrato->cont_situacao) && $contrato->cont_situacao === 'ATIVO') $cls = 'badge-ativo';
            if (isset($contrato->cont_situacao) && $contrato->cont_situacao === 'RASCUNHO') $cls = 'badge-pendente';
            if (isset($contrato->cont_situacao) && $contrato->cont_situacao === 'ANALISE')  $cls = 'badge-pendente';
          ?>
          <span class="badge <?= $cls ?>"><?= htmlspecialchars($contrato->cont_situacao ?? '') ?></span>
        </p>
        <p><strong>Início:</strong> <?= !empty($contrato->cont_inicio_vigencia) ? date('d/m/Y', strtotime($contrato->cont_inicio_vigencia)) : '—' ?></p>
        <p><strong>Fim:</strong> <?= !empty($contrato->cont_fim_vigencia) ? date('d/m/Y', strtotime($contrato->cont_fim_vigencia)) : '—' ?></p>
        <?php
          $empresaContratoUrl = (!empty($contrato->empr_contrato_url))
              ? URL . '/' . $contrato->empr_contrato_url
              : URL . '/public/docs/Contrato.pdf';
        ?>
        <!-- ==================== CALL OUT DO CONTRATO ===================== -->
        <?php $situacaoContrato = $contrato->cont_situacao ?? ''; ?>
        <div class="contrato-callout" style="margin-top:16px;">
          <p>
            <?php if ($situacaoContrato === 'RASCUNHO'): ?>
              Seu contrato está em preparação. Para concluir o cadastro, leia o contrato e faça o upload do contrato assinado.
            <?php elseif ($situacaoContrato === 'ANALISE'): ?>
              Contrato enviado! Aguarde a análise do administrador.
            <?php elseif ($situacaoContrato === 'ATIVO'): ?>
              Contrato aprovado! A vigência está ativa e agora você pode acessar as mensalidades.
            <?php elseif ($situacaoContrato === 'CANCELADO'): ?>
              Contrato cancelado. O documento da empresa segue disponível para consulta abaixo.
            <?php else: ?>
              Situação atual do contrato: <?= htmlspecialchars($situacaoContrato) ?>.
            <?php endif; ?>
          </p>
          <?php if ($situacaoContrato === 'ATIVO'): ?>
            <div style="margin-top:12px;">
              <form method="post" action="<?= URL ?>/paginas/cancelarContrato" onsubmit="return confirm('Deseja realmente cancelar o contrato?');">
                <button type="submit" class="btn-soli" style="background:#e74c3c; color:#fff; padding:8px 12px; border-radius:6px;">
                  Cancelar contrato
                </button>
              </form>
            </div>
          <?php endif; ?>
          <!-- Tabela: documentos do contrato mostrado no callout -->
          <table class="soli-doc-table">
            <?php if (!empty($contrato->cont_assinado_url)): ?>
            <tr>
              <td class="doc-label">Contrato Assinado</td>
              <td class="doc-action">
                <i class="bi bi-eye"></i>
                <a href="<?= URL . '/' . ltrim($contrato->cont_assinado_url, '/') ?>" target="_blank">Abrir</a>
              </td>
              <td class="doc-action">
                <i class="bi bi-download"></i>
                <a href="<?= URL . '/' . ltrim($contrato->cont_assinado_url, '/') ?>" download="Contrato_assinado.<?= pathinfo($contrato->cont_assinado_url, PATHINFO_EXTENSION) ?: 'pdf' ?>">Baixar</a>
              </td>
            </tr>
            <?php endif; ?>
            <tr>
              <td class="doc-label">Contrato da Empresa</td>
              <td class="doc-action">
                <i class="bi bi-eye"></i>
                <a href="<?= htmlspecialchars($empresaContratoUrl) ?>" target="_blank">Abrir</a>
              </td>
              <td class="doc-action">
                <i class="bi bi-download"></i>
                <a href="<?= htmlspecialchars($empresaContratoUrl) ?>" download="Contrato_empresa.pdf">Baixar</a>
              </td>
            </tr>
          </table>
          <?php if (!empty($contrato->cont_motivo_recusa)): ?>
            <div class="alert alert-warning" style="margin-top:12px;">
              <strong>Motivo da recusa:</strong> <?= htmlspecialchars($contrato->cont_motivo_recusa) ?>
            </div>
          <?php endif; ?>
          <!-- ======================== UPLOAD (somente rascunho) ======================= -->
          <?php if ($situacaoContrato === 'RASCUNHO'): ?>
            <p class="contrato-hint" style="margin-top: 12px;">Leu e concorda? Por favor, anexe aqui o contrato assinado.</p>
            <form class="upload-form"
                  action="<?= URL ?>/paginas/salvarContratoAssinado"
                  method="post"
                  enctype="multipart/form-data"
                  style="margin-top:8px;">
              <table class="soli-doc-table">
                <tr>
                  <td class="doc-label">Contrato Assinado</td>
                  <td class="doc-action">
                    <label class="btn-outline">
                      <i class="bi bi-upload"></i> Anexar
                      <input type="file"
                             name="contrato_assinado"
                             id="contratoAssinadoInput"
                             accept=".pdf,.jpg,.jpeg,.png,.webp"
                             required
                             style="display:none;"
                             onchange="document.getElementById('nome-arquivo').innerText = this.files[0]?.name || '';">
                    </label>
                  </td>
                </tr>
              </table>
              <div id="nome-arquivo"
                   style="margin-top:6px; font-size:.85rem; color:#555;"></div>
              <button type="submit" class="btn-primary">
                Enviar contrato
              </button>
            </form>
          <?php endif; ?>
        </div>
        <!-- Contrato assinado agora é exibido no callout acima (evita duplicação) -->
      <?php endif; ?>
    </section>




    <!-- ======================================================== -->
    <!-- ====================== MENSALIDADE ====================== -->
    <!-- ======================================================== -->

    <section class="card-box">
  <h2>Mensalidade – competência <?= htmlspecialchars($competencia) ?></h2>
  <?php if (!$contrato || !isset($contrato->cont_situacao) || $contrato->cont_situacao !== 'ATIVO'): ?>
    <p>Você ainda não possui contrato ativo.</p>
    <p>Assim que seu contrato for aprovado, esta área será liberada.</p>
  <?php else: ?>
    <?php
      $temPix      = !empty($contrato->empr_pix_url) || !empty($contrato->empr_chave_pix);
      $pixImgUrl   = !empty($contrato->empr_pix_url) ? URL . '/' . ltrim($contrato->empr_pix_url, '/') : null;
      $pixChave    = $contrato->empr_chave_pix ?? '';
      $rawValor = $contrato->cont_valor_total ?? $contrato->empr_vlr_mensalidade ?? null;
      $valorMensal = $rawValor ? number_format($rawValor, 2, ',', '.') : null;
      $nomeEmpresa = $contrato->empr_nome ?? 'Empresa';
      $mostrarPixCTA = $temPix && (!$pagamento || $pagamento->paga_situacao !== 'PAGO');
    ?>
    <?php if ($mostrarPixCTA): ?>
      <div class="mensalidade-callout">
        <p>Você ainda precisa realizar o pagamento deste mês. Clique no botão abaixo para ver os dados do Pix.</p>
        <button type="button" class="btn-primary btn-pix" id="btnAbrirPixModal">
          <i class="bi bi-qr-code-scan"></i>
          Ver dados para pagamento (Pix)
        </button>
      </div>
    <?php endif; ?>
    <?php if (!$pagamento || ($pagamento && $pagamento->paga_situacao === 'RECUSADO')): ?>
      <?php if ($pagamento && $pagamento->paga_situacao === 'RECUSADO'): ?>
        <p style="color:#d9534f; margin-bottom:12px;">
          <i class="bi bi-exclamation-circle-fill"></i>
          Seu comprovante foi recusado. Envie um novo comprovante.
        </p>
      <?php else: ?>
        <p style="margin-top: 14px;">Não encontramos pagamento para este mês.</p>
        <p>Após efetuar o Pix, envie o comprovante para liberar o acesso.</p>
      <?php endif; ?>
      <form class="pagamento-form" action="<?=URL?>/pagamentos/enviarComprovante" method="post" enctype="multipart/form-data">
        <input type="hidden" name="cont_id" value="<?= (int)$contrato->cont_id ?>">
        <input type="hidden" name="competencia" value="<?= htmlspecialchars($competencia) ?>">
        <div class="field-line">
          <label>Comprovante (PDF/JPG/PNG/WEBP):</label>
          <input type="file" name="comprovante" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
        </div>
        <button class="btn-primary">Enviar comprovante</button>
      </form>
    <?php else: ?>
      <p><strong>Status:</strong>
        <?php if ($pagamento->paga_situacao === 'PAGO'): ?>
          <span class="badge badge-ativo">PAGO</span>
        <?php elseif ($pagamento->paga_situacao === 'PENDENTE'): ?>
          <span class="badge badge-pendente">Em análise</span>
        <?php else: ?>
          <span class="badge badge-recusada">RECUSADO</span>
        <?php endif; ?>
      </p>
      <?php if (!empty($pagamento->paga_comprovante_url)): ?>
        <p><strong>Comprovante:</strong>
          <a href="<?= URL . '/' . $pagamento->paga_comprovante_url ?>" target="_blank" class="btn-link">Abrir comprovante</a>
        </p>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($temPix): ?>
      <!-- MODAL DO PIX -->
      <div id="pixModal" class="pix-modal-overlay" style="display:none;">
        <div class="pix-modal">
          <button type="button" class="pix-modal-close" id="btnFecharPixModal">
            <i class="bi bi-x-lg"></i>
          </button>
          <h3>Pagamento via Pix</h3>
          <?php if ($pixImgUrl): ?>
            <div class="pix-qrcode-wrap">
              <img src="<?= htmlspecialchars($pixImgUrl) ?>" alt="QR Code do Pix da empresa">
            </div>
          <?php endif; ?>
          <div class="pix-info">
            <p><strong>Empresa:</strong> <?= htmlspecialchars($nomeEmpresa) ?></p>
            <?php if ($valorMensal): ?>
              <p><strong>Valor:</strong> R$ <?= $valorMensal ?></p>
            <?php endif; ?>
            <?php if (!empty($pixChave)): ?>
              <p><strong>Chave Pix:</strong> <span class="pix-chave"><?= htmlspecialchars($pixChave) ?></span></p>
            <?php endif; ?>
          </div>
          <p class="pix-hint">
            Faça o pagamento utilizando o QR Code ou a chave Pix acima e, em seguida, envie o comprovante na área de mensalidade.
          </p>
        </div>
      </div>
      <script>
        (function() {
          const abrir = document.getElementById('btnAbrirPixModal');
          const modal = document.getElementById('pixModal');
          const fechar = document.getElementById('btnFecharPixModal');
          if (abrir && modal && fechar) {
            abrir.addEventListener('click', function() {
              modal.style.display = 'flex';
            });
            fechar.addEventListener('click', function() {
              modal.style.display = 'none';
            });
            modal.addEventListener('click', function(e) {
              if (e.target === modal) {
                modal.style.display = 'none';
              }
            });
          }
        })();
      </script>
    <?php endif; ?>
  <?php endif; ?>
</section>



    <div class="contrato-footer-hint">
      <small>Enquanto não houver pagamento PAGO, algumas áreas ficarão bloqueadas.</small>
    </div>

  </main>
</section>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/solicitacao_status.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <meta http-equiv="refresh" content="10">
</head>

<section class="status-wrap">
  <div class="status-card">
    <h2>Status da sua solicitação</h2>

    <?php if (!$sol): ?>
      <div class="alert alert-danger">Solicitação não encontrada.</div>
      <div class="actions">
        <a class="btn-text" href="<?=URL?>/paginas/home"><i class="bi bi-house-door-fill"></i> Ir para início</a>
      </div>
    <?php else: ?>
      <p><strong>Empresa:</strong> <?=htmlspecialchars($sol->empr_nome)?> (CNPJ <?=htmlspecialchars($sol->empr_cnpj)?>)</p>
      <p><strong>Enviado em:</strong> <?=date('d/m/Y H:i', strtotime($sol->soli_dth_criacao))?></p>
      <p><strong>Status:</strong>
        <?php if ($sol->status === 'PENDENTE'): ?>
          <span class="badge badge-pendente">Aguardando aprovação</span>
        <?php elseif ($sol->status === 'ACEITA'): ?>
          <span class="badge badge-aceita">Aceita</span>
        <?php else: ?>
          <span class="badge badge-recusada">Recusada</span>
        <?php endif; ?>
      </p>

      <?php if ($sol->status === 'RECUSADA' && !empty($sol->motivo_recusa)): ?>
        <p><strong>Motivo da recusa:</strong> <?=htmlspecialchars($sol->motivo_recusa)?></p>
      <?php endif; ?>

      <div class="actions">
        <?php if ($sol->status === 'PENDENTE'): ?>
          <small>Esta página atualiza automaticamente a cada 10s.</small>
        <?php else: ?>
          <a class="btn-text" href="<?=URL?>/paginas/entrar"><i class="bi bi-box-arrow-in-right"></i> Ir para entrar</a>
          <a class="btn-text" href="<?=URL?>/paginas/home"><i class="bi bi-house-door-fill"></i> Início</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

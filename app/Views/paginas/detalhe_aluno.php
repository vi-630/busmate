<?php
// Vars vindas do controller:
// $aluno, $docs (array docu_tipo => docu_url), $contrato, $competencia, $pagamento
?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/solicitacao_detalhe.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="soli-section">
  <div class="soli-container">

    <a href="<?=URL?>/paginas/index_app" class="soli-back-link">
      <i class="bi bi-arrow-left"></i>
      <span>Voltar</span>
    </a>

    <h1 class="soli-page-title">Detalhe do aluno</h1>

    <div class="soli-card">

      <!-- =============== CABEÇALHO =============== -->
      <header class="soli-header">
        <div class="soli-avatar">
          <?php if (!empty($aluno->usua_foto)): ?>
            <img src="<?= URL ?>/<?= htmlspecialchars($aluno->usua_foto) ?>"
                 alt="Foto de <?= htmlspecialchars($aluno->usua_nome) ?>"
                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
          <?php else: ?>
            <i class="bi bi-person-fill"></i>
          <?php endif; ?>
        </div>

        <div class="soli-header-info">
          <h2 class="soli-name">
            <?= htmlspecialchars($aluno->usua_nome ?? 'Nome do aluno') ?>
          </h2>

          <div class="soli-chips">
            <!-- Empresa -->
            <span class="chip chip-company">
              <i class="bi bi-building"></i>
              <?= htmlspecialchars($aluno->empr_nome ?? 'Empresa') ?>
            </span>

            <!-- Status do aluno (A/I/B) -->
            <?php
              $usit = $aluno->usua_situacao ?? 'A';
              $labelStatus = 'Ativo';
              $statusClass = 'chip-status-aprovada';

              if ($usit === 'I') {
                $labelStatus = 'Inativo';
                $statusClass = 'chip-status-recusada';
              } elseif ($usit === 'B') {
                $labelStatus = 'Bloqueado';
                $statusClass = 'chip-status-recusada';
              }
            ?>
            <span class="chip chip-status <?= $statusClass ?>">
              <i class="bi bi-toggle-on"></i>
              <?= $labelStatus ?>
            </span>

            <!-- Data de criação do usuário -->
            <span class="chip chip-date">
              <i class="bi bi-calendar-event"></i>
              <?= isset($aluno->usua_dth_criacao)
                ? date('d/m/Y H:i', strtotime($aluno->usua_dth_criacao))
                : '—' ?>
            </span>
          </div>

          <!-- Botão ativar / desativar aluno -->
          <div style="margin-top: 12px;">
            <form method="post" action="<?= URL ?>/usuarios/trocarSituacao" style="display:inline-block;">
              <input type="hidden" name="usua_id" value="<?= (int)$aluno->usua_id ?>">
              <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

              <?php if ($usit === 'A'): ?>
                <input type="hidden" name="nova_situacao" value="I">
                <button type="submit" class="btn-soli btn-recusar" style="padding: 6px 14px; font-size:.85rem;">
                  Desativar aluno
                </button>
              <?php else: ?>
                <input type="hidden" name="nova_situacao" value="A">
                <button type="submit" class="btn-soli btn-aceitar" style="padding: 6px 14px; font-size:.85rem;">
                  Ativar aluno
                </button>
              <?php endif; ?>
            </form>
          </div>

        </div>
      </header>

      <!-- =============== DADOS DO ALUNO =============== -->
      <section class="soli-block">
        <h3 class="soli-block-title">Dados</h3>

        <div class="soli-grid">
          <div class="soli-field">
            <label>Curso</label>
            <input type="text" value="<?= htmlspecialchars($aluno->usua_curso ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>Turma</label>
            <input type="text" value="<?= htmlspecialchars($aluno->usua_turma ?? '') ?>" readonly>
          </div>

          <div class="soli-field soli-field-full">
            <label>Escola</label>
            <input type="text" value="<?= htmlspecialchars($aluno->usua_escola ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>Turno</label>
            <input type="text" value="<?= htmlspecialchars($aluno->usua_turno ?? '') ?>" readonly>
          </div>

          <div class="soli-field soli-field-full">
            <label>Endereço</label>
            <input type="text" value="<?= htmlspecialchars($aluno->usua_endereco ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>E-mail principal</label>
            <input type="text" value="<?= htmlspecialchars($aluno->email_principal ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>E-mail secundário</label>
            <input type="text" value="<?= htmlspecialchars($aluno->email_secundario ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>Telefone do aluno</label>
            <input type="text" value="<?= htmlspecialchars($aluno->telefone ?? '') ?>" readonly>
          </div>

          <div class="soli-field">
            <label>Telefone do responsável</label>
            <input type="text" value="<?= htmlspecialchars($aluno->telefone_responsavel ?? '') ?>" readonly>
          </div>
        </div>
      </section>

      <!-- =============== DOCUMENTAÇÃO (tabela documento) =============== -->
      <section class="soli-block">
        <h3 class="soli-block-title">Documentação</h3>

        <table class="soli-doc-table">
          <tbody>
          <?php
            // Mapeia os tipos para labels amigáveis
            $docLabels = [
              'MATRICULA'   => 'Comprovante de Matrícula',
              'RESIDENCIA'  => 'Comprovante de Residência',
              'RG'          => 'RG do Aluno',
              'CPF'         => 'CPF do Aluno',
              'RESPONSAVEL' => 'Documento do Responsável'
            ];

            $temDoc = false;
            foreach ($docLabels as $tipo => $label):
              if (!empty($docs[$tipo])):
                $temDoc = true;
                $url = URL . '/' . ltrim($docs[$tipo], '/');
          ?>
                <tr>
                  <td class="doc-label"><?= htmlspecialchars($label) ?></td>

                  <td class="doc-action">
                    <i class="bi bi-eye"></i>
                    <a href="<?= htmlspecialchars($url) ?>" target="_blank">Visualizar</a>
                  </td>

                  <td class="doc-action">
                    <i class="bi bi-download"></i>
                    <a href="<?= htmlspecialchars($url) ?>" download="<?= htmlspecialchars($label) ?>.<?= pathinfo($docs[$tipo], PATHINFO_EXTENSION) ?>">Baixar</a>
                  </td>
                </tr>
          <?php
              endif;
            endforeach;

            if (!$temDoc):
          ?>
            <tr>
              <td colspan="3" class="doc-label">Nenhum documento disponível</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </section>

      <!-- =============== CONTRATO =============== -->
      <section class="soli-block">
        <h3 class="soli-block-title">Contrato</h3>

        <?php if (empty($contrato)): ?>
          <p>Aluno ainda não possui contrato gerado para esta empresa.</p>
        <?php else: ?>
          <?php
            $contStatus = $contrato->cont_situacao ?? 'RASCUNHO';
            $contClass  = 'chip-status-pendente';
            if ($contStatus === 'ATIVO')                       $contClass = 'chip-status-aprovada';
            elseif ($contStatus === 'ANALISE')                 $contClass = 'chip-status-pendente';
            elseif (in_array($contStatus, ['ENCERRADO','CANCELADO'])) $contClass = 'chip-status-recusada';

            $empresaContratoUrl = !empty($contrato->empr_contrato_url)
              ? URL . '/' . $contrato->empr_contrato_url
              : URL . '/public/docs/Contrato.pdf';
          ?>

          <p><strong>Empresa:</strong>
            <?= htmlspecialchars($contrato->empr_nome) ?>
            (CNPJ <?= htmlspecialchars($contrato->empr_cnpj) ?>)
          </p>

          <p><strong>Status do contrato:</strong>
            <span class="chip chip-status <?= $contClass ?>">
              <?= htmlspecialchars($contStatus) ?>
            </span>
          </p>

          <?php if (!empty($contrato->cont_motivo_recusa)): ?>
            <p style="margin-top: 6px; font-size:.9rem; color:#a33;">
              <strong>Última recusa:</strong> <?= htmlspecialchars($contrato->cont_motivo_recusa) ?>
            </p>
          <?php endif; ?>

          <p><strong>Início:</strong>
            <?= $contrato->cont_inicio_vigencia ? date('d/m/Y', strtotime($contrato->cont_inicio_vigencia)) : '—' ?>
          </p>
          <p><strong>Fim:</strong>
            <?= $contrato->cont_fim_vigencia ? date('d/m/Y', strtotime($contrato->cont_fim_vigencia)) : '—' ?>
          </p>

          <!-- Contrato modelo -->
          <table class="soli-doc-table" style="margin-top: 10px;">
            <tbody>
              <tr>
                <td class="doc-label">Contrato (modelo oficial)</td>
                <td class="doc-action">
                  <i class="bi bi-eye"></i>
                  <a href="<?= htmlspecialchars($empresaContratoUrl) ?>" target="_blank">Visualizar</a>
                </td>
                <td class="doc-action">
                  <i class="bi bi-download"></i>
                  <a href="<?= htmlspecialchars($empresaContratoUrl) ?>" download="Contrato_busmate.<?= pathinfo($empresaContratoUrl, PATHINFO_EXTENSION) ?>">Baixar</a>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Contrato assinado -->
          <?php if (!empty($contrato->cont_assinado_url)): ?>
            <table class="soli-doc-table" style="margin-top: 8px;">
              <tbody>
                <tr>
                  <td class="doc-label">Contrato assinado</td>
                  <td class="doc-action">
                    <i class="bi bi-eye"></i>
                    <a href="<?= URL . '/' . htmlspecialchars($contrato->cont_assinado_url) ?>" target="_blank">
                      Visualizar
                    </a>
                  </td>
                  <td class="doc-action">
                    <i class="bi bi-download"></i>
                    <a href="<?= URL . '/' . htmlspecialchars($contrato->cont_assinado_url) ?>" download="Contrato_assinado.<?= pathinfo($contrato->cont_assinado_url, PATHINFO_EXTENSION) ?>">
                      Baixar
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          <?php else: ?>
            <p style="margin-top: 6px; font-size:.9rem; color:#777;">
              Contrato assinado ainda não foi enviado pelo aluno.
            </p>
          <?php endif; ?>

          <!-- Ações de aprovação/recusa do contrato (ADMIN) -->
          <?php if ($contStatus === 'ANALISE' && !empty($contrato->cont_assinado_url)): ?>
            <div style="margin-top: 14px;">
              <form method="post" action="<?= URL ?>/solicitacoes/aprovarContrato" style="display:inline-block; margin-right:8px;">
                <input type="hidden" name="cont_id" value="<?= (int)$contrato->cont_id ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                <button type="submit" class="btn-soli btn-aceitar" style="padding:8px 16px; font-size:14px;">
                  Aprovar contrato
                </button>
              </form>

              <button type="button" id="btn-recusar-contrato-toggle" class="btn-soli btn-recusar" style="padding:8px 16px; font-size:14px;">
                Recusar contrato
              </button>
            </div>

            <div id="recusar-contrato-form"
                 style="display:none; margin-top:16px; padding:16px; background-color:#f9f0f0; border-radius:8px;">
              <form method="post" action="<?= URL ?>/solicitacoes/recusarContrato">
                <input type="hidden" name="cont_id" value="<?= (int)$contrato->cont_id ?>">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

                <label for="motivo-contrato" style="display:block; margin-bottom:8px; font-weight:bold;">
                  Motivo da recusa:
                </label>
                <textarea id="motivo-contrato" name="motivo"
                          placeholder="Descreva o motivo da recusa do contrato."
                          style="width:100%; min-height:80px; padding:8px; border:1px solid #ccc; border-radius:4px; font-family:inherit;"
                          required></textarea>

                <div style="margin-top:12px; display:flex; gap:8px;">
                  <button type="submit" class="btn-soli btn-recusar" style="padding:8px 16px; font-size:14px;">
                    Confirmar recusa
                  </button>
                  <button type="button" id="btn-cancelar-recusa-contrato" class="btn-soli" style="padding:8px 16px; font-size:14px; background-color:#999;">
                    Cancelar
                  </button>
                </div>
              </form>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </section>

      <!-- =============== MENSALIDADE =============== -->
      <section class="soli-block">
        <h3 class="soli-block-title">Mensalidade</h3>

        <?php if (empty($contrato) || ($contrato->cont_situacao ?? 'RASCUNHO') !== 'ATIVO'): ?>
          <p>Contrato ainda não está ativo. A mensalidade só é liberada após aprovação do contrato.</p>
        <?php else: ?>

          <p><strong>Competência:</strong> <?= htmlspecialchars($competencia ?? '') ?></p>

          <?php if (empty($pagamento)): ?>
            <p>Nenhum pagamento encontrado para esta competência.</p>
          <?php else: ?>
            <p><strong>Status do pagamento:</strong>
              <?php
                $pStatus = strtoupper($pagamento->paga_situacao ?? 'PENDENTE');
                $pClass  = 'chip-status-pendente';
                if ($pStatus === 'PAGO')     $pClass = 'chip-status-aprovada';
                if ($pStatus === 'FALHOU') $pClass = 'chip-status-recusada';
              ?>
              <span class="chip chip-status <?= $pClass ?>">
                <?= htmlspecialchars($pStatus) ?>
              </span>
            </p>

            <p><strong>Valor:</strong> R$ <?= number_format($pagamento->paga_valor, 2, ',', '.') ?></p>

            <?php if (!empty($pagamento->paga_comprovante_url)): ?>
              <p><strong>Comprovante:</strong>
                <a href="<?= URL . '/' . $pagamento->paga_comprovante_url ?>" target="_blank">
                  Abrir comprovante
                </a>
              </p>
            <?php endif; ?>

            <!-- Ações de aprovação/recusa (apenas se PENDENTE) -->
            <?php if (($pagamento->paga_situacao ?? '') === 'PENDENTE'): ?>
              <div style="margin-top: 14px; display: flex; gap: 8px;">
                <form method="post" action="<?= URL ?>/pagamentos/aprovarPagamento" style="display:inline;">
                  <input type="hidden" name="paga_id" value="<?= (int)$pagamento->paga_id ?>">
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                  <button type="submit" class="btn-soli btn-aceitar" style="padding: 8px 12px; font-size: 14px;">
                    Aprovar pagamento
                  </button>
                </form>

                <button type="button" id="btn-recusar-pagamento-toggle-<?= $pagamento->paga_id ?>" class="btn-soli btn-recusar" style="padding: 8px 12px; font-size: 14px;">
                  Recusar pagamento
                </button>
              </div>

              <div id="recusar-pagamento-form-<?= $pagamento->paga_id ?>"
                   style="display:none; margin-top:12px; padding:12px; background-color:#f9f0f0; border-radius:8px;">
                <form method="post" action="<?= URL ?>/pagamentos/recusarPagamento">
                  <input type="hidden" name="paga_id" value="<?= (int)$pagamento->paga_id ?>">
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

                  <textarea name="motivo"
                            placeholder="Descreva o motivo da recusa (opcional)."
                            style="width:100%; min-height:60px; padding:8px; border:1px solid #ccc; border-radius:4px; font-family:inherit;"></textarea>

                  <div style="margin-top:8px; display:flex; gap:8px;">
                    <button type="submit" class="btn-soli btn-recusar" style="padding:8px 12px; font-size:14px;">
                      Confirmar recusa
                    </button>
                    <button type="button" class="btn-cancelar-recusa-pagamento" data-id="<?= $pagamento->paga_id ?>" style="padding:8px 12px; font-size:14px; background-color:#999; color:#fff; border:none; border-radius:4px; cursor:pointer;">
                      Cancelar
                    </button>
                  </div>
                </form>
              </div>

              <script>
                (function() {
                  const pagaId = <?= (int)$pagamento->paga_id ?>;
                  const toggle = document.getElementById('btn-recusar-pagamento-toggle-' + pagaId);
                  const form = document.getElementById('recusar-pagamento-form-' + pagaId);
                  const cancelBtns = document.querySelectorAll('.btn-cancelar-recusa-pagamento[data-id="' + pagaId + '"]');

                  if (toggle && form) {
                    toggle.addEventListener('click', function() {
                      form.style.display = 'block';
                      toggle.style.display = 'none';
                    });
                  }

                  cancelBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                      form.style.display = 'none';
                      toggle.style.display = 'inline-block';
                    });
                  });
                })();
              </script>
            <?php endif; ?>

          <?php endif; ?>

        <?php endif; ?>
      </section>

      <!-- =============== Scripts locais =============== -->
      <script>
        const btnRecusarContratoToggle = document.getElementById('btn-recusar-contrato-toggle');
        const recusarContratoForm      = document.getElementById('recusar-contrato-form');
        const btnCancelarRecusaContrato = document.getElementById('btn-cancelar-recusa-contrato');

        if (btnRecusarContratoToggle && recusarContratoForm) {
          btnRecusarContratoToggle.addEventListener('click', function() {
            recusarContratoForm.style.display = 'block';
            btnRecusarContratoToggle.style.display = 'none';
          });
        }

        if (btnCancelarRecusaContrato && recusarContratoForm && btnRecusarContratoToggle) {
          btnCancelarRecusaContrato.addEventListener('click', function() {
            recusarContratoForm.style.display = 'none';
            btnRecusarContratoToggle.style.display = 'inline-block';
          });
        }
      </script>

    </div>
  </div>
</section>

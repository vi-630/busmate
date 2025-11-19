<?php 
// $sol = registro da solicitação + dados da empresa
// $docs = array com os docs da solicitação vindo do controller
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/solicitacao_detalhe.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout"><!-- 🔥 ENVOLVENDO TUDO NO LAYOUT PADRÃO -->

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile">
      <?php
        $userPhoto = null;
        if (!empty($_SESSION['user_id'])) {
          require_once dirname(__DIR__,2) . '/Libraries/Database.php';
          try {
            $db = new Database();
            $db->query('SELECT usua_foto, usua_nome FROM usuario WHERE usua_id = :id LIMIT 1');
            $db->bind(':id', $_SESSION['user_id']);
            $r = $db->resultado();
            if ($r) {
              if (!empty($r->usua_foto)) $userPhoto = $r->usua_foto;
              if (!empty($r->usua_nome)) $_SESSION['user_name'] = $r->usua_nome;
            }
          } catch (Throwable $t) {
            $userPhoto = null;
          }
        }
      ?>
      <div class="avatar">
        <?php if ($userPhoto): ?>
          <img src="<?= URL ?>/<?= htmlspecialchars($userPhoto) ?>" alt="Avatar" />
        <?php else: ?>
          <i class="bi bi-person-fill"></i>
        <?php endif; ?>
      </div>
      <strong class="user-name">
        <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?>
      </strong>
    </div>

    <?php if (!empty($_SESSION['user_tipo'])): ?>
      <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

      <nav class="menu">
        <a href="<?=URL?>/paginas/index_app" class="item">
          <i class="bi bi-people-fill"></i>
          <span>Alunos</span>
        </a>
        <a href="<?=URL?>/avisos" class="item">
          <i class="bi bi-bell-fill"></i>
          <span>Avisos</span>
        </a>
        <a href="<?=URL?>/viagens/minhas" class="item">
          <i class="bi bi-bus-front-fill"></i>
          <span>Viagens</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
        <a href="<?=URL?>/solicitacoes/index" class="item active">
          <i class="bi bi-list-check"></i>
          <span>Solicitações</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
      </nav>
    <?php endif; ?>
  </aside>

  <!-- CONTEÚDO PRINCIPAL -->
  <main class="content">
    <section class="soli-section">
      <div class="soli-container">

        <a href="<?=URL?>/solicitacoes/index" class="soli-back-link">
          <i class="bi bi-arrow-left"></i>
          <span>Voltar</span>
        </a>

        <h1 class="soli-page-title">Solicitação</h1>

        <div class="soli-card">

          <!-- CABEÇALHO DA SOLICITAÇÃO -->
          <header class="soli-header">
            <div class="soli-avatar">
              <?php if (!empty($sol->soli_foto_url)): ?>
                <img src="<?= URL ?>/<?= htmlspecialchars($sol->soli_foto_url) ?>" alt="Foto de <?= htmlspecialchars($sol->soli_nome) ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
              <?php else: ?>
                <i class="bi bi-person-fill"></i>
              <?php endif; ?>
            </div>

            <div class="soli-header-info">
              <h2 class="soli-name">
                <?= htmlspecialchars($sol->soli_nome ?? 'Nome do aluno') ?>
              </h2>

              <div class="soli-chips">
                <span class="chip chip-company">
                  <i class="bi bi-building"></i>
                  <?= htmlspecialchars($sol->empr_nome ?? 'Juma Transportes') ?>
                </span>

                <?php
                  $status = strtoupper($sol->soli_status ?? 'PENDENTE');
                  $statusClass = 'chip-status-pendente';
                  if ($status === 'ACEITA')   $statusClass = 'chip-status-aprovada';
                  if ($status === 'RECUSADA') $statusClass = 'chip-status-recusada';
                ?>
                <span class="chip chip-status <?= $statusClass ?>">
                  <i class="bi bi-badge-ad"></i>
                  <?= $status ?>
                </span>

                <span class="chip chip-date">
                  <i class="bi bi-calendar-event"></i>
                  <?= isset($sol->soli_dth_criacao)
                    ? date('d/m/Y H:i', strtotime($sol->soli_dth_criacao))
                    : '—' ?>
                </span>
              </div>
            </div>
          </header>

          <!-- ======================= DADOS ======================= -->
          <section class="soli-block">
            <h3 class="soli-block-title">Dados</h3>

            <div class="soli-grid">
              <div class="soli-field">
                <label>E-mail</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_email ?? '') ?>" readonly>
              </div>

              <div class="soli-field">
                <label>E-mail de recuperação</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_email_recuperacao ?? '') ?>" readonly>
              </div>

              <div class="soli-field">
                <label>Telefone</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_tel ?? '') ?>" readonly>
              </div>

              <div class="soli-field">
                <label>Telefone do responsável</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_responsavel_tel ?? '') ?>" readonly>
              </div>

              <div class="soli-field soli-field-full">
                <label>Endereço</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_endereco ?? '') ?>" readonly>
              </div>

              <div class="soli-field soli-field-full">
                <label>Escola</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_escola ?? '') ?>" readonly>
              </div>

              <div class="soli-field">
                <label>Turno</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_turno ?? '') ?>" readonly>
              </div>

              <div class="soli-field">
                <label>Curso</label>
                <input type="text" value="<?= htmlspecialchars($sol->soli_curso ?? '') ?>" readonly>
              </div>
            </div>
          </section>

          <!-- =================== DOCUMENTAÇÃO ==================== -->
          <section class="soli-block">
            <h3 class="soli-block-title">Documentação</h3>

            <table class="soli-doc-table">
              <tbody>
              <?php
              if (!empty($docs) && is_array($docs)):
                $docLabels = [
                  'COMPROVANTE_MATRICULA' => 'Comprovante de Matrícula',
                  'COMPROVANTE_RESIDENCIA' => 'Comprovante de Residência',
                  'RG_ALUNO' => 'RG do Aluno',
                  'CPF_ALUNO' => 'CPF do Aluno',
                  'DOC_RESPONSAVEL' => 'Documento do Responsável'
                ];
                foreach ($docLabels as $tipo => $label):
                  if (isset($docs[$tipo])): ?>
                    <tr>
                      <td class="doc-label"><?= htmlspecialchars($label) ?></td>

                      <td class="doc-action">
                        <i class="bi bi-eye"></i>
                        <a href="<?= URL ?>/solicitacoes/visualizar_documento?id=<?= (int)$sol->soli_id ?>&tipo=<?= urlencode($tipo) ?>" target="_blank">Visualizar</a>
                      </td>

                      <td class="doc-action">
                        <i class="bi bi-download"></i>
                        <a href="<?= URL ?>/solicitacoes/download_documento?id=<?= (int)$sol->soli_id ?>&tipo=<?= urlencode($tipo) ?>">Baixar</a>
                      </td>
                    </tr>
                  <?php endif;
                endforeach;
              else: ?>
                <tr>
                  <td colspan="3" class="doc-label">Nenhum documento disponível</td>
                </tr>
              <?php endif; ?>
              </tbody>
            </table>
          </section>

          <?php
            $isPendente = strtoupper($sol->soli_status ?? '') === 'PENDENTE';
          ?>

          <!-- ===================== AÇÕES DA SOLICITAÇÃO ===================== -->
          <?php if ($isPendente): ?>
            <form method="post" action="<?= URL ?>/solicitacoes/decidir" class="soli-actions-form">
              <input type="hidden" name="soli_id" value="<?= (int)$sol->soli_id ?>">

              <div class="soli-actions">
                <button type="submit" name="acao" value="aceitar" class="btn-soli btn-aceitar">
                  ACEITAR
                </button>

                <button type="button" id="btn-recusar-toggle" class="btn-soli btn-recusar">
                  RECUSAR
                </button>
              </div>

              <div class="soli-block soli-block-motivo" id="motivo-form" style="display: none;">
                <label for="motivo" class="soli-block-title">Motivo da recusa</label>
                <textarea id="motivo" name="motivo_recusa"
                          placeholder="Descreva o motivo da recusa."><?= htmlspecialchars($sol->soli_motivo_recusa ?? '') ?></textarea>

                <div class="soli-actions" style="margin-top: 15px;">
                  <button type="submit" name="acao" value="recusar" class="btn-soli btn-recusar">
                    CONFIRMAR RECUSA
                  </button>
                  <button type="button" id="btn-cancelar-recusa" class="btn-soli" style="background-color: #999;">
                    CANCELAR
                  </button>
                </div>
              </div>
            </form>
          <?php else: ?>
            <div class="soli-block" style="padding: 20px; background-color: #f0f0f0; border-radius: 8px; text-align: center;">
              <p style="margin: 0; font-weight: bold; color: #333;">
                Status: <span style="color: #28a745;">✓ <?= strtoupper($sol->soli_status) ?></span>
              </p>
              <?php if (!empty($sol->soli_motivo_recusa) && strtoupper($sol->soli_status) === 'RECUSADA'): ?>
                <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">
                  <strong>Motivo:</strong> <?= htmlspecialchars($sol->soli_motivo_recusa) ?>
                </p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- ====================== SCRIPTS LOCAIS ====================== -->
          <script>
            const btnRecusarToggle = document.getElementById('btn-recusar-toggle');
            const motivoForm = document.getElementById('motivo-form');
            const btnCancelarRecusa = document.getElementById('btn-cancelar-recusa');

            if (btnRecusarToggle) {
              btnRecusarToggle.addEventListener('click', function() {
                motivoForm.style.display = 'block';
                btnRecusarToggle.style.display = 'none';
              });
            }

            if (btnCancelarRecusa) {
              btnCancelarRecusa.addEventListener('click', function() {
                motivoForm.style.display = 'none';
                btnRecusarToggle.style.display = 'block';
              });
            }

            const btnRecusarContratoToggle = document.getElementById('btn-recusar-contrato-toggle');
            const recusarContratoForm = document.getElementById('recusar-contrato-form');
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

        </div><!-- /.soli-card -->
      </div><!-- /.soli-container -->
    </section>
  </main>
</section>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$tipoUsuario = !empty($_SESSION['user_tipo']) ? (int)$_SESSION['user_tipo'] : 0;

// Sugestão de variáveis que o controller manda:
// - para root/admin: $usuario (objeto)
// - para aluno:      $aluno (objeto) e $docs (array docu_tipo => url)

// foto de perfil que vai aparecer no topo
$fotoPerfil = null;
if (!empty($usuario->usua_foto)) {
  $fotoPerfil = $usuario->usua_foto;
} elseif (!empty($aluno->usua_foto)) {
  $fotoPerfil = $aluno->usua_foto;
}
?>
<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?=URL?>/public/css/perfil.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar">
    <div class="profile">
      <?php
        // foto do menu lateral
        $userPhoto = null;
        if (!empty($_SESSION['user_id'])) {
          require_once dirname(__DIR__,2) . '/Libraries/Database.php';
          try {
            $db = new Database();
            $db->query('SELECT usua_foto FROM usuario WHERE usua_id = :id LIMIT 1');
            $db->bind(':id', $_SESSION['user_id']);
            $r = $db->resultado();
            if ($r && !empty($r->usua_foto)) $userPhoto = $r->usua_foto;
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
        <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Usuário' ?>
      </strong>
    </div>

    <?php if (!empty($_SESSION['user_tipo'])): ?>
      <nav class="menu">
        <!-- ROOT -->
        <?php if ($tipoUsuario === 1): ?>
          <a href="<?=URL?>/paginas/index_app" class="item">
            <i class="bi bi-buildings-fill"></i>
            <span>Empresas</span>
          </a>
          <a href="<?=URL?>/paginas/perfil" class="item active">
            <i class="bi bi-person-badge-fill"></i>
            <span>Perfil</span>
          </a>

        <!-- ADMIN -->
        <?php elseif ($tipoUsuario === 2): ?>
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
          <a href="<?=URL?>/solicitacoes/index" class="item">
            <i class="bi bi-list-check"></i>
            <span>Solicitações</span>
          </a>
          <a href="<?=URL?>/paginas/perfil" class="item active">
            <i class="bi bi-person-badge-fill"></i>
            <span>Perfil</span>
          </a>

        <!-- ALUNO -->
        <?php else: ?>
          <a href="<?=URL?>/paginas/index_app" class="item">
            <i class="bi bi-house-door-fill"></i>
            <span>Início</span>
          </a>
          <a href="<?=URL?>/paginas/forum" class="item">
            <i class="bi bi-chat-dots-fill"></i>
            <span>Fórum</span>
          </a>
          <a href="<?=URL?>/paginas/perfil" class="item active">
            <i class="bi bi-person-badge-fill"></i>
            <span>Perfil</span>
          </a>
          <a href="<?=URL?>/paginas/contrato" class="item">
            <i class="bi bi-file-earmark-text-fill"></i>
            <span>Contrato</span>
          </a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </aside>

  <!-- ===== CONTEÚDO ===== -->
  <main class="content">
    <div class="perfil-wrapper">

      <!-- HEADER PERFIL: foto + nome + tipo + form de foto -->
      <header class="perfil-header">
        <div class="perfil-avatar">
          <?php if ($fotoPerfil): ?>
            <img src="<?= URL ?>/<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de perfil">
          <?php else: ?>
            <i class="bi bi-person-fill"></i>
          <?php endif; ?>
        </div>

        <div class="perfil-header-main">
          <div class="perfil-header-text">
            <h1 class="perfil-name">
              <?php
                if ($tipoUsuario === 3 && !empty($aluno->usua_nome)) {
                  echo htmlspecialchars($aluno->usua_nome);
                } elseif (!empty($usuario->usua_nome)) {
                  echo htmlspecialchars($usuario->usua_nome);
                } else {
                  echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Meu perfil';
                }
              ?>
            </h1>
            <div class="perfil-chips">
              <span class="chip chip-role">
                <i class="bi bi-person-badge-fill"></i>
                <?= $tipoUsuario === 1 ? 'Root'
                  : ($tipoUsuario === 2 ? 'Administrador' : 'Aluno') ?>
              </span>
            </div>
          </div>

          <!-- Form pra atualizar foto -->
          <form class="foto-form"
                action="<?=URL?>/usuarios/atualizarFoto"
                method="post"
                enctype="multipart/form-data">
            <label class="btn-foto">
              <i class="bi bi-camera-fill"></i>
              <span>Trocar foto</span>
              <input id="usua_foto_input" type="file" name="usua_foto" accept="image/*" hidden>
            </label>
            <small class="foto-hint">PNG ou JPG, até 2 MB.</small>
            <div class="foto-actions" style="margin-top:8px;">
              <span id="foto_filename" style="margin-right:8px;color:#444"></span>
              <button id="confirm_foto_btn" type="button" class="btn btn-primary" style="display:none;margin-right:6px">Confirmar</button>
              <button id="cancel_foto_btn" type="button" class="btn btn-secondary" style="display:none">Cancelar</button>
            </div>
          </form>
        </div>
      </header>

      <?php
        // normaliza objetos
        $u   = isset($usuario) ? $usuario : (object)[];
        $alu = isset($aluno)   ? $aluno   : (object)[];
      ?>

      <?php
        // Mensagens simples de feedback via query string
        $msgSuccess = $_GET['success'] ?? '';
        $msgError = $_GET['error'] ?? '';
        if ($msgSuccess):
          $label = '';
          switch ($msgSuccess) {
            case 'foto_atualizada': $label = 'Foto atualizada com sucesso!'; break;
            case 'senha_atualizada': $label = 'Senha alterada com sucesso!'; break;
            default: $label = htmlspecialchars($msgSuccess); break;
          }
      ?>
        <div class="alert alert-success"><?= $label ?></div>
      <?php endif; ?>
      <?php if ($msgError):
          $code = $msgError;
          $text = '';
          switch ($code) {
            case 'senha_invalida': $text = 'Nova senha inválida ou não confere.'; break;
            case 'senha_atual_incorreta': $text = 'Senha atual incorreta.'; break;
            case 'salvar_falha': $text = 'Falha ao salvar. Tente novamente.'; break;
            default: $text = htmlspecialchars($code); break;
          }
      ?>
        <div class="alert alert-danger"><?= $text ?></div>
      <?php endif; ?>

      <!-- DADOS BÁSICOS -->
      <section class="perfil-section">
        <h2 class="perfil-section-title">Dados básicos</h2>

        <div class="perfil-grid">
          <div class="perfil-field perfil-field-full">
            <label>Nome completo</label>
            <input type="text"
                   value="<?= htmlspecialchars($alu->usua_nome ?? $u->usua_nome ?? ($_SESSION['user_name'] ?? '')) ?>"
                   readonly>
          </div>

          <div class="perfil-field perfil-field-full">
            <label>E-mail</label>
            <input type="text"
                   value="<?= htmlspecialchars($alu->usua_email ?? $u->usua_email ?? ($_SESSION['user_email'] ?? '')) ?>"
                   readonly>
          </div>

          <?php if ($tipoUsuario === 3): ?>
            <div class="perfil-field">
              <label>Curso</label>
              <input type="text" value="<?= htmlspecialchars($alu->usua_curso ?? '') ?>" readonly>
            </div>

            <div class="perfil-field">
              <label>Turma</label>
              <input type="text" value="<?= htmlspecialchars($alu->usua_turma ?? '') ?>" readonly>
            </div>

            <div class="perfil-field perfil-field-full">
              <label>Escola</label>
              <input type="text" value="<?= htmlspecialchars($alu->usua_escola ?? '') ?>" readonly>
            </div>

            <div class="perfil-field">
              <label>Turno</label>
              <input type="text" value="<?= htmlspecialchars($alu->usua_turno ?? '') ?>" readonly>
            </div>

            <div class="perfil-field perfil-field-full">
              <label>Endereço</label>
              <input type="text" value="<?= htmlspecialchars($alu->usua_endereco ?? '') ?>" readonly>
            </div>

            <!-- CPF removido para alunos conforme solicitado -->
          <?php elseif ($tipoUsuario === 1): ?>
            <!-- ROOT: mostrar apenas nome e e-mail (campos já acima) -->
          <?php else: ?>
            <div class="perfil-field">
              <label>Telefone</label>
              <input type="text" value="<?= htmlspecialchars($u->usua_telefone ?? '') ?>" readonly>
            </div>

            <div class="perfil-field">
              <label>Empresa</label>
              <input type="text" value="<?= htmlspecialchars($u->empr_nome ?? '') ?>" readonly>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- DOCUMENTAÇÃO – só aluno -->
      <?php if ($tipoUsuario === 3): ?>
        <?php
          $docs = isset($docs) && is_array($docs) ? $docs : [];
        ?>
        <section class="perfil-section">
          <h2 class="perfil-section-title">Minha documentação</h2>

          <table class="perfil-doc-table">
            <tbody>
            <?php
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
                  <?php
                    $ext = pathinfo($docs[$tipo], PATHINFO_EXTENSION);
                    $downloadName = htmlspecialchars($label) . ((!empty($ext) && $ext !== 'pdf') ? '.' . $ext : '.pdf');
                  ?>
                  <a href="<?= htmlspecialchars($url) ?>" download="<?= $downloadName ?>">Baixar</a>
                </td>
              </tr>
            <?php
                endif;
              endforeach;
              if (!$temDoc):
            ?>
              <tr>
                <td colspan="3" class="doc-label">Nenhum documento disponível.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </section>
      <?php endif; ?>

      <!-- ALTERAR SENHA – todos -->
      <section class="perfil-section">
        <h2 class="perfil-section-title">Alterar senha</h2>

        <form class="senha-form" action="<?=URL?>/usuarios/alterarSenha" method="post">
          <div class="perfil-grid">
            <div class="perfil-field perfil-field-full">
              <label for="senha_atual">Senha atual</label>
              <input type="password" id="senha_atual" name="senha_atual" required>
            </div>

            <div class="perfil-field">
              <label for="nova_senha">Nova senha</label>
              <input type="password" id="nova_senha" name="nova_senha" required>
            </div>

            <div class="perfil-field">
              <label for="confirma_senha">Confirmar nova senha</label>
              <input type="password" id="confirma_senha" name="confirma_senha" required>
            </div>
          </div>

          <p class="senha-hint">
            A senha deve ter pelo menos 8 caracteres. Evite usar a mesma senha de outros sistemas.
          </p>

          <div class="senha-actions">
            <button type="submit" class="btn-primary">
              <i class="bi bi-key-fill"></i>
              Salvar nova senha
            </button>
          </div>
        </form>
      </section>

    </div>
  </main>
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var input = document.getElementById('usua_foto_input');
  var confirmBtn = document.getElementById('confirm_foto_btn');
  var cancelBtn = document.getElementById('cancel_foto_btn');
  var filenameSpan = document.getElementById('foto_filename');
  if (!input || !confirmBtn || !cancelBtn) return;

  input.addEventListener('change', function(){
    if (!this.files || this.files.length === 0) {
      filenameSpan.textContent = '';
      confirmBtn.style.display = 'none';
      cancelBtn.style.display = 'none';
      return;
    }
    var fileName = this.files[0].name || '';
    filenameSpan.textContent = fileName;
    confirmBtn.style.display = '';
    cancelBtn.style.display = '';
  });

  confirmBtn.addEventListener('click', function(){
    var form = input.closest('form');
    if (form) form.submit();
  });

  cancelBtn.addEventListener('click', function(){
    try { input.value = ''; } catch (e) { }
    filenameSpan.textContent = '';
    confirmBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
  });
});
</script>

<head>
  <link rel="stylesheet" href="<?=URL?>/public/css/menu_lateral.css">
  <link rel="stylesheet" href="<?=URL?>/public/css/viagens_admin.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<section class="app-layout">
  <aside class="sidebar">
    <div class="profile">
      <?php
        if (session_status() === PHP_SESSION_NONE) session_start();
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
      <strong class="user-name"><?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?></strong>
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
        <a href="<?=URL?>/viagens/minhas" class="item active">
          <i class="bi bi-clock-fill"></i>
          <span>Horários</span>
        </a>
        <a href="#" class="item">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
        <a href="<?=URL?>/solicitacoes/index" class="item">
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


  <main class="content">
    <header class="page-head">
      <h1>Novo horário</h1>
    </header>

    <section class="viagem-form-card">
      <form class="viagem-form" action="<?=URL?>/viagens/salvar" method="post">
        <?php if (!empty($hori_id)): ?>
          <input type="hidden" name="hori_id" value="<?= (int)$hori_id ?>">
        <?php endif; ?>

        <div class="form-row">
          <label for="hori_titulo">Título do horário <span class="required">*</span></label>
          <input type="text" id="hori_titulo" name="hori_titulo"
                 value="<?= htmlspecialchars($dados['hori_titulo'] ?? '') ?>"
                 placeholder="Ex.: Linha IFRO / Centro" required>
        </div>

        <div class="form-row-inline">
          <div class="form-row">
            <label for="hori_turno">Turno <span class="required">*</span></label>
            <select id="hori_turno" name="hori_turno" required>
              <?php
                $turno = $dados['hori_turno'] ?? 'MANHA';
              ?>
              <option value="MANHA" <?= $turno === 'MANHA' ? 'selected' : '' ?>>Manhã</option>
              <option value="TARDE" <?= $turno === 'TARDE' ? 'selected' : '' ?>>Tarde</option>
              <option value="NOITE" <?= $turno === 'NOITE' ? 'selected' : '' ?>>Noite</option>
            </select>
          </div>

          <div class="form-row">
            <label for="hori_hora_ida">Saída <span class="required">*</span></label>
            <input type="time" id="hori_hora_ida" name="hori_hora_ida"
                   value="<?= htmlspecialchars($dados['hori_hora_ida'] ?? '') ?>" required>
          </div>

          <div class="form-row">
            <label for="hori_hora_volta">Volta</label>
            <input type="time" id="hori_hora_volta" name="hori_hora_volta"
                   value="<?= htmlspecialchars($dados['hori_hora_volta'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row">
          <label for="onib_id">Ônibus (opcional)</label>
          <select id="onib_id" name="onib_id">
            <option value="">Nenhum</option>
            <?php if (!empty($onibusList) && is_array($onibusList)): ?>
              <?php foreach ($onibusList as $o): ?>
                <option value="<?= (int)$o->onib_id ?>" <?= (isset($dados['onib_id']) && intval($dados['onib_id']) === intval($o->onib_id)) ? 'selected' : '' ?>>
                  <?= htmlspecialchars(trim(($o->onib_modelo ?? '') . (!empty($o->onib_placa) ? ' — '.$o->onib_placa : ''))) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="form-row-inline">
          <div class="form-row">
            <label for="hori_ponto">Ponto / Descrição</label>
            <input type="text" id="hori_ponto" name="hori_ponto"
                   value="<?= htmlspecialchars($dados['hori_ponto'] ?? '') ?>"
                   placeholder="Ex.: Saída do IFRO, em frente ao portão principal">
          </div>

          <div class="form-row">
            <label for="hori_dias">Dias da semana</label>
            <input type="text" id="hori_dias" name="hori_dias"
                   value="<?= htmlspecialchars($dados['hori_dias'] ?? '') ?>"
                   placeholder="Ex.: Seg a Sex">
          </div>
        </div>

        <div class="form-row">
          <label for="hori_situacao">Situação</label>
          <?php $sit = $dados['hori_situacao'] ?? 'ATIVO'; ?>
          <select id="hori_situacao" name="hori_situacao">
            <option value="ATIVO"   <?= $sit === 'ATIVO'   ? 'selected' : '' ?>>Ativo</option>
            <option value="INATIVO" <?= $sit === 'INATIVO' ? 'selected' : '' ?>>Inativo</option>
          </select>
        </div>

        <div class="form-actions">
          <a href="<?=URL?>/viagens/minhas" class="btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
          </a>
          <button type="submit" class="btn-primary">
            <i class="bi bi-check-lg"></i> Salvar horário
          </button>
        </div>
      </form>
    </section>
  </main>
</section>
<head>
  <link rel="stylesheet" href="<?= URL ?>/public/css/index_app.css">
  <link rel="stylesheet" href="<?= URL ?>/public/css/menu_lateral.css">
</head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
      <!-- ======== ROOT ======== -->
      <?php if ($tipoUsuario === 1): ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-buildings-fill"></i>
          <span>Empresas</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>

      <!-- ======== ADMIN ======== -->
      <?php elseif ($tipoUsuario === 2): ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
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
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>

      <!-- ======== ALUNO ======== -->
      <?php else: ?>
        <a href="<?=URL?>/paginas/index_app" class="item active">
          <i class="bi bi-house-door-fill"></i>
          <span>Início</span>
        </a>
        <a href="<?=URL?>/paginas/forum" class="item">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Fórum</span>
        </a>
        <a href="<?=URL?>/paginas/perfil" class="item">
          <i class="bi bi-person-badge-fill"></i>
          <span>Perfil</span>
        </a>
        <a href="<?=URL?>/paginas/contrato" class="item">
          <i class="bi bi-file-earmark-text-fill"></i><span>Contrato</span>
        </a>

      <?php endif; ?>
    </nav>
  <?php endif; ?>

  </aside>

  <main class="content">
  <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

  <?php if (!empty($_SESSION['admin_cadastrado'])): ?>
    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
      Administrador cadastrado com sucesso!
    </div>
    <?php unset($_SESSION['admin_cadastrado']); ?>
  <?php endif; ?>

  <header class="page-head">
    <h1>👋 Bem-vindo, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Fulano' ?>!</h1>
  </header>

  <?php
    // garante variável para a view
    $empresas = isset($empresas) && is_array($empresas) ? $empresas : [];
    $alunos = isset($alunos) && is_array($alunos) ? $alunos : [];
  ?>

  <?php if (!empty($_SESSION['user_tipo'])): ?>
    <?php $tipoUsuario = intval($_SESSION['user_tipo']); ?>

    <?php if ($tipoUsuario === 1): ?>
      <!-- =======================
           ÁREA DO ROOT (Tipo 1)
           ======================= -->
      <section class="root-section">
        <div class="root-head">
          <h2>Empresas cadastradas</h2>
        </div>

        <!-- Barra de busca + Cadastrar empresa -->
        <form class="company-search" method="get" action="<?=URL?>/paginas/index_app">
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input type="text" name="q" value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
                   placeholder="Pesquisar por nome da empresa, CNPJ ou razão social...">
          </div>

          <div class="action-wrap">
            <button class="btn-outline" type="submit">Buscar</button>
            <a class="btn-primary" href="<?=URL?>/paginas/cadastro_empresa">
              <i class="bi bi-building-add"></i> Cadastrar empresa
            </a>
          </div>
        </form>

        <!-- Grid de empresas -->
        <!-- Grid de empresas -->
        <div class="company-grid">
          <?php if (empty($empresas)): ?>
            <div class="company-empty">
              <i class="bi bi-inboxes"></i>
              <p>Nenhuma empresa encontrada.</p>
              <!-- (sem botão extra aqui) -->
            </div>
          <?php else: ?>
            <?php foreach ($empresas as $e): ?>
              <a class="company-card link-card" href="<?=URL?>/paginas/detalhe_empresa?id=<?= urlencode($e['id'] ?? '') ?>">
                <div class="company-logo">
                  <?php if (!empty($e['logo'])): ?>
                    <img src="<?= URL.'/'.$e['logo'] ?>" alt="Logo da empresa <?= htmlspecialchars($e['nome'] ?? '') ?>">
                  <?php else: ?>
                    <div class="no-logo"><i class="bi bi-buildings"></i></div>
                  <?php endif; ?>
                </div>

                <div class="company-body">
                  <h3 class="company-name"><?= htmlspecialchars($e['nome'] ?? '—') ?></h3>
                  <div class="company-meta">
                    <span><i class="bi bi-file-earmark-text"></i> CNPJ: <?= 
                      preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', 
                        preg_replace('/\D/', '', $e['cnpj'] ?? '')) 
                    ?></span>
                    <span><i class="bi bi-card-text"></i> Razão: <?= htmlspecialchars($e['razao'] ?? '—') ?></span>
                  </div>

                  <div class="company-footer">
                    <span class="badge">
                      <i class="bi bi-people-fill"></i>
                      <?= intval($e['admins'] ?? 0) ?> admin<?= (intval($e['admins'] ?? 0) === 1 ? '' : 's') ?>
                    </span>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </section>

        <?php elseif ($tipoUsuario === 2): ?>
      <!-- =======================
           ÁREA DO ADMIN (Tipo 2)
           Lista de alunos da empresa
           ======================= -->
      <section class="root-section">
        <div class="root-head">
          <h2>Alunos da empresa</h2>
        </div>

        <!-- Barra de busca de alunos -->
        <form class="company-search" method="get" action="<?=URL?>/paginas/index_app">
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input
              type="text"
              name="q"
              value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
              placeholder="Pesquisar por nome, curso ou escola..."
            >
          </div>

          <div class="action-wrap">
            <button class="btn-outline" type="submit">Buscar</button>
          </div>
        </form>

        <!-- Grid de alunos (reaproveitando layout de empresas) -->
        <div class="company-grid">
          <?php if (empty($alunos)): ?>
            <div class="company-empty">
              <i class="bi bi-people"></i>
              <p>Nenhum aluno encontrado para esta empresa.</p>
            </div>
          <?php else: ?>
            <?php foreach ($alunos as $a): ?>
              <a
                class="company-card link-card"
                href="<?=URL?>/paginas/detalhe_aluno?id=<?= urlencode($a['id']) ?>"
              >
                <div class="company-logo">
                  <?php if (!empty($a['foto'])): ?>
                    <img src="<?= URL . '/' . htmlspecialchars($a['foto']) ?>"
                         alt="Foto de <?= htmlspecialchars($a['nome']) ?>">
                  <?php else: ?>
                    <div class="no-logo">
                      <i class="bi bi-person-video3"></i>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="company-body">
                  <h3 class="company-name">
                    <?= htmlspecialchars($a['nome'] ?? '—') ?>
                  </h3>

                  <div class="company-meta">
                    <?php if (!empty($a['curso'])): ?>
                      <span>
                        <i class="bi bi-mortarboard-fill"></i>
                        Curso: <?= htmlspecialchars($a['curso']) ?>
                      </span>
                    <?php endif; ?>

                    <?php if (!empty($a['escola'])): ?>
                      <span>
                        <i class="bi bi-building"></i>
                        Escola: <?= htmlspecialchars($a['escola']) ?>
                      </span>
                    <?php endif; ?>

                    <?php if (!empty($a['turno'])): ?>
                      <span>
                        <i class="bi bi-clock-fill"></i>
                        Turno: <?= htmlspecialchars($a['turno']) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>


        <?php else: ?>
      <!-- =======================
           ÁREA DO ALUNO (Tipo 3)
           ======================= -->
      <section class="student-section">

        <!-- ===== AVISOS ===== -->
        <section class="student-panel">
          <header class="student-panel-head">
            <h2>Avisos</h2>
          </header>

          <?php if (empty($avisos)): ?>
            <div class="avisos-empty">
              <i class="bi bi-inbox"></i>
              <p>Nenhum aviso disponível.</p>
            </div>
          <?php else: ?>
            <div class="avisos-list">
              <?php foreach ($avisos as $aviso): ?>
                <article class="aviso-item">
                  <header class="aviso-item-head">
                    <h3 class="aviso-title">
                      <?= htmlspecialchars($aviso->avis_titulo ?: 'Aviso') ?>
                    </h3>
                    <span class="aviso-admin">
                      <?= htmlspecialchars($aviso->admin_nome ?? $aviso->criador_nome ?? 'Admin') ?>
                    </span>
                  </header>

                  <p class="aviso-body">
                    <?php
                      $t = $aviso->avis_texto ?? '';
                      echo htmlspecialchars(strlen($t) > 180 ? substr($t,0,180).'…' : $t);
                    ?>
                  </p>

                  <footer class="aviso-meta">
                    <i class="bi bi-clock"></i>
                    <span>
                      <?= date('d/m/Y H:i', strtotime($aviso->avis_publica_em ?? $aviso->avis_dth_criacao)) ?>
                    </span>
                  </footer>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <!-- ===== HORÁRIOS ===== -->
                <!-- ===== HORÁRIOS ===== -->
        <section class="student-panel">
          <header class="student-panel-head">
            <h2>Horários</h2>
          </header>

          <?php
            // vem do controller quando user_tipo === 3
            $horariosAluno = isset($horariosAluno) && is_array($horariosAluno) ? $horariosAluno : [];
          ?>

          <?php if (empty($horariosAluno)): ?>
            <div class="avisos-empty">
              <i class="bi bi-bus-front"></i>
              <p>Nenhum horário cadastrado para sua empresa.</p>
            </div>
          <?php else: ?>
            <div class="student-bus-list">
              <?php foreach ($horariosAluno as $h): ?>
                <article class="student-bus-card">
                  <header class="student-bus-head">
                    <div class="student-bus-img">
                      <img
                        src="<?= URL ?>/<?= htmlspecialchars($h['onib_foto'] ?? 'public/img/onibus-escolar.png') ?>"
                        alt="Ônibus"
                      >
                    </div>

                    <div class="student-bus-main">
                      <h3>
                        <?= htmlspecialchars($h['titulo'] ?? ($h['turno'] ?? 'Linha')) ?>
                      </h3>

                      <p class="student-bus-meta">
                        <?php if (!empty($h['turno'])): ?>
                          <span><?= htmlspecialchars($h['turno']) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($h['onib_modelo']) || !empty($h['onib_placa'])): ?>
                          <span>
                            <?= htmlspecialchars($h['onib_modelo'] ?? '') ?>
                            <?php if (!empty($h['onib_placa'])): ?>
                              — Placa <?= htmlspecialchars($h['onib_placa']) ?>
                            <?php endif; ?>
                          </span>
                        <?php endif; ?>
                      </p>
                    </div>
                  </header>

                  <p class="student-bus-info">
                    <i class="bi bi-clock"></i>
                    Saída: <?= substr($h['hora_ida'], 0, 5) ?>
                    <?php if (!empty($h['hora_volta'])): ?>
                      • Volta: <?= substr($h['hora_volta'], 0, 5) ?>
                    <?php endif; ?>
                  </p>

                  <?php if (!empty($h['ponto'])): ?>
                    <p class="student-bus-extra">
                      <i class="bi bi-geo-alt"></i>
                      <?= htmlspecialchars($h['ponto']) ?>
                    </p>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>


      </section>
    <?php endif; ?>
  <?php endif; ?>

</main>
</section>

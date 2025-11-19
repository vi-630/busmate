<head>
    <link rel="stylesheet" href="<?= URL ?>/public/css/tokens.css">
    <link rel="stylesheet" href="<?= URL ?>/public/css/header.css">
</head>
<header class="bg-dark">
    <div class="container">
        <nav class="navbar navbar-expand-sm navbar-dark position-relative">
            <img src="<?=URL?>/public/img/logo.png" alt="Logo BusMate">            
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
                        <?php $homeUrl = isset($_SESSION['user_id']) ? URL . '/paginas/index_app' : URL; ?>
                        <a class="nav-link" href="<?= $homeUrl ?>" data-tooltip="tooltip" title="Página Inicial">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>/paginas/sobre" data-tooltip="tooltip" title="Sobre nós">Sobre nós</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>/paginas/contato" data-tooltip="tooltip" title="Contato">Contato</a>
                    </li>
                    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>/usuarios/logout" data-tooltip="tooltip" title="Sair">Sair</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>/paginas/entrar" data-tooltip="tooltip" title="Entrar">Entrar</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </div>
</header>
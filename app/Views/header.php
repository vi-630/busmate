<header class="bg-dark">
    <div class="container">
        <nav class="navbar navbar-expand-sm navbar-dark position-relative">
            <a class="navbar-brand" href="<?=URL?>">LOGO</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Menu alinhado à esquerda -->
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>" data-tooltip="tooltip" title="Página Inicial">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?=URL?>/paginas/sobre" data-tooltip="tooltip" title="Sobre nós">Sobre nós</a>
                    </li>
                </ul>
            </div>

            <?php if(isset($_SESSION['usuario_id'])): ?>
                <span class="navbar-text">
                    <p>Olá, <?= $_SESSION['usuario_nome'] ?>, seja bem-vindo(a)!</p>
                    <a class="btn btn-sm btn-danger" href="<?=URL?>/usuarios/sair" data-tooltip="tooltip" title="Sair do sistema">Sair</a>
                </span>
                <?php else: ?>

            <!-- Botões alinhados à direita da tela -->
            <div class="position-absolute" style="right: 0;">
                <a class="btn btn-info mr-3" 
                href="<?=URL?>/usuarios/cadastrar" data-tooltip="tooltip" title="Não tem uma conta? Cadastre-se">Cadastre-se</a>
                <a class="btn btn-info" href="<?=URL?>/usuarios/login" data-tooltip="tooltip" title="Tem uma conta? Faça login">Entrar</a>
            </div>
            <?php endif; ?>
        </nav>
    </div>
</header>
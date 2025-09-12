<?php
include '../app/Libraries/Rota.php';
include '../app/Libraries/Controllers.php';
include '../app/configuracao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=APP_NOME?></title>
    <link rel="stylesheet" href="<?= URL ?>/public/css/estilo.css">
    <script src="<?URL?>/public/"></script>
</head>
<body>
   <?php
   include '../app/Views/header.php';
   $rotas = new Rota();
   include '../app/Views/footer.php';
   //$rotas->url();
   ?>
</body>
</html>
<?php
ob_start(); // <<--- ADICIONE AQUI
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
    <link rel="shortcut icon" type="image/x-icon" href="<?=URL?>/public/img/logo_simples.png">
    <link rel="stylesheet" href="<?=URL?>/public/css/estilo.css">
</head>
<body>
   <?php
   include '../app/Views/header.php';
   $rotas = new Rota();
   include '../app/Views/footer.php';
   ?>
</body>
</html>
<?php
ob_end_flush(); // <<--- E AQUI
?>
<?php

function formatarNome($nome) {
    // Garante UTF-8 e remove espaços extras
    $nome = trim(mb_strtolower($nome, 'UTF-8'));

    // Coloca a primeira letra de cada palavra maiúscula
    $nome = mb_convert_case($nome, MB_CASE_TITLE, 'UTF-8');

    // Corrige preposições (opcional, deixa visual mais natural)
    $substituir = [
        ' Da ' => ' da ', ' De ' => ' de ', ' Do ' => ' do ',
        ' Das ' => ' das ', ' Dos ' => ' dos ', ' E ' => ' e '
    ];
    return strtr($nome, $substituir);
}


define('APP', dirname(__FILE__));
define('URL', 'http://localhost/busmate');
define('APP_NOME', 'BusMate');
const APP_VERSAO = '1.0.0';
?>
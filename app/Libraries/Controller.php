<?php

/**
 * Controlador base
 * Carrega os modelos e views
 */
class Controller {
    /**
     * Carrega o modelo
     */
    public function model($model) {
        require_once '../app/Models/' . $model . '.php';
        return new $model();
    }

    /**
     * Carrega a view
     */
    public function view($view, $dados = []) {
        $arquivo = '../app/Views/' . $view . '.php';
        if(file_exists($arquivo)){
            require_once $arquivo;
        } else {
            die('View não existe');
        }
    }
}

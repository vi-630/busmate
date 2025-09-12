<?php
class Controllers{
    public function model($model){
        require_once '../app/Models/'.$model.'.php';
        return new $model;
    }//fim da funcão model

    public function view($view, $dados = []){
        $arquivo = ('../app/Views/'.$view.'.php');
        
        if(file_exists($arquivo)){
            require_once $arquivo;
        }else{
            die("O arquivo não existe kkk");
        }// fim do if
    }// fim do método view
}//fim da classe Controllers
?>
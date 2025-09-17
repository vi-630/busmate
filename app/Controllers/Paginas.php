<?php
class Paginas extends Controllers{
    public function sobre(){
        $this->view('paginas/sobre');    
    }//fim do método sobre
    public function index(){
        $this->view('paginas/home');
    }//fim da fução index
    public function contato(){
        $this->view('paginas/contato');
    }
    public function entrar(){
        $this->view('paginas/entrar');
    }
    public function cadastrar(){
        $this->view('paginas/cadastrar');
    }
}//fim da classe Paginas
?>
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
    public function index_app(){
        $this->view('paginas/index_app');
    }
    public function perfil(){
        $this->view('paginas/perfil');
    }
    public function forum(){
        $this->view('paginas/forum');
    }
    public function manha(){
        $this->view('paginas/manha');
    }
    public function tarde(){
        $this->view('paginas/tarde');
    }
}//fim da classe Paginas
?>
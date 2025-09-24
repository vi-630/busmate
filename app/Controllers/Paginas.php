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
    public function contrato(){
        $this->view('paginas/contrato');
    }
    public function termo_uso(){
        $this->view('paginas/termo_uso');
    }
    public function politica_privacidade(){
        $this->view('paginas/politica_privacidade');
    }
    public function central_ajuda(){
        $this->view('paginas/central_ajuda');
    }
}//fim da classe Paginas
?>
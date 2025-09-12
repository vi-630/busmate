<?php
class Paginas extends Controllers{
    public function sobre(){
        $dados = ['titulo'=>'Pagina Sobre nós', 'descricao'=>'Somos a maior empresa de tecnologia do Brasil', 'contato'=>'Estamos localizados na rua flores, jardim das maravilhas'];
        $this->view('paginas/sobre', $dados);    
    }//fim do método sobre

    public function index(){
        $dados = ['titulo'=>'Pagina Inicial', 'descricao'=>'Aula de PHP Orientada a Objetos com MVC.'];
        $this->view('paginas/home', $dados);
    }//fim da fução index

    public function contato(){
        
    }
}//fim da classe Paginas
?>
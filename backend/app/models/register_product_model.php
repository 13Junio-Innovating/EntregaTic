<?php
#[AllowDynamicProperties] #php 8.2
class RegisterProductModel
{
    private $id;
    private $imagem_produto;
    private $nome_produto;
    private $codigo_produto;
    private $fornecedor;
    private $descricao_produto;
    private $quantidade_produto;
    private $preco_unitario_produto;
    private $preco_venda_produto;
    private $preco_total_em_produto;
    private $data_criacao;
    private $data_modificacao;
    private $id_doc_controletic;
    private $tipo_produto;
    private $so_produto;
    private $memoria_produto;
    private $processador_produto;
    private $email_produto;



    public function __get($attribute)
    {
        return $this->$attribute;
    }

    public function __set($attribute, $value)
    {
        $this->$attribute = $value;
    }
}

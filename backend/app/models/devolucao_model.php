<?php
#[AllowDynamicProperties]
class DevolucaoModel
{
    private $id;
    private $venda_id;
    private $numero_da_venda;
    private $produto_id;
    private $quantidade;
    private $motivo;
    private $data_devolucao;
    private $assinatura_devolucao;
    private $login_devolucao;

    public function __get($attribute)
    {
        return $this->$attribute;
    }

    public function __set($attribute, $value)
    {
        $this->$attribute = $value;
    }
}
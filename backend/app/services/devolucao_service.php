<?php
class DevolucaoService
{
    private $connect;
    private $devolucao;

    public function __construct(DbConnection $connect, DevolucaoModel $devolucao)
    {
        $this->connect = $connect->getConnection();
        $this->devolucao = $devolucao;
    }

    public function getVendaData()
    {
        $query = "SELECT numero_da_venda, produto FROM tb_vendas WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':id', $this->devolucao->__get('venda_id'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getEstoqueProduto()
    {
        $query = "SELECT quantidade_produto FROM tb_produtos WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':id', $this->devolucao->__get('produto_id'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function atualizarEstoque()
    {
        $query = "UPDATE tb_produtos SET quantidade_produto = :qtd WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':qtd', $this->devolucao->__get('quantidade'));
        $stmt->bindValue(':id', $this->devolucao->__get('produto_id'));
        $stmt->execute();
    }

    public function registrarDevolucao()
    {
        $query = "INSERT INTO tb_devolucoes (venda_id, numero_da_venda, produto_id, quantidade, motivo, data_devolucao, assinatura_devolucao, login_devolucao)
                  VALUES (:venda_id, :numero_da_venda, :produto_id, :quantidade, :motivo, NOW(), :assinatura_devolucao, :login_devolucao)";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':venda_id', $this->devolucao->__get('venda_id'));
        $stmt->bindValue(':numero_da_venda', $this->devolucao->__get('numero_da_venda'));
        $stmt->bindValue(':produto_id', $this->devolucao->__get('produto_id'));
        $stmt->bindValue(':quantidade', $this->devolucao->__get('quantidade'));
        $stmt->bindValue(':motivo', $this->devolucao->__get('motivo'));
        $stmt->bindValue(':assinatura_devolucao', $this->devolucao->__get('assinatura_devolucao'));
        $stmt->bindValue(':login_devolucao', $this->devolucao->__get('login_devolucao'));
        $stmt->execute();
        return $stmt->rowCount();
    }
}
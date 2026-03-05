<?php
class RegisterProductService
{
    private $connect;
    private $product;

    public function __construct(DbConnection $connect, RegisterProductModel $product)
    {
        $this->connect = $connect->getConnection();
        $this->product = $product;
    }

    public function searchProductByCode()
    {
        $query = "SELECT codigo_produto FROM tb_produtos WHERE codigo_produto = :codigo_produto";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function registerProduct()
    {
        $query = "INSERT INTO tb_produtos (nome_produto, codigo_produto, fornecedor, descricao_produto, quantidade_produto, preco_unitario_produto, preco_venda_produto, preco_total_em_produto, imagem_produto, id_doc_controletic, tipo_produto, so_produto, memoria_produto, processador_produto, imei_produto, storage_produto, email_produto) VALUES (:nome_produto, :codigo_produto, :fornecedor, :descricao_produto, :quantidade_produto, :preco_unitario_produto, :preco_unitario_produto, :preco_total_em_produto, :imagem_produto, :id_doc_controletic, :tipo_produto, :so_produto, :memoria_produto, :cpu_produto, :imei_produto, :storage_produto, :email_produto)";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':nome_produto', $this->product->__get('nome_produto'));
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->bindValue(':fornecedor', $this->product->__get('fornecedor'));
        $stmt->bindValue(':descricao_produto', $this->product->__get('descricao_produto'));
        $stmt->bindValue(':quantidade_produto', $this->product->__get('quantidade_produto'));
        $stmt->bindValue(':preco_unitario_produto', $this->product->__get('preco_unitario_produto'));
        $stmt->bindValue(':preco_venda_produto', $this->product->__get('preco_venda_produto'));
        $stmt->bindValue(':preco_total_em_produto', $this->product->__get('preco_total_em_produto'));
        $stmt->bindValue(':imagem_produto', $this->product->__get('imagem_produto'));
        $stmt->bindValue(':id_doc_controletic', $this->product->__get('id_doc_controletic'));
        $stmt->bindValue(':tipo_produto', $this->product->__get('tipo_produto'));
        $stmt->bindValue(':so_produto', $this->product->__get('so_produto'));
        $stmt->bindValue(':cpu_produto', $this->product->__get('cpu_produto'));
        $stmt->bindValue(':memoria_produto', $this->product->__get('memoria_produto'));
        $stmt->bindValue(':imei_produto', $this->product->__get('imei_produto'));
        $stmt->bindValue(':storage_produto', $this->product->__get('storage_produto'));
        $stmt->bindValue(':email_produto', $this->product->__get('email_produto'));

        $stmt->execute();
        return $stmt->rowCount();
    }

    public function searchProduct()
    {
        $query = "SELECT * FROM tb_produtos WHERE nome_produto LIKE :valueSearch OR codigo_produto LIKE :valueSearch OR id_doc_controletic LIKE :valueSearch  ORDER BY nome_produto ASC";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':valueSearch', '%' . $this->product->__get('valueSearch') . '%');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function deleteProduct()
    {
        $query = "DELETE FROM tb_produtos WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':id', $this->product->__get('id'));
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function searchProductByCodeUpdate()
    {
        $query = "SELECT codigo_produto FROM tb_produtos WHERE codigo_produto = :codigo_produto AND id != :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->bindValue(':id', $this->product->__get('id'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function searchProductByIDControleTIC()
    {
        $query = "SELECT id_doc_controletic FROM tb_produtos WHERE id_doc_controletic = :id_doc_controletic AND id != :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':id_doc_controletic', $this->product->__get('id_doc_controletic'));
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->bindValue(':id', $this->product->__get('id'));
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function updateProductWithouImage()
    {
        $query = "UPDATE tb_produtos SET nome_produto = :nome_produto, codigo_produto = :codigo_produto, fornecedor = :fornecedor, descricao_produto = :descricao_produto, quantidade_produto = :quantidade_produto, preco_unitario_produto = :preco_unitario_produto, preco_venda_produto = :preco_unitario_produto, preco_total_em_produto = :preco_total_em_produto, id_doc_controletic = :id_doc_controletic, tipo_produto = :tipo_produto, so_produto = :so_produto, memoria_produto = :memoria_produto, processador_produto = :cpu_produto, imei_produto = :imei_produto, storage_produto = :storage_produto, email_produto = :email_produto WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':nome_produto', $this->product->__get('nome_produto'));
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->bindValue(':fornecedor', $this->product->__get('fornecedor'));
        $stmt->bindValue(':descricao_produto', $this->product->__get('descricao_produto'));
        $stmt->bindValue(':quantidade_produto', $this->product->__get('quantidade_produto'));
        $stmt->bindValue(':preco_unitario_produto', $this->product->__get('preco_unitario_produto'));
        $stmt->bindValue(':preco_venda_produto', $this->product->__get('preco_venda_produto'));
        $stmt->bindValue(':preco_total_em_produto', $this->product->__get('preco_total_em_produto'));
        $stmt->bindValue(':id_doc_controletic', $this->product->__get('id_doc_controletic'));
        $stmt->bindValue(':tipo_produto', $this->product->__get('tipo_produto'));
        $stmt->bindValue(':so_produto', $this->product->__get('so_produto'));
        $stmt->bindValue(':cpu_produto', $this->product->__get('cpu_produto'));
        $stmt->bindValue(':memoria_produto', $this->product->__get('memoria_produto'));
        $stmt->bindValue(':imei_produto', $this->product->__get('imei_produto'));
        $stmt->bindValue(':storage_produto', $this->product->__get('storage_produto'));
        $stmt->bindValue(':email_produto', $this->product->__get('email_produto'));
        $stmt->bindValue(':id', $this->product->__get('id'));
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function updateProduct()
    {
        $query = "UPDATE tb_produtos SET nome_produto = :nome_produto, codigo_produto = :codigo_produto, fornecedor = :fornecedor, descricao_produto = :descricao_produto, quantidade_produto = :quantidade_produto, preco_unitario_produto = :preco_unitario_produto, preco_venda_produto = :preco_unitario_produto, preco_total_em_produto = :preco_total_em_produto, imagem_produto = :imagem_produto, id_doc_controletic = :id_doc_controletic, tipo_produto = :tipo_produto, so_produto = :so_produto, memoria_produto = :memoria_produto, processador_produto = :cpu_produto, email_produto = :email_produto WHERE id = :id";
        $stmt = $this->connect->prepare($query);
        $stmt->bindValue(':nome_produto', $this->product->__get('nome_produto'));
        $stmt->bindValue(':codigo_produto', $this->product->__get('codigo_produto'));
        $stmt->bindValue(':fornecedor', $this->product->__get('fornecedor'));
        $stmt->bindValue(':descricao_produto', $this->product->__get('descricao_produto'));
        $stmt->bindValue(':quantidade_produto', $this->product->__get('quantidade_produto'));
        $stmt->bindValue(':preco_unitario_produto', $this->product->__get('preco_unitario_produto'));
        $stmt->bindValue(':preco_venda_produto', $this->product->__get('preco_venda_produto'));
        $stmt->bindValue(':preco_total_em_produto', $this->product->__get('preco_total_em_produto'));
        $stmt->bindValue(':imagem_produto', $this->product->__get('imagem_produto'));
        $stmt->bindValue(':id_doc_controletic', $this->product->__get('id_doc_controletic'));
        $stmt->bindValue(':tipo_produto', $this->product->__get('tipo_produto'));
        $stmt->bindValue(':so_produto', $this->product->__get('so_produto'));
        $stmt->bindValue(':cpu_produto', $this->product->__get('cpu_produto'));
        $stmt->bindValue(':memoria_produto', $this->product->__get('memoria_produto'));
        $stmt->bindValue(':imei_produto', $this->product->__get('imei_produto'));
        $stmt->bindValue(':storage_produto', $this->product->__get('storage_produto'));
        $stmt->bindValue(':email_produto', $this->product->__get('email_produto'));
        $stmt->bindValue(':id', $this->product->__get('id'));
        $stmt->execute();
        return $stmt->rowCount();
    }
}

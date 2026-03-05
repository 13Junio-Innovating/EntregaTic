<?php require "../layouts/session.php";
require_once '../controllers/db_connection.php';
$connect = new DbConnection();
$connect = $connect->getConnection();

#carregar os clientes
$client_sql = "SELECT * FROM tb_clientes ORDER BY nome ASC";
$stmt = $connect->prepare($client_sql);
$stmt->execute();
$result_client = $stmt->fetchAll(PDO::FETCH_OBJ);

#carregar os produtos
$product_sql = "SELECT * FROM tb_produtos ORDER BY nome_produto ASC";
$stmt = $connect->prepare($product_sql);
$stmt->execute();
$result_product = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require '../layouts/head.php' ?>
    <link rel="stylesheet" href="../css/sales.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/pt-BR.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>

<body>

<main>
    <section class="section-product-list">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="product-list">
                        <select id="select_client" class="form-select" required>
                            <option value="">Selecione um Colaborador</option>
                            <?php foreach ($result_client as $client) : ?>
                                <option value="<?= $client->id ?>"><?= $client->nome ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="product-list">
                        <select id="select_product" class="form-select" required>
                            <option value="">Selecione um disposiivo</option>
                            <?php foreach ($result_product as $product) : ?>
                                <option value="<?= $product->id ?>" 
                                        data-id="<?= $product->id ?>" 
                                        data-price="<?= $product->preco_venda_produto ?>" 
                                        data-img="<?= $product->imagem_produto ?>" 
                                        data-qnt="<?= $product->quantidade_produto ?>">
                                    <?= "$product->nome_produto - $product->codigo_produto - $product->descricao_produto" ?> 
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container">
            <div class="row">
                <!-- Formulário Produto -->
                <div class="col-md-6">
                    <div class="container container_product">
                        <div class="row">
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="number" class="form-control" id="product_quantity" placeholder="Quantidade" value="1">
                                    <label for="product_quantity">Quantidade</label>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="unit_price" placeholder="Valor Unitário" disabled value="R$00,00">
                                    <label for="unit_price">Valor Unitário</label>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="patrimony" placeholder="Nº Patrimônio" maxlength="6">
                                    <label for="patrimony">Patrimônio</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="total_price" placeholder="Valor Total" disabled value="R$00,00">
                                    <label for="total_price">Valor Total</label>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="itsm_ticket" placeholder="Ticket ITSM" maxlength="11">
                                    <label for="itsm_ticket">ITSM</label>
                                </div>
                            </div>
                            <div class="col-md-4 mt-3">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="hostname" placeholder="Nome do Dispositivo" maxlength="14">
                                    <label for="hostname">Hostname</label>
                                </div>
                            </div>
                            <center><div class="col-md-12 mt-3">
                                <div class="col-md-12 mt-3">
                                    <button class="btn btn-success btn-lg" id="add_product" onclick="addProductTable()" disabled>Adicionar Produto</button>
                                </div>
                            </div></center>
                        </div>
                        <div class="container-product-preview">
                            <div>
                                <img id="preview_img" src="../assets/img/avatar/shopping-cart.webp" alt="" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cupom -->
                <div class="col-md-6">
                    <div class="container container-cupom">
                        <div class="cupom">
                            <div class="table-responsive">
                                <table class="table table-hover" id="table_product">
                                    <thead>
                                        <tr>
                                            <th>COD/SERIAL</th>
                                            <th>PRODUTO</th>
                                            <th>VLR. UN.</th>
                                            <th>QTD</th>
                                            <th>PATRIMÔNIO</th>
                                            <th>ITSM</th>
                                            <th>HOSTNAME</th>
                                            <th>VLR. TOTAL</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="subtotal mt-5">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="subtotal" placeholder="Sub-total" disabled value="R$00,00">
                                <label for="subtotal">Sub-total</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="container-operation">
                        <button class="btn btn-lg btn-primary" onclick="closeSale()" id="close_sale" disabled>Fechar Solicitação</button>
                        <button class="btn btn-lg btn-danger" onclick="cancelSale()">Cancelar Solicitação</button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal Assinatura -->
    <div class="modal fade" id="modal-close-order" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="container">
                    <div class="modal-title">
                        <button type="button" class="btn-close btn-close-white" onclick="backToSale()" aria-label="Close"></button>
                    </div>
                    <div class="row">
                        <form action="#" method="post" id="formCloseOrder">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="action" value="register_sale">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="total_sale" readonly disabled placeholder="Total da Entrega">
                                <label for="total_sale">Total da Entrega</label>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header">
                                    Assinatura do Colaborador
                                </div>
                                <div class="card-body text-center">
                                    <canvas id="signature-pad" style="border: 1px solid #ccc; width: 100%; height: 200px;"></canvas>
                                    

                                    <div class="mt-2">
                                        <button type="button" class="btn btn-danger" onclick="clearSignature()">Limpar</button>
                                    </div>
                                    <input type="hidden" id="signature-data" name="signature-data">
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-primary btn-lg" id="closeSaleModal">Fechar Entrega</button>
                                <button type="button" class="btn btn-secondary btn-lg" onclick="backToSale()">Voltar para Entrega</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../js/_component/validation.js"></script>
<script src="../js/_component/mask.js"></script>
<script src="../js/sales.js"></script>

</body>
</html>

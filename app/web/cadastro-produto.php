<?php require "../layouts/session.php";
require_once '../controllers/db_connection.php';
$connect = new DbConnection();
$connect = $connect->getConnection();
$query_supplier = "SELECT * FROM tb_fornecedores ORDER BY nome_fantasia";
$stmt = $connect->prepare($query_supplier);
$stmt->execute();
$result_supplier = $stmt->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require '../layouts/head.php' ?>
    <link rel="stylesheet" href="../css/register.css">

</head>

<body>
    <?php require '../layouts/menu.php' ?>
    <main>
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-md-12 center">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="container-box">
                                        <div class="col-md-6">
                                            <div class="col-md-4 card-box ">
                                                <a href="#" onclick="display('modal-cad-product')">
                                                    <i class="fa-solid fa-user-plus"></i>
                                                    Novo Produto</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="col-md-4 card-box">
                                                <a href="#" onclick="display('modal-search-product')">
                                                    <i class="fa-solid fa-magnifying-glass"></i>
                                                    Pesquisar Produto</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="modal fade bd-example-modal-lg show" data-bs-backdrop="static" data-bs-keyboard="false" id="modal-cad-product" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="container">
                        <div class="modal-title">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row">
                            <form method="post" id="formCadProduct" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" tabindex="-1">
                                <input type="hidden" name="action" value="add_product" tabindex="-1">
                                <div class="row">
                                    <div class="container-img col-md-12">
                                        <span id="delete-img-preview" title="Remover imagem"></span>
                                        <img id="preview-img" src="../assets/img/products/produto-sem-imagem.webp" width="200" height="200" alt="Avatar" class="img-fluid">
                                        <label for="img-product">Cadastre a imagem do seu produto</label>
                                        <input id="img-product" type="file" name="img-product" class="input-file">
                                        <span id="file-name"></span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating col-md-6">
                                        <input type="text" class="form-control" id="product-name" name="product-name" placeholder="Nome do Produto" required>
                                        <label for="product-name" class="required-field-label">Nome do Produto</label>
                                    </div>

                                    <div class="col-md-4 form-floating">
                                        <input type="text" class="form-control" id="product-code" name="product-code" placeholder="Código do Produto" required>
                                        <label for="product-code" class="required-field-label">Cód/Serial do Produto</label>
                                    </div>

                                    <div class="col-md-2 d-flex align-items-center">
                                        <button type="button" class="btn btn-primary w-80" onclick="gerarCodigo()">Gerar</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <select id="product-supplier" name="product-supplier" class="form-select form-control" title="Fornecedor do Produto" required>
                                            <option selected value="">Selecionar Fornecedor</option>
                                            <?php
                                            foreach ($result_supplier as $supplier) {
                                                echo "<option value='$supplier->id'>$supplier->nome_fantasia</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating col-md-6">
                                        <input type="text" class="form-control" id="product-description" name="product-description" placeholder="Descrição do Produto" required>
                                        <label for="product-description" class="required-field-label">Modelo do Produto</label>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <select id="product-type" name="product-type" class="form-select form-control" title="Tipo" required>
                                            <option selected value="">Sel. Tipo</option>
                                            <option value="Notebook">Notebook</option>
                                            <option value="Desktop">Desktop</option>
                                            <option value="MiniPC">MiniPC</option>
                                            <option value="Tablet">Tablet</option>
                                            <option value="Celular">Celular</option>
                                            <option value="Periferico">Periférico</option>
                                            <option value="Licenca">Licença</option>
                                            <option value="Firewall">Firewall</option>
                                            <option value="Switch">Switch</option>
                                            <option value="AP">Access Point</option>
                                            <option value="Monitor">Monitor</option>
                                        </select>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-imei" name="product-imei" placeholder="imei" disabled>
                                        <label for="product-imei" class="required-field-label">IMEI</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-storage" name="product-storage" placeholder="storage" disabled>
                                        <label for="product-storage" class="required-field-label">Armazenamento</label>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-so" name="product-so" placeholder="S.O." disabled>
                                        <label for="product-so" class="required-field-label">S.O.</label>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-cpu" name="product-cpu" placeholder="Processador" disabled>
                                        <label for="product-cpu" class="required-field-label">Processador</label>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-memory" name="product-memory" placeholder="Memória" disabled>
                                        <label for="product-memory" class="required-field-label">Memória</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating col-md-6">
                                        <input type="email" class="form-control" id="product-email" name="product-email" placeholder="Email Licença" disabled>
                                        <label for="product-email" class="required-field-label">Email Licença</label>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="number" class="form-control number_only" id="product-qnt" name="product-qnt" onfocus="previewSumPriceTotal()" onchange="previewSumPriceTotal()" placeholder="Quantidade do Produto" required>
                                        <label for="product-qnt" class="required-field-label">Qtde</label>
                                    </div>
                                    <div class="form-floating col-md-3">
                                        <input type="text" class="form-control" id="product-unit-price" name="product-unit-price" onblur="previewSumPriceTotal()" onchange="previewSumPriceTotal()" placeholder="Preço Unitário do Produto" required>
                                        <label for="product-unit-price" class="required-field-label">Vlr Unit.</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-floating col-md-6">
                                        <input type="text" class="form-control" id="total-price-on-product" name="total-price-on-product" placeholder="Preço Total em Produto" required readonly>
                                        <label for="total-price-on-product">Preço Total em Produto</label>
                                    </div>
                                    <div class="form-floating col-md-6">
                                        <input type="text" class="form-control" id="id-controle-tic" name="id-controle-tic" placeholder="ID Documentos Controle TIC" required>
                                        <label for="product-unit-price" class="required-field-label">ID Documentos Controle TIC</label>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-primary" id="btnSend">Salvar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-example-modal-lg show" data-bs-backdrop="static" data-bs-keyboard="false" id="modal-search-product" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="container">
                        <div class="modal-title">
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="row">
                            <form action="#" method="post" id="formSearchProduct">
                                <input type="hidden" name="csrf_token_search" value="<?= $_SESSION['csrf_token'] ?>" tabindex="-1">
                                <input type="hidden" name="action_search" value="search_product" tabindex="-1">
                                <div id="container-search">
                                    <label for="search-product" class="label-search">
                                        <input type="search" name="search-product" id="search-product" class="form-control" placeholder="Pesquise pelo nome ou código do produto" required>
                                        <div class="icon-search">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </label>
                                </div>
                            </form>
                            <div class="table-responsive d-none">
                                <table class="table table-hover mt-4" id="result-search">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <?php
    // retorno de mensagem vinda do backend
    
    if (!empty($_GET) && !isset($_GET['error']) || !isset($_GET['success'])) {
        if (isset($_GET['error'])) {
            $message = $_GET['message'];
            echo "<script>
            Swal.fire({
                            icon: 'error',
                            text: '$message',
                            allowOutsideClick: false
              });

        </script>";
        }

        if (isset($_GET['success'])) {
            $message = $_GET['message'];
            echo "<script>        
              Swal.fire({
                            icon: 'success',
                            text: '$message',
                            allowOutsideClick: false
              });
        </script>";
        }
    }
    ?>
</body>

<script src="../js/_component/validation.js"></script>
<script src="../js/_component/mask.js"></script>
<script src="../js/register.js"></script>

</html>
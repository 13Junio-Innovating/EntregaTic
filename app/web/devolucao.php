<?php
require "../layouts/session.php";
require_once '../controllers/db_connection.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$connect = new DbConnection();
$connect = $connect->getConnection();

// Consulta vendas com nome do cliente
$vendas_sql = "
SELECT DISTINCT 
    v.numero_da_venda, 
    c.nome AS cliente_nome
FROM 
    tb_vendas v
INNER JOIN 
    tb_clientes c ON v.cliente = c.id
WHERE 
    EXISTS (
        SELECT 1
        FROM tb_vendas v2
        LEFT JOIN tb_devolucoes d ON v2.id = d.venda_id
        WHERE v2.numero_da_venda = v.numero_da_venda
        AND d.venda_id IS NULL
    )
ORDER BY 
    v.numero_da_venda DESC;

";
$stmt = $connect->prepare($vendas_sql);
$stmt->execute();
$result_vendas = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php require '../layouts/head.php' ?>
    <link rel="stylesheet" href="../css/devolucao.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/pt-BR.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    
    <script>
        window.csrfToken = '<?= $csrf_token ?>';
    </script>
</head>

<body>
<main>
    <section class="section-product-list">
        <div class="container">
            <center><h2>Devolução de Dispositivos</h2></center>
            <div class="row">
                <div class="col-md-12">
                    <div class="product-list">
                        <select id="select_venda" class="form-select" required>
                            <option value="">Selecione o Número do Termo de Entrega</option>
                            <?php foreach ($result_vendas as $venda) : ?>
                                <option value="<?= $venda->numero_da_venda ?>">
                                    Termo de Entrega nº <?= $venda->numero_da_venda ?> - <?= $venda->cliente_nome ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="device-list">
                        <div class="table-responsive">
                            <table class="table table-hover" id="table_return">
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
                                        <th>DEVOLVER</th>
                                        <th>MOTIVO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="10">Selecione uma entrega realizada para visualizar os dispositivos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="container-operation">
                        <button class="btn btn-lg btn-primary" id="btn_register_return" disabled>
                            Confirmar Devolução
                        </button>
                        <button class="btn btn-lg btn-danger" onclick="cancelReturn()">Cancelar Devolução</button>
                    </div>
                </div>
            </div>
        </div>
    </footer>

<!-- Modal de Confirmação da Devolução -->
<div class="modal fade" id="modal-confirm-return" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="container">
                <div class="modal-title">
                    <button type="button" class="btn-close btn-close-white" onclick="backToDevolution()" aria-label="Close"></button>
                </div>
                <div class="row">
                    <form method="post" id="formConfirmReturn">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" name="action" value="registrar_devolucao">
                        <input type="hidden" name="assinatura_devolucao" id="assinatura_devolucao">

                        <div class="card mt-3">
                            <div class="card-header">
                                Assinatura do Colaborador
                            </div>
                            <div class="card-body text-center">
                                <canvas id="canvas_assinatura_devolucao" style="border: 1px solid #ccc; width: 100%; height: 200px;"></canvas>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-danger" onclick="clearSignatureReturn()">Limpar</button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Confirmar Devolução</button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="backToDevolution()">Voltar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


</main>

<script src="../js/devolucao.js"></script>
</body>
</html>

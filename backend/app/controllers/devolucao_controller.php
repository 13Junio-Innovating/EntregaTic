<?php
session_start();
require __DIR__ . '/../database/db_connection.php';
require __DIR__ . '/../models/devolucao_model.php';
require __DIR__ . '/../services/devolucao_service.php';

$connect = new DbConnection();
$conn = $connect->getConnection();
$model_devolucao = new DevolucaoModel();
$devolucao_service = new DevolucaoService($connect, $model_devolucao);

function redirect($msg)
{
    echo json_encode($msg);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['action']) && $input['action'] === 'get_sale_products') {
    $venda_id = $input['venda_id'];

    $query = "SELECT 
                v.id AS id,
                v.produto AS produto_id,
                p.nome_produto AS nome,
                p.codigo_produto AS codigo,
                v.valor_unitario,
                v.subtotal AS valor_total,
                v.quantidade,
                v.patrimonio,
                v.itsm_ticket,
                v.hostname
            FROM tb_vendas v
            INNER JOIN tb_produtos p ON v.produto = p.id
            WHERE v.numero_da_venda = :numero_da_venda
            AND v.id NOT IN (SELECT venda_id FROM tb_devolucoes)";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':numero_da_venda', $venda_id);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "products" => $produtos]);
    exit;
}

if (!$input) {
    redirect(["error" => "decode", "message" => "Erro ao decodificar JSON"]);
}

$csrf_token = $input['csrf_token'];
$action = $input['action'];

if (!isset($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
    redirect(["error" => "csrf", "message" => "Token CSRF inválido"]);
}

    if ($action === "registrar_devolucao") {
        $venda_id = $input['venda_id'];
        $produtos = $input['produtos'];
        $assinatura_devolucao = $input['assinatura_devolucao'] ?? '';
        $assinatura_devolucao_path = '';
        $login_devolucao = $_SESSION['access_user'];

        if (!empty($assinatura_devolucao)) {
        $assinatura_devolucao = str_replace('data:image/png;base64,', '', $assinatura_devolucao);
        $assinatura_devolucao = str_replace(' ', '+', $assinatura_devolucao);
        $fileData = base64_decode($assinatura_devolucao);

        $assinatura_devolucao_dir = '/var/www/html/entregatic/app/assets/img/assinaturas_devolucao/';

        if (!file_exists($assinatura_devolucao_dir)) {
            mkdir($assinatura_devolucao_dir, 0777, true);
        }

        $signature_filename = 'assinatura_devolucao_' . time() . '_' . rand(1000, 9999) . '.png';
        $file_path = $assinatura_devolucao_dir . $signature_filename;

        if (file_put_contents($file_path, $fileData)) {
            $assinatura_devolucao_path = 'assets/img/assinaturas_devolucao/' . $signature_filename; // Caminho relativo para salvar no banco
        }
    }

    $return = [];

    foreach ($produtos as $item) {
        $model_devolucao->__set("venda_id", $item['id']);
        $model_devolucao->__set("motivo", $item['motivo']);
        

        $venda_data = $devolucao_service->getVendaData();

        if (!$venda_data) continue;

        $model_devolucao->__set("numero_da_venda", $venda_data->numero_da_venda);
        $model_devolucao->__set("produto_id", $venda_data->produto);

        $estoque = $devolucao_service->getEstoqueProduto();
        $nova_qtd = $estoque->quantidade_produto + 1;

        $model_devolucao->__set("quantidade", $nova_qtd);
        $devolucao_service->atualizarEstoque();

        $model_devolucao->__set("quantidade", 1);

        $model_devolucao->__set("assinatura_devolucao", $signature_filename);
        $model_devolucao->__set("login_devolucao", $login_devolucao);



        $return[] = $devolucao_service->registrarDevolucao();
    }

    if (in_array(0, $return)) {
        redirect(["error" => "fail", "message" => "Erro ao registrar alguma devolução"]);
    } else {
        redirect(["success" => true, "message" => "Devolução registrada com sucesso"]);
    }
} else {
    redirect(["error" => "invalid", "message" => "Ação inválida"]);
}
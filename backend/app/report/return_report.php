<?php
require_once __DIR__ . "/../middleware/proteger.php";
session_start();

require __DIR__ . "/../database/db_connection.php";
require __DIR__ . "/../utils/mpdf/vendor/autoload.php";
require __DIR__ . '/../utils/qrcode/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

$connect = new DbConnection();
$connect = $connect->getConnection();

function redirect($msg)
{
    echo json_encode($msg);
    exit();
}

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    redirect(array("error" => "erro1", "message" => "Erro de autenticação."));
    exit();
} else {
    ob_start();

    $action = $_GET['action'];
    if ($action === "print_return") {
        $return_id = $_GET['id'];

        $query = "SELECT d.*, c.nome, c.cpf, p.nome_produto, p.codigo_produto
                  FROM tb_devolucoes d
                  JOIN tb_vendas v ON d.venda_id = v.id
                  JOIN tb_clientes c ON v.cliente = c.id
                  JOIN tb_produtos p ON d.produto_id = p.id
                  WHERE d.numero_da_venda = :return_id";

        $stmt = $connect->prepare($query);
        $stmt->bindValue(':return_id', $return_id);
        $stmt->execute();
        $results_returns = $stmt->fetchAll(PDO::FETCH_OBJ);

        $sale = $results_returns[0];
        $mpdf = new \Mpdf\Mpdf(
            [
                'mode' => 'utf-8',
                //'orientation' => 'L',
                'format' => 'Legal',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 10,
                'setAutoTopMargin' => 'stretch',
                'setAutoBottomMargin' => 'stretch',
                'default_font_size' => 10,
                'default_font' => 'sans-serif',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'autoVietnamese' => true,
            ]
        );

        $html = '
        <style>
        table { font-size:10px; }
        .table, .table th, .table td { border: 1px solid black; border-collapse: collapse; }
        </style><br>

        <p style="text-align: justify;">
            Este Termo de Devolução é firmado entre o <strong>COSTÃO DO SANTINHO</strong>, e o colaborador
            <strong>' . $sale->nome . '</strong>, CPF <strong>' . $sale->cpf . '</strong>, para registrar a devolução dos dispositivos anteriormente cedidos para uso profissional.
        </p>

        <p style="text-align: justify;">
            Os dispositivos descritos na tabela abaixo foram devolvidos em perfeitas condições, conforme inspeção técnica e checklist interno, cumprindo os requisitos estabelecidos no Termo de Cessão assinado anteriormente.
        </p>

        <table class="table" width="100%" style="text-align:center;">
            <tr>
                <th>Dispositivo</th>
                <th>Quantidade</th>
                <th>Motivo Devolução</th>
                <th>Data da Devolução</th>
                <th>QrCode</th>
            </tr>';

        foreach ($results_returns as $ret) {

            //QRCODE PRODUTO

            $link_qrcode_produto = "http://entregatic.costao.com.br/report/product_report.php?action=print_product_report&id=$ret->codigo_produto";

            $qrCode_produto = new QrCode($link_qrcode_produto);
            $qrCode_produto->setSize(200);
            $qrCode_produto->setMargin(10);
            $qrCode_produto->setEncoding('UTF-8');
            $qrCode_produto->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));

            $qrDataUri_produto = $qrCode_produto->writeDataUri();

            $html .= '<tr>
                <td>' . $ret->nome_produto . '</td>
                <td>' . $ret->quantidade . '</td>
                <td>' . $ret->motivo . '</td>
                <td>' . date('d/m/Y H:i:s', strtotime($ret->data_devolucao)) . '</td>
                <td><img src="' . $qrDataUri_produto . '" width="110" height="110"></td> 
            </tr>';
        }

        $html .= '</table><br>';

        $assinatura = $sale->assinatura_devolucao ?? '';
        $assinatura_path = '../assets/img/assinaturas_devolucao/' . $assinatura;

        $html .= '<div style="text-align:center; margin-top:30px;">
            <h4>Assinatura do Colaborador:</h4>';
        if (!empty($assinatura) && file_exists($assinatura_path)) {
            $html .= '<img src="' . $assinatura_path . '" style="width:250px; height:120px;">';
        } else {
            $html .= '<p><i>Assinatura não disponível.</i></p>';
        }
        $html .= '<div style="margin-top:20px;">__________________________________________<br>' . $sale->nome . '</div></div>';

        $link_qrcode = "http://entregatic.costao.com.br/report/return_report.php?action=print_return&id={$return_id}";
        $qrCode = new QrCode($link_qrcode);
        $qrCode->setSize(200);
        $qrDataUri = $qrCode->writeDataUri();

        $html .= '<div style="text-align: center; margin-top: 40px;">
            <h4>Escaneie para visualizar o termo online:</h4>
            <img src="' . $qrDataUri . '" width="160" height="160">
        </div>';

            $mpdf->SetHTMLHeader('
            <table width="100%" style="font-family: Arial; font-size: 10pt; border: 1px solid #000; border-collapse: collapse;">
                <tr>
                    <td rowspan="2" width="15%" style="border: 1px solid #000; text-align: center; vertical-align: top; padding: 5px;">
                        <table width="100%" style="border-collapse: collapse;">
                            <tr>
                                <td style="text-align: center;">
                                    <img src="/var/www/html/entregatic/app/assets/img/logo/logo_costao.png" width="170" height="60">
                                </td>
                            </tr>
                            <tr>
                                <td style="border-top: 1px solid #000; text-align: center; font-size: 8pt; padding-top: 4px;">
                                    <strong>Devolução Nº: </strong><br>' . $return_id . '
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td rowspan="2" width="55%" style="border: 1px solid #000; text-align: center;">
                        <div style="font-size: 11pt; font-weight: bold;">
                        TERMO DE DEVOLUÇÃO DE DISPOSITIVOS
                        </div>
                    </td>
                    <td width="15%" style="border: 1px solid #000; text-align: center;">
                        <strong>Emissão</strong><br>
                        ' . date('d/m/Y H:i:s', strtotime($sale->data_devolucao)) . '
                    </td>
                    <td width="15%" style="border: 1px solid #000; text-align: center;">
                        <strong>Classificação</strong><br>
                        Uso Interno
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; text-align: center;">
                        <strong>Versão</strong><br>
                        1.0
                    </td>
                    <td style="border: 1px solid #000; text-align: center;">
                        <strong>Registrado por:</strong><br>
                        ' . $sale->login_devolucao . '

                    </td>
                </tr>
            </table>
        ');



        $mpdf->setHTMLFooter('<table width="100%" style="font-size: 8pt"><tr><td width="33%">Gerado em: {DATE j/m/Y}</td><td width="33%" align="center">{PAGENO}/{nbpg}</td><td width="33%" align="right">Por: ' . $_SESSION['access_user'] . '</td></tr></table>');

        $mpdf->WriteHTML($html);
        $mpdf->Output("Termo_Devolucao_" . $return_id . " - " . $sale->nome . ".pdf", "D");
        exit();
    }
}
?>

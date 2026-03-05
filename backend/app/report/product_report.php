<?php

require_once __DIR__ . "/../middleware/proteger.php";

session_start();

require __DIR__ . "/../database/db_connection.php";
require __DIR__ . "/../utils/mpdf/vendor/autoload.php";
require __DIR__ . '/../utils/qrcode/vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\Response\QrCodeResponse;

$connect = new DbConnection();
$connect = $connect->getConnection();

function redirect($msg)
{
    echo json_encode($msg);
    exit();
}

//os parametros de action e id da venda viram via GET
//verificando se foram passados
if (!isset($_GET['action']) || !isset($_GET['id'])) {
    redirect(array("error" => "erro1", "message" => "Erro de autenticação."));
    exit();
} else {
    ob_start(); //inicia o buffer de saída

    $action = $_GET['action'];
    if ($action === "print_product_report") {
        $codigo_produto = $_GET['id'];

        //$query = "SELECT * FROM tb_vendas v JOIN tb_clientes c ON v.cliente = c.id JOIN tb_produtos p ON v.produto = p.id WHERE p.codigo_produto = :codigo_produto";
        $query = "SELECT v.*, c.*, p.*, f.nome_fantasia 
                    FROM tb_vendas v 
                    JOIN tb_clientes c ON v.cliente = c.id 
                    JOIN tb_produtos p ON v.produto = p.id 
                    JOIN tb_fornecedores f ON p.fornecedor = f.id 
                    WHERE p.codigo_produto = :codigo_produto
                ";

        $stmt = $connect->prepare($query);
        $stmt->bindValue(':codigo_produto', $codigo_produto);
        $stmt->execute();
        $results_sales = $stmt->fetchAll(PDO::FETCH_OBJ);

        $mpdf = new \Mpdf\Mpdf(
            [
                'mode' => 'utf-8',
                'orientation' => 'L',
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
        table{
            font-size:10px;
        }   

        .table,  .table th,  .table td{
            border: 1px solid black;
            border-collapse: collapse;
        }
        
        </style>
        ';


        $html .= '
        <br>';

                $html .= '    
                <div class="container">       
                <table class="table" width="100%" style="font-family: Arial; font-size: 10pt; text-align:center; border-collapse: collapse;">
                    <tr>                
                        <th>Dispositivo</th>
                        <th>Hostname</th>
                        <th>Valor Unitário</th>                     
                        <th>Patrimônio</th>
                        <th>ITSM</th>
                        <th>Modelo</th>
                        <th>Fornecedor</th>
                        <th>Controle TIC</th>
                        <th>Tipo</th>
                        <th>SO</th>
                        <th>Memória</th>
                        <th>CPU</th>
                        <th>Armazenamento</th>
                        <th>IMEI</th>

                    </tr>';

        foreach ($results_sales as $key => $sale) {
            $html .= '
            <tr>               
                <td>' . $sale->nome_produto . '</td>
                <td>' . $sale->hostname . '</td>
                <td>' . $sale->valor_unitario . '</td>
                <td>' . $sale->patrimonio . '</td> 
                <td>' . $sale->itsm_ticket . '</td>
                <td>' . $sale->descricao_produto . '</td>
                <td>' . $sale->nome_fantasia . '</td>
                <td>' . $sale->id_doc_controletic . '</td>
                <td>' . $sale->tipo_produto . '</td>
                <td>' . $sale->so_produto . '</td>
                <td>' . $sale->memoria_produto . '</td>
                <td>' . $sale->processador_produto . '</td>
                <td>' . $sale->storage_produto . '</td>
                <td>' . $sale->imei_produto . '</td>

            </tr>';
        }

        $html .= ' </table></div> <br>';



        $html .= '
            <div class="container">
            <h3>Atribuição do dispositivo: ' . $results_sales[0]->nome . '</h3>
            <h3>CPF: ' . $results_sales[0]->cpf . '</h3>
            <h6>Data de atribuição: Florianópolis, ' . date('d/m/Y H:i:s', strtotime($sale->data_criacao)) . '</h6>
            ';

        

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
                                <strong>Código do Dispositivo: </strong><br>
                                ' . $codigo_produto . '
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2" width="55%" style="border: 1px solid #000; text-align: center;">
                    <div style="font-size: 11pt; font-weight: bold;">
                    DETALHES DO DISPOSITIVO ATRIBUÍDO AO COLABORADOR
                    </div>
                </td>
                <td width="15%" style="border: 1px solid #000; text-align: center;">
                    <strong>Emissão</strong><br>
                    ' . date('d/m/Y H:i:s', strtotime($sale->data_criacao)) . '
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
                    <strong>Entregue por:</strong><br>
                    ' . $results_sales[0]->vendedor . '
                </td>
            </tr>
        </table>
        ');



        
        $html .=
            '<div class="container">
            <div>
            <h5>Login TIC que efetuou a entrega: ' . $results_sales[0]->vendedor . '</h5>
            </div>';

            $assinatura = $results_sales[0]->assinatura;
            //$path_assinatura = '/var/www/html/entregatic/app/assets/img/assinaturas/' . $assinatura;
            $url_assinatura = '../assets/img/assinaturas/' . $assinatura;

            $html .= '
            <div style="margin-top: 50px; text-align: center;">
                <h4>Assinatura do Colaborador:</h4>';

            if (!empty($assinatura) && file_exists($url_assinatura)) {
                $html .= '<img src="' . $url_assinatura . '" style="width: 250px; height: 120px;">';
            } else {
                $html .= '<p><i>Assinatura não disponível.</i></p>';
            }


            $html .= '
                <div style="margin-top:20px;">__________________________________________<br>' . $results_sales[0]->nome . '</div>
            </div>';

            //QRCODE

            $link_qrcode = "http://entregatic.costao.com.br/report/delivery_qrcode_report.php?action=print_report_qrcode&id={$sale_id}";

            $qrCode = new QrCode($link_qrcode);
            $qrCode->setSize(200);
            $qrCode->setMargin(10);
            $qrCode->setEncoding('UTF-8');
            $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));


            // converte para data URI para uso direto em HTML
            $qrDataUri = $qrCode->writeDataUri();



        $html .=
            '<div class="container">
                <div>
                    <div style="text-align: center; margin-top: 50px;">
                        <h4>Escaneie para acessar o termo de entrega online:</h4>
                        <img src="' . $qrDataUri . '" width="160" height="160">
                    </div>
                </div>
            </div>';


        // Define o rodapé no Mpdf
        $mpdf->setHTMLFooter('<table width="100%" style="font-size: 8pt"><tr><td width="33%">Gerado na data: {DATE j/m/Y}</td><td width="33%" align="center">{PAGENO}/{nbpg}</td><td width="33%" style="text-align: right;">PDF gerado por: ' . $_SESSION['access_user'] . '</td></tr></table>');

        $mpdf->WriteHTML($html);
        $mpdf->Output("Relatorio_dispositivo_" . $codigo_produto . ".pdf", "I");
        exit();
        ob_end_flush(); //limpa o buffer e envia a saida para o navegador
    }
}

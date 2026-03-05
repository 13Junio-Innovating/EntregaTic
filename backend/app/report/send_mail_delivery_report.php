<?php
//require_once __DIR__ . "/../middleware/proteger.php";

session_start();
require __DIR__ . "/../database/db_connection.php";
require __DIR__ . "/../utils/mpdf/vendor/autoload.php";
require __DIR__ . "/../utils/phpmailer/PHPMailer.php";
require __DIR__ . "/../utils/phpmailer/SMTP.php";
require __DIR__ . "/../utils/phpmailer/Exception.php";
require __DIR__ . '/../utils/qrcode/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

if (!isset($_GET['action']) || !isset($_GET['id'])) {
    redirect(array("error" => "erro1", "message" => "Erro de autenticação."));
    exit();
} else {
    ob_start();

    $action = $_GET['action'];
    if ($action === "send_mail") {
        $sale_id = $_GET['id'];

        $query = "SELECT v.*, c.nome, c.email, c.cpf, p.nome_produto, p.codigo_produto 
                  FROM tb_vendas v 
                  JOIN tb_clientes c ON v.cliente = c.id 
                  JOIN tb_produtos p ON v.produto = p.id 
                  WHERE v.numero_da_venda = :sale_id";
        $stmt = $connect->prepare($query);
        $stmt->bindValue(':sale_id', $sale_id);
        $stmt->execute();
        $results_sales = $stmt->fetchAll(PDO::FETCH_OBJ);

        if (!$results_sales) {
            redirect(array("error" => "erro2", "message" => "Entrega não encontrada."));
            exit();
        }

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
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
        ]);

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
        <br>
        
        <p style="text-align: justify;">O presente termo de cessão e posse de dispositivos fornecidos pela TIC, celebrado entre o COSTÃO DO SANTINHO TURISMO E LAZER S.A, CNPJ: 004.908.757/0001-39, 
        doravante denominado <strong>COSTÃO DO SANTINHO</strong>, e o colaborador (funcionário ou estagiário do COSTÃO DO SANTINHO ou prestador de serviço contratado pelo <strong>COSTÃO DO SANTINHO</strong>) 
        doravante denominado <strong>COLABORADOR </strong>determina as seguintes condições:<br></p>
        
        <ol>
            <li style="text-align: justify;">O <strong>COSTÃO DO SANTINHO</strong> concederá ao <strong>COLABORADOR '. $results_sales[0]->nome .'</strong>, 
            única e exclusivamente para o exercício das atividades profissionais, contratadas, o(s) dispositivo(s) indicado(s) na tabela do item 2 deste termo (denominados conjuntamente como "<strong>DISPOSITIVO(S)</strong>"), 
            os quais ficarão sob sua guarda e responsabilidade e cuja utilização reger-se-á pelas clausulas seguintes:</li><br>

            <li style="text-align: justify;">O <strong>COLABORADOR </strong>declara neste ato ter recebido do <strong>COSTÃO DO SANTINHO </strong>o(s) dispositivo(s) descritos abaixo:</li><br>';

                $html .= '    
                <div class="container">       
                <table class="table" width="100%" style="font-family: Arial; font-size: 10pt; text-align:center; border-collapse: collapse;">
                    <tr>                
                        <th>Dispositivo</th>
                        <th>Quantidade</th>
                        <th>Valor Unitário</th>                     
                        <th>Patrimônio</th>
                        <th>ITSM</th>
                        <th>Nome Dispositivo</th>
                    </tr>';

        foreach ($results_sales as $key => $sale) {
            $html .= '
            <tr>               
                <td>' . $sale->nome_produto . '</td>
                <td>' . $sale->quantidade . '</td>
                <td>' . $sale->valor_unitario . '</td>';

            $html .= ' 
                <td>' . $sale->patrimonio . '</td>  
                <td>' . $sale->itsm_ticket . '</td> 
                <td>' . $sale->hostname . '</td>          
            </tr>';
        }

        $html .= ' </table></div> <br>';

        $html .= '
            <li style="text-align: justify;">Compete única e exclusivamente ao <strong>COSTÃO DO SANTINHO </strong>a escolha do(s) dispositivos a ser(em) utilizado(s) pelo <strong>COLABORADOR, </strong>podendo, a seu critério e no momento que entender adequado, 
            efetuar a substituição de acordo com suas necessidades.</li><br>

            <li style="text-align: justify;"><strong>A guarda e conservação do(s) dispositivo(s) será(ão) de integral responsabilidade do COLABORADOR </strong>ficando autorizado o 
            <strong>COSTÃO DO SANTINHO a proceder o desconto em pagamentos futuros dos valores referentes a eventuais danos e/ou prejuízos causados por dolo ou culpa do COLABORADOR.</strong></li><br>

            <li style="text-align: justify;"><strong>O COLABORADOR </strong>fica, desde já, autorizado a proceder à retirada do(s) dispositivo(s) das dependências do <strong>COSTÃO DO SANTINHO</strong> 
            e se obriga a devolv&ecirc;-los em perfeito estado de uso e conserva&ccedil;&atilde;o caso o <strong>COSTÃO DO SANTINHO </strong>assim solicite ou se encerre a sua rela&ccedil;&atilde;o com o <strong>COSTÃO DO SANTINHO.</strong></li><br>
            
            <li style="text-align: justify;"><strong>O COLABORADOR </strong>compromete-se a:</li><br>

            <ol style="text-align: justify;">
                <li><strong>Zelar pela conservação do(s) dispositivos cedidos pelo COSTÃO DO SANTINHO, </strong>utilizando-o(s) adequadamente e obedecendo rigorosamente as Políticas e Normas de Segurança da informação do 
                <strong>COSTÃO DO SANTINHO, </strong>bem como as demais políticas e normas pertinentes;</li><br>

                <li>Comunicar a área de Tecnologia da Informação do <strong>COSTÃO DO SANTINHO, </strong> em um prazo de até 5 dias úteis, por meio de abertura de chamado, 
                caso haja qualquer ocorrência, problema, avaria ou defeito no(s) dispositivo(s);</li><br>

                <li>Seguir as Normas de segurança da informação que complementam a Política Geral de Segurança da Informação <strong>(PGSI)</strong>, definindo as diretrizes para o uso aceitável 
                de ativos de informação do COSTÃO DO SANTINHO TURISMO E LAZER por seus usuários autorizados;</li><br>
            </ol>

        <li style="text-align: justify;">É terminantemente proibido ao <strong>COLABORADOR:</strong></li><br>

            <ol style="text-align: justify;">
                <li>Alterar quaisquer configurações do(s) dispositivo(s) sem a expressa prévia concordância do <strong>COSTÃO DO SANTINHO, </strong>modificar e/ou instalar qualquer <em>hardware</em> ou <em>software</em> não autorizado;</li><br>

                <li>Realizar <em>root </em>e/ou <em>jailbreak</em> ou qualquer outro método que burle o sistema operacional para utilização própria. 
                Todos os dispositivos corporativos devem conter a conta do domínio <strong>COSTÃO DO SANTINHO </strong>como conta principal;</li><br>

                <li>Colar adesivos e/ou etiquetas personalizadas no(s) dispositivo(s);</li><br>

                <li>Remover etiquetas que fazem parte do controle do(s) dispositivo(s), como rótulo de identificação do equipamento, rótulo de patrimônio e tags de serviço;</li><br>

                <li>Emprestar o(s) dispositivo(s) ou utilizá-lo(s) para fins alheios aos interesses do <strong>COSTÃO DO SANTINHO.</strong></li><br>
            </ol>

            <li style="text-align: justify;">O <strong>COLABORADOR </strong>declara estar ciente e de acordo que o(s) dispositivo(s) pode(m) ser monitorado(s) pelo <strong>COSTÃO DO SANTINHO, </strong>
            assumindo a responsabilidade pela inobservância de quaisquer das obrigações/responsabilidades ora assumidas.</li><br>

            <li style="text-align: justify;">Em caso de sinistro de qualquer natureza com o(s) dispositivo(s), o <strong>COLABORADOR </strong>se obriga a proceder 
            à lavratura de Boletim de Ocorrência Policial imediatamente para o registro do fato e possível apuração seguindo 
            as normas de segurança da informação que complementam a Política Geral de Segurança da Informação <strong>(PGSI)</strong>, onde são definidas as diretrizes para 
            responder eventos ou incidentes de segurança que estejam impactando ou possam vir a impactar ativos/serviços de informação ou recursos computacionais do 
            <strong>COSTÃO DO SANTINHO TURISMO E LAZER</strong>.</li><br>

            <li style="text-align: justify;">Se comprovada culpa ou dolo do <strong>COLABORADOR </strong>no sinistro, este responderá por todas as obrigações jurídicas daí decorrentes, 
            além de arcar com os prejuízos causados ao(s) dispositivo(s) e/ou a terceiros, autorizando desde já o desconto dos valores respectivos em futuros pagamentos. 
            Caso o <strong>COLABORADOR </strong>seja prestador de serviços, os custos envolvidos no sinistro serão apresentados à empresa responsável pelo prestador, 
            para que a mesma tome as providências para efetuar a liquidação dos valores.</li><br>

            <li style="text-align: justify;">O<strong> COLABORADOR </strong>declara estar ciente de que o descumprimento dos procedimentos acima implicará em falta grave e poderá resultar na rescisão do contrato por justa causa.</li><br>
        </ol>';


        $html .= '
            <div class="container">
            <h3>Colaborador: ' . $results_sales[0]->nome . '</h3>
            <h3>CPF: ' . $results_sales[0]->cpf . '</h3>
            <h6>Florianópolis, ' . date('d/m/Y H:i:s', strtotime($sale->data_criacao)) . '</h6>
            ';



        $html .= '           
                <table class="table" width="100%" style="font-family: Arial; font-size: 10pt; text-align:center; border-collapse: collapse;">
                    <tr>                
                        <th>Dispositivo</th>                  
                        <th>Patrimônio</th>
                        <th>ITSM</th>
                        <th>Nome Dispositivo</th>
                        <th>QrCode</th>
                    </tr>';

        foreach ($results_sales as $key => $sale) {

                        //QRCODE PRODUTO

            $link_qrcode_produto = "http://entregatic.costao.com.br/report/product_report.php?action=print_product_report&id=$sale->codigo_produto";

            $qrCode_produto = new QrCode($link_qrcode_produto);
            $qrCode_produto->setSize(200);
            $qrCode_produto->setMargin(10);
            $qrCode_produto->setEncoding('UTF-8');
            $qrCode_produto->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));


            // converte para data URI para uso direto em HTML
            $qrDataUri_produto = $qrCode_produto->writeDataUri();

            $html .= '
            <tr>               
                <td>' . $sale->nome_produto . '</td>
                <td>' . $sale->patrimonio . '</td>
                <td>' . $sale->itsm_ticket . '</td> 
                <td>' . $sale->hostname . '</td> 
                <td><img src="' . $qrDataUri_produto . '" width="110" height="110"></td> ';
                

            // Bloco condicional para coluna de código de transação */

            $html .= '         
            </tr>';
        }

        $html .= ' </table></div> <br>';

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
                                <strong>Termo de Entrega: </strong><br>
                                ' . $sale_id . '
                            </td>
                        </tr>
                    </table>
                </td>
                <td rowspan="2" width="55%" style="border: 1px solid #000; text-align: center;">
                    <div style="font-size: 11pt; font-weight: bold;">
                    TERMO DE CESSÃO E POSSE DE DISPOSITIVOS
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
                <label>Valor Total Entrega:</label>
                <span>' . $sale->subtotal . '</span>
            </div>
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
                        <h4>Escaneie para acessar o termo online:</h4>
                        <img src="' . $qrDataUri . '" width="160" height="160">
                    </div>
                </div>
            </div>';


        // Define o rodapé no Mpdf
        $mpdf->setHTMLFooter('<table width="100%" style="font-size: 8pt"><tr><td width="33%">Gerado na data: {DATE j/m/Y}</td><td width="33%" align="center">{PAGENO}/{nbpg}</td><td width="33%" style="text-align: right;">PDF gerado por: ' . $_SESSION['access_user'] . '</td></tr></table>');


        $mpdf->WriteHTML($html);

        // === PDF para anexo no e-mail
        $pdfContent = $mpdf->Output('', 'S');

        // === Envio do E-mail
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'mail.costao.com.br';
            $mail->SMTPAuth = false;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('suporte@costao.com.br', 'Entregas TIC');
            $mail->addAddress($results_sales[0]->email, $results_sales[0]->nome);
            //$mail->addBCC("giordano.alves@costao.com.br", "Giornado Alves - TIC");
            $mail->addBCC("anilton.junior@costao.com.br", "Anilton Junior - Coordenador de innfraestrutura de TI");
            $mail->addBCC("coordenacao.rh@costao.com.br", "Lise de Andrade - Coordenadora de RH");
            $mail->addBCC("beatriz.vogt@costao.com.br", "Beatriz Vogt - RH");
            $mail->addBCC("rescisao.rh@costao.com.br", "Victor Diorio - RH");
            $mail->isHTML(true);
            $mail->Subject = "Termo de Entrega - Nº {$sale_id} - Colaborador {$results_sales[0]->nome}";
            $mail->Body = "
                Olá <b>{$results_sales[0]->nome}</b>,<br><br>
                Segue em anexo seu Termo de Entrega nº <b>{$sale_id}</b>.<br><br>
                Atenciosamente,<br>
                <strong>Equipe TIC</strong>
            ";

            $mail->addStringAttachment($pdfContent, "Termo de Entrega {$sale_id} - {$results_sales[0]->nome}.pdf");
            
            $mail->send();

            if ($action === 'send_mail') {
                redirect(["status" => "success", "message" => "E-mail enviado com sucesso!"]);
            } else {
                $mpdf->Output("Termo de Entrega {$sale_id} - {$results_sales[0]->nome}.pdf", "I");
                exit();
            }


        } catch (Exception $e) {
            if ($action === 'send_mail') {
                redirect(["status" => "error", "message" => "Erro ao enviar o e-mail: " . $mail->ErrorInfo]);
            } else {
                echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
            }
        }

        ob_end_flush();
    }
}

?>

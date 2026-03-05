$('#select_product').select2({
    theme: "bootstrap-5",
    language: "pt-BR",
    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
    placeholder: $(this).data('placeholder'),
});

$('#select_client').select2({
    theme: "bootstrap-5",
    language: "pt-BR",
    width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
    placeholder: $(this).data('placeholder'),
});

function display(modal) {
    let exibirModal = document.getElementById(modal);
    $(exibirModal).modal('show');
}

$(document).ready(function () {
    // Função para verificar se ambos cliente, produto e quantidade foram selecionados
    function checkSelection() {
        let selectedClient = $("#select_client").val();
        let selectedProduct = $("#select_product").val();
        let quantity = parseInt($(this).val());

        // Se ambos cliente e produto foram selecionados, habilitar o botão
        if (selectedClient && selectedProduct && quantity != 0) {
            $('#add_product').prop('disabled', false);
        } else {
            $('#add_product').prop('disabled', true);
        }
    }

    // Adicionar eventos de escuta aos campos de seleção
    $("#select_client, #select_product").on('change', checkSelection);
});


//seleção do produto, pegando o valor unitário do produto e setando no campo unit_price. configura também a imagem do produto
$('#select_product').on('change', function () {
    $('#product_quantity').val('1');
    let unit_price = $('#select_product option:selected').data('price');
    $('#unit_price').val(unit_price);
    $('#total_price').val(unit_price);
    let preview_img = $('#select_product option:selected').data('img');
    if (preview_img == null || preview_img == '' || preview_img == undefined) {
        $('#preview_img').attr('src', "../assets/img/avatar/shopping-cart.webp");
    } else {
        $('#preview_img').attr('src', "../assets/img/products/" + preview_img);
    }
});


$("#product_quantity").on('input', function () {
    let quantity = parseInt($(this).val());
    if (quantity > 0) {
        let unit_price = $('#unit_price').val().replace(/[^0-9]/g, "");
        let total_price = unit_price * quantity;

        // Formatando para reais
        total_price = (total_price / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

        $('#total_price').val(total_price);
    } else {
        $('#product_quantity').val('1');
    }
});

function checkProductRepeated() {
    //se o id do produto já estiver na tabela, não permitir adicionar novamente
    let selectedProductID = $('#select_product').val();
    let tableProduct = document.querySelectorAll("#table_product")
    let rows = tableProduct[0].rows;
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].cells[0].innerHTML == selectedProductID) {
            return false;
        }
    }
    return true;
}

//adicionando produto na tabela
function addProductTable() {
    let selectedProductID = $('#select_product').val();
    let selectedProduct = $('#select_product option:selected').text();
    let selectedUnitPrice = $('#unit_price').val();
    let selectedQuantity = $('#product_quantity').val();
    let selectedTotalPrice = $('#total_price').val();
    let patrimony = $('#patrimony').val() || '';  
    let itsm_ticket = $('#itsm_ticket').val() || '';
    let hostname = $('#hostname').val() || '';

    let tableProduct = document.querySelector("#table_product");
    let quantityProductDatabase = $('#select_product option:selected').data('qnt');
    let subtotal = document.querySelector("#subtotal");

    if (selectedQuantity > quantityProductDatabase) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Quantidade do produto indisponível em estoque!. Em estoque: ' + quantityProductDatabase + ' unidades.',
        });
    } else if (!checkProductRepeated()) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Produto já adicionado!',
        });
    } else {
        let line = tableProduct.insertRow();

        line.insertCell(0).innerHTML = selectedProductID;
        line.insertCell(1).innerHTML = selectedProduct;
        line.insertCell(2).innerHTML = selectedUnitPrice;
        line.insertCell(3).innerHTML = selectedQuantity;
        line.insertCell(4).innerHTML = patrimony;
        line.insertCell(5).innerHTML = itsm_ticket;
        line.insertCell(6).innerHTML = hostname;
        line.insertCell(7).innerHTML = selectedTotalPrice;
        line.insertCell(8).innerHTML = '<i class="fas fa-trash-alt" style="cursor:pointer" onclick="deleteProduct(this)"></i>';


        // Calcular subtotal
        let total = 0;
        for (let i = 1; i < tableProduct.rows.length; i++) {
            total += parseInt(tableProduct.rows[i].cells[7].innerHTML.replace(/[^0-9]/g, ""));
        }

        total = total.toString().replace(/([0-9]{2})$/g, ",$1");
        if (total.length > 6) {
            total = total.toString().replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
        }
        subtotal.value = "R$ " + total;

        $('#select_client').prop('disabled', true);
        $('#close_sale').prop('disabled', false);
    }
}


//removendo produto da tabela e recalculando o subtotal
function deleteProduct(id) {
    let row = id.parentNode.parentNode;
    let tableProduct = document.querySelectorAll("#table_product")
    let subtotal = document.querySelectorAll("#subtotal");

    //remover o produto da tabela
    tableProduct[0].deleteRow(row.rowIndex);

    //preenchendo o input de subtotal. Somando todos os valores da coluna valor total
    let total = 0;
    for (let i = 1; i < tableProduct[0].rows.length; i++) {
        total += parseInt(tableProduct[0].rows[i].cells[4].innerHTML.replace(/[^0-9]/g, ""));
    }
    total = total.toString().replace(/([0-9]{2})$/g, ",$1");

    if (total.length > 6) {
        total = total.toString().replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
    }
    //adicionado R$ ao valor total
    total = "R$ " + total;
    subtotal[0].value = total;
}

//cancelando a venda e limpando todos os campos da tela
function cancelSale() {

    Swal.fire({
        icon: 'warning',
        title: 'Atenção!',
        text: 'Deseja realmente cancelar a entrega?',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {

            //removendo todas as seleçoes do Selec2
            $("#select_client").val(null).trigger("change");
            $("#select_product").val(null).trigger("change");

            //limpando os campos da tela
            let subtotal = document.querySelectorAll("#subtotal");
            let productQuantity = document.querySelectorAll("#product_quantity");
            let unitPrice = document.querySelectorAll("#unit_price");
            let totalPrice = document.querySelectorAll("#total_price");
            let previewImg = document.querySelectorAll("#preview_img");
            let add_product = document.querySelectorAll("#add_product");
            let patrimony = document.querySelectorAll("#patrimony");
            let itsm_ticket = document.querySelectorAll("#itsm_ticket");
            let hostname = document.querySelectorAll("#hostname");


            //limpando a tabela
            let tableProduct = document.querySelectorAll("#table_product")
            let rows = tableProduct[0].rows;
            for (let i = rows.length - 1; i > 0; i--) {
                tableProduct[0].deleteRow(i);
            }

            //limpando os campos
            subtotal[0].value = "R$ 00,00"
            productQuantity[0].value = 1;
            unitPrice[0].value = "R$ 00,00"
            totalPrice[0].value = "R$ 00,00"
            previewImg[0].src = '../assets/img/avatar/shopping-cart.webp';
            add_product[0].disabled = true;
            patrimony[0].value = '';
            itsm_ticket[0].value = '';
            hostname[0].value = '';

            //desbloquear o seletor de cliente
            $('#select_client').prop('disabled', false);

            //bloquear o botão de fechar o pedido
            $('#close_sale').prop('disabled', true);
        }
    });
}

//fechando a venda e abrindo o modal de pagamento 
function closeSale() {
    let tableProduct = document.querySelectorAll("#table_product")
    let subtotal = document.querySelectorAll("#subtotal");

    if (tableProduct[0].rows.length == 1) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Adicione um produto para fechar a entrega!',
        });
    } else {
        //chamando o modal de pagamento
        display('modal-close-order');

        //mostrar o valor total da venda no input de Total Venda que esta no modal
        let total_sale = document.getElementById('total_sale');
        total_sale.value = subtotal[0].value;

        //se pagamento for em cartão liberar o campo de codigo de transação
        /*$('#payment_type').on('change', function () {
            if ($('#payment_type').val() != 'dinheiro') {
                //habilita o campo de transação
                $('#cd_transaction_pix').prop('disabled', false);
                $('#cd_transaction_pix').prop('required', true);
            } else {
                //desabilita o campo de transação
                $('#cd_transaction_pix').prop('disabled', true);
            }
        });*/

        //let inputDiscount = document.getElementById('discount');
        //let inputDisconuntedPrice = document.getElementById('disconunted_price');
        //let inputAmountPaid = document.getElementById('amount_paid');

        //calcular o valor total da venda com desconto (desconto em %)
        /*inputDiscount.addEventListener('input', function () {
            let discount = inputDiscount.value;
            let total = parseFloat(subtotal[0].value.replace(/[^0-9]/g, "")) / 100; // Converte o total para número, assumindo que ele já está em centavos.
            let totalDiscount = total - (total * discount / 100);

            // Formatação usando Intl.NumberFormat
            let formatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });

            let formattedTotalDiscount = formatter.format(totalDiscount);
            inputDisconuntedPrice.value = formattedTotalDiscount;
        });

        //verificar se o desconto digitado é maior do que 100%
        inputDiscount.addEventListener('blur', function () {
            if (inputDiscount.value > 100 || inputDiscount.value < 0) {
                const blockModalCloseOrder = document.querySelector("#modal-close-order");
                if (blockModalCloseOrder) {
                    blockModalCloseOrder.addEventListener("keypress", function (e) {
                        if (e.key === "Enter") {
                            e.preventDefault();
                        }
                    })
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Desconto não pode ser maior que 100%!',
                    allowOutsideClick: false,
                });
                inputDiscount.value = '';
                inputDisconuntedPrice.value = '';
                inputAmountPaid.value = subtotal[0].value;
            }
        });

        inputDiscount.addEventListener('change', function () {
            //calculando o valor a ser pago, de acordo com o valor com desconto ou sem desconto
            if (inputDiscount.value == '') {
                inputAmountPaid.value = subtotal[0].value;
            } else {
                //calculando o valor a ser pago com desconto
                inputAmountPaid.value = inputDisconuntedPrice.value;
            }
        })
        inputAmountPaid.value = subtotal[0].value;*/
    }
}
    

// salvando a venda
const formCloseOrder = document.querySelector('#formCloseOrder');
if (formCloseOrder) {
    formCloseOrder.addEventListener('submit', function (e) {
        e.preventDefault();

        // Verifica se há assinatura
        if (signaturePad.isEmpty()) {
            Swal.fire({
                icon: 'error',
                title: 'Assinatura obrigatória!',
                text: 'Por favor, realize a assinatura antes de fechar a entrega.'
            });
            return;
        }

        const dataURL = signaturePad.toDataURL();
        document.getElementById('signature-data').value = dataURL;

        // Coletando dados
        let client = document.getElementById('select_client').value;
        let products = [];
        let tableProduct = document.querySelector("#table_product");

        for (let i = 1; i < tableProduct.rows.length; i++) {
            let product = {
                product_id: tableProduct.rows[i].cells[0].innerHTML,
                quantity: tableProduct.rows[i].cells[3].innerHTML,
                price: tableProduct.rows[i].cells[2].innerHTML,
                patrimony: tableProduct.rows[i].cells[4].innerHTML,
                itsm_ticket: tableProduct.rows[i].cells[5].innerHTML,
                hostname: tableProduct.rows[i].cells[6].innerHTML
            };
            products.push(product);
        }

        let subtotal = document.getElementById('subtotal').value;
        let csrfToken = document.querySelector('input[name="csrf_token"]').value;
        let action = document.querySelector('input[name="action"]').value;


        let values_sale = {
            client: client,
            products: products,
            subtotal: subtotal,
            csrf_token: csrfToken,
            action: action,
            'signature-data': dataURL
            
        };

        showLoading();
        $.ajax({
            type: 'POST',
            url: '../controllers/sales_controller.php',
            dataType: 'json',
            data: values_sale,
            async: true,

            success: function (response) {
                hideLoading();
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        text: response.message
                    });
                } else {
                    const numberSale = response.id;

                    // Primeira pergunta: imprimir?
                    Swal.fire({
                        icon: 'question',
                        title: 'Entrega realizada com sucesso!',
                        text: 'Deseja imprimir o comprovante?',
                        showCancelButton: true,
                        confirmButtonText: 'Sim',
                        cancelButtonText: 'Não'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open('../report/delivery_report.php?action=print_sale&id=' + numberSale, '_blank');

                            // Segunda pergunta: enviar por e-mail?
                            setTimeout(() => {
                                Swal.fire({
                                    icon: 'question',
                                    title: 'Deseja enviar o comprovante por e-mail?',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sim',
                                    cancelButtonText: 'Não'
                                }).then((emailResult) => {
                                    if (emailResult.isConfirmed) {
                                        fetch('../report/send_mail_delivery_report.php?action=send_mail&id=' + numberSale)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.status === 'success') {
                                                    Swal.fire('Sucesso', data.message, 'success');
                                                } else {
                                                    Swal.fire('Erro', data.message, 'error');
                                                }
                                            })
                                            .catch(() => {
                                                Swal.fire('Erro', 'Erro ao enviar o e-mail.', 'error');
                                            });
                                    }

                                    // Redirecionar para entregas depois de tudo
                                    setTimeout(() => {
                                        window.location.href = 'entregas.php';
                                    }, 1500);
                                });
                            }, 500);
                        } else {
                            // Se não quiser imprimir, redireciona direto
                            window.location.href = 'entregas.php';
                        }
                    });
                }
            },
            error: function () {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao realizar a entrega!',
                });
            }
        });
    });
}


/*
//salvando a venda
const formCloseOrder = document.querySelector('#formCloseOrder');
if (formCloseOrder) {
    formCloseOrder.addEventListener('submit', function (e) {
        e.preventDefault()

        // Verifica se há assinatura
        if (signaturePad.isEmpty()) {
            Swal.fire({
                icon: 'error',
                title: 'Assinatura obrigatória!',
                text: 'Por favor, realize a assinatura antes de fechar a entrega.'
            });
            return;
        }

        const dataURL = signaturePad.toDataURL();
        document.getElementById('signature-data').value = dataURL;

        //pegar os dados para enviar para o backend
        //cliente, produtos, quantidade, valor unitário, valor total do produto, subtotal, tipo de pagamento, valor com/sem desconto, codigo de transação, valor pago
        let client = document.getElementById('select_client').value;
        let products = [];
        let tableProduct = document.querySelector("#table_product");

        for (let i = 1; i < tableProduct.rows.length; i++) {
            let product = {
                product_id: tableProduct.rows[i].cells[0].innerHTML,
                quantity: tableProduct.rows[i].cells[3].innerHTML,
                price: tableProduct.rows[i].cells[2].innerHTML,
                patrimony: tableProduct.rows[i].cells[4].innerHTML  // <-- Captura patrimônio
            }
            products.push(product);
        }

        let subtotal = document.getElementById('subtotal').value;
        //let payment_type = document.getElementById('payment_type').value;
        //let discount = document.getElementById('discount').value;
        //let disconunted_price = document.getElementById('disconunted_price').value;
        //let amount_paid = document.getElementById('amount_paid').value;
        //let cd_transaction_pix = document.getElementById('cd_transaction_pix').value;

        let csrfToken = document.querySelector('input[name="csrf_token"]').value;
        let action = document.querySelector('input[name="action"]').value;

        //console.log(client, products, subtotal, payment_type, discount, disconunted_price, amount_paid, cd_transaction_pix);

        //enviar os dados para o backend
        showLoading();
        values_sale = {
            client: client,
            products: products,
            subtotal: subtotal,
            //payment_type: payment_type,
            //discount: discount,
            //disconunted_price: disconunted_price,
            //amount_paid: amount_paid,
            //cd_transaction_pix: cd_transaction_pix,
            csrf_token: csrfToken,
            action: action,
            'signature-data': dataURL
        }
        $.ajax({
            type: 'POST',
            url: '../controllers/sales_controller.php',
            dataType: 'json',
            data: values_sale,
            async: true,

            success: function (response) {
                hideLoading();
                if (response.error) {
                    hideLoading();
                    Swal.fire({
                        icon: 'error',
                        text: response.message
                    });
                } else {
                    hideLoading();
                    const numberSale = response.id;
                    Swal.fire({
                        icon: 'question',
                        title: 'Entrega realizada com sucesso! Deseja Imprimir o Comprovante?',
                        showCancelButton: true,
                        confirmButtonText: 'Sim',
                        cancelButtonText: 'Não'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            //imprimir o cupom
                            window.open('../report/delivery_report.php?action=print_sale&id=' + numberSale, '_blank');
                            window.location.href = 'entregas.php';
                        } else {
                            window.location.href = 'entregas.php';
                        }
                    });
                }
            },
            error: function () {
                hideLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'Erro ao realizar a entrega!',
                });
            }
        });

    });
}*/

//no modal de pagamento voltar para a tela de venda zerando todos os campos
function backToSale() {
    //limpar os campos do modal de pagamento
    //let inputPaymentType = document.getElementById('payment_type');
    //let inputDiscount = document.getElementById('discount');
    //let inputDisconuntedPrice = document.getElementById('disconunted_price');
    //let inputAmountPaid = document.getElementById('amount_paid');

    //inputPaymentType.value = 'dinheiro';
    //inputDiscount.value = '';
    //inputDisconuntedPrice.value = '';
    //inputAmountPaid.value = '';
    //$('#cd_transaction_pix').val('');
    //$('#cd_transaction_pix').prop('disabled', true);
    //fechar o modal de pagamento
    $('#modal-close-order').modal('hide');
}


// Inicializa o canvas da assinatura
const canvas = document.getElementById('signature-pad');



const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(255, 255, 255)'
});

// Função para limpar a assinatura
function clearSignature() {
    signaturePad.clear();
}

// Antes de submeter o formulário, salvar a assinatura
const form = document.getElementById('formCloseOrder');

form.addEventListener('submit', function(event) {
    if (signaturePad.isEmpty()) {
        alert('Por favor, realize a assinatura antes de finalizar.');
        event.preventDefault();
    } else {
        const dataURL = signaturePad.toDataURL();
        document.getElementById('signature-data').value = dataURL;
    }
});

$(document).ready(function () {
    const $itsmTicket = $('#itsm_ticket');

    $itsmTicket.on('input', function () {
        let numbers = $itsmTicket.val().replace(/\D/g, '').slice(0, 10); // Apenas números, máximo 10 dígitos

        if (numbers.length > 4) {
            numbers = numbers.slice(0, 4) + '-' + numbers.slice(4);
        }

        $itsmTicket.val(numbers);
    });
});

$(document).ready(function () {
    const $patrimonio = $('#patrimony');

    $patrimonio.on('input', function () {
        let numbers_patrimonio = $patrimonio.val().replace(/\D/g, '').slice(0, 6);

        $patrimonio.val(numbers_patrimonio);
    });
});


/*$(document).ready(function () {
    const $hostname = $('#hostname');

    $hostname.on('input', function () {
        let input = $(this).val().toUpperCase();

        // Remove tudo que não for letra ou número
        input = input.replace(/[^A-Z0-9]/g, '');

        // Força as letras nos primeiros 8 caracteres e números nos 3 últimos
        let letras = input.slice(0, 8).replace(/[^A-Z]/g, '');
        let numeros = input.slice(8).replace(/\D/g, '');

        letras = letras.slice(0, 8);     // no máximo 8 letras
        numeros = numeros.slice(0, 3);   // no máximo 3 números

        let formatado = '';

        if (letras.length >= 2) {
            formatado += letras.slice(0, 2) + '-';
        } else {
            formatado += letras;
        }

        if (letras.length >= 5) {
            formatado += letras.slice(2, 5) + '-';
        } else if (letras.length > 2) {
            formatado += letras.slice(2);
        }

        if (letras.length >= 8) {
            formatado += letras.slice(5, 8);
        } else if (letras.length > 5) {
            formatado += letras.slice(5);
        }

        if (numeros.length > 0) {
            formatado += numeros;
        }

        $(this).val(formatado);
    });
});*/

$(document).ready(function () {
    const $hostname = $('#hostname');

    $hostname.on('input', function () {
        let input = $(this).val().toUpperCase();

        // Remove tudo que não for letra ou número
        input = input.replace(/[^A-Z0-9]/g, '');

        // Verifica se está no formato simples: 2 letras seguidas de até 4 números
        const matchSimple = input.match(/^([A-Z]{2})([0-9]{1,4})$/);
        if (matchSimple) {
            $(this).val(matchSimple[1] + matchSimple[2]);
            return;
        }

        // Caso contrário, aplica o formato padrão com traços
        let letras = input.slice(0, 8).replace(/[^A-Z]/g, '');
        let numeros = input.slice(8).replace(/\D/g, '');

        letras = letras.slice(0, 8);     // no máximo 8 letras
        numeros = numeros.slice(0, 3);   // no máximo 3 números

        let formatado = '';

        if (letras.length >= 2) {
            formatado += letras.slice(0, 2) + '-';
        } else {
            formatado += letras;
        }

        if (letras.length >= 5) {
            formatado += letras.slice(2, 5) + '-';
        } else if (letras.length > 2) {
            formatado += letras.slice(2);
        }

        if (letras.length >= 8) {
            formatado += letras.slice(5, 8);
        } else if (letras.length > 5) {
            formatado += letras.slice(5);
        }

        if (numeros.length > 0) {
            formatado += numeros;
        }

        $(this).val(formatado);
    });
});


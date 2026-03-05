document.addEventListener("DOMContentLoaded", function () {
    const selectVenda = document.getElementById("select_venda");
    const tabelaProdutos = document.getElementById("table_return").querySelector("tbody");
    const signatureModal = new bootstrap.Modal(document.getElementById("modal-confirm-return"));
    let produtosSelecionadosGlobal = [];

    const canvas = document.getElementById("canvas_assinatura_devolucao");
    const signaturePad = new SignaturePad(canvas);

    selectVenda.addEventListener("change", function () {
        const vendaId = this.value;
        if (!vendaId) return;

        fetch("../controllers/devolucao_controller.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "get_sale_products",
                venda_id: vendaId,
                csrf_token: window.csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            tabelaProdutos.innerHTML = "";
            if (data.success && Array.isArray(data.products)) {
                data.products.forEach(produto => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${produto.codigo}</td>
                        <td>${produto.nome}</td>
                        <td>R$ ${produto.valor_unitario}</td>
                        <td>${produto.quantidade}</td>
                        <td>${produto.patrimonio || ""}</td>
                        <td>${produto.itsm_ticket || ""}</td>
                        <td>${produto.hostname || ""}</td>
                        <td>R$ ${produto.valor_total}</td>
                        <td><input type="checkbox" class="check_devolver" data-id="${produto.id}"></td>
                        <td><input type="text" class="form-control input_motivo" placeholder="Motivo da devolução" data-id="${produto.id}"></td>
                    `;
                    tabelaProdutos.appendChild(tr);
                });
                document.getElementById("btn_register_return").disabled = false;
            } else {
                Swal.fire('Erro', 'Erro ao carregar os produtos da venda.', 'error');
            }
        })
        .catch(error => console.error("Erro:", error));
    });

    document.getElementById("btn_register_return").addEventListener("click", function () {
        const vendaId = selectVenda.value;
        const produtosSelecionados = Array.from(document.querySelectorAll(".check_devolver:checked")).map(chk => {
            const id = chk.dataset.id;
            const motivo = document.querySelector(`.input_motivo[data-id='${id}']`).value.trim();
            return { id, motivo };
        });

        if (produtosSelecionados.length === 0) {
            Swal.fire('Atenção!', 'Selecione ao menos um produto para devolver.', 'warning');
            return;
        }

        const faltandoMotivo = produtosSelecionados.some(p => p.motivo === '');
        if (faltandoMotivo) {
            Swal.fire('Atenção!', 'Informe o motivo da devolução para todos os produtos selecionados.', 'warning');
            return;
        }

        produtosSelecionadosGlobal = produtosSelecionados;
        signatureModal.show();
    });

    document.getElementById("formConfirmReturn").addEventListener("submit", function (e) {
        e.preventDefault();

        if (signaturePad.isEmpty()) {
            Swal.fire('Atenção!', 'Por favor, realize a assinatura antes de confirmar.', 'warning');
            return;
        }

        const vendaId = selectVenda.value;
        const csrfToken = document.querySelector("input[name='csrf_token']").value;
        const dataURL = signaturePad.toDataURL();

        const dados = {
            action: "registrar_devolucao",
            venda_id: vendaId,
            produtos: produtosSelecionadosGlobal,
            csrf_token: csrfToken,
            assinatura_devolucao: dataURL
        };

        fetch("../controllers/devolucao_controller.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(dados)
        })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                const returnId = response.id;

                Swal.fire({
                    icon: 'success',
                    title: 'Devolução registrada com sucesso!',
                    text: 'Deseja imprimir o comprovante?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim',
                    cancelButtonText: 'Não'
                }).then(result => {
                    if (result.isConfirmed) {
                        window.open(`../report/return_report.php?action=print_return&id=${vendaId}`, '_blank');
                    }

                    Swal.fire({
                        icon: 'question',
                        title: 'Deseja enviar o comprovante por e-mail?',
                        showCancelButton: true,
                        confirmButtonText: 'Sim',
                        cancelButtonText: 'Não'
                    }).then(emailResult => {
                        if (emailResult.isConfirmed) {
                            fetch(`../report/send_mail_return_report.php?action=send_mail&id=${vendaId}`, {
                                method: "GET"
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire('Sucesso', data.message, 'success');
                                } else {
                                    Swal.fire('Erro', data.message, 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Sucesso', data.message, 'success');
                            });
                        }

                        setTimeout(() => {
                            window.location.href = 'devolucao';
                        }, 1500);
                    });
                });

            } else {
                Swal.fire('Erro!', response.message || 'Falha ao registrar devolução.', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Erro de rede!', 'Falha ao conectar com o servidor.', 'error');
        });
    });
});

function cancelReturn() {
    Swal.fire({
        icon: 'warning',
        title: 'Atenção!',
        text: 'Deseja realmente cancelar a devolução?',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    }).then((result) => {
        if (result.isConfirmed) {
            $("#select_venda").val(null).trigger("change");
            const tabelaProdutos = document.getElementById("table_return").querySelector("tbody");
            tabelaProdutos.innerHTML = `<tr><td colspan="10">Selecione uma entrega realizada para visualizar os dispositivos.</td></tr>`;
            document.getElementById("btn_register_return").disabled = true;
        }
    });
}

function clearSignatureReturn() {
    const canvas = document.getElementById("canvas_assinatura_devolucao");
    const signaturePad = new SignaturePad(canvas);
    signaturePad.clear();
}

function backToDevolution() {
    const modal = bootstrap.Modal.getInstance(document.getElementById("modal-confirm-return"));
    modal.hide();
}

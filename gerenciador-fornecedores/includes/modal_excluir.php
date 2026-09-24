<?php
/**
 * Modal único de confirmação de exclusão, reutilizado por fornecedores, produtos e contatos.
 * O botão que abre o modal informa, via data-attributes:
 *   data-action (arquivo que processa), data-id, data-fornecedor, data-titulo e data-texto.
 * O preenchimento é feito em assets/js/app.js.
 */
?>
<div class="modal fade" id="modalExcluir" tabindex="-1" aria-labelledby="modalExcluirTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <input type="hidden" name="fornecedor_id" value="">

            <div class="modal-header">
                <h2 class="modal-title fs-5" id="modalExcluirTitulo">Excluir</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" data-role="texto"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Excluir</button>
            </div>
        </form>
    </div>
</div>

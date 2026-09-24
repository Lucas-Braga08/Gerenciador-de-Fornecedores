/**
 * Gerenciador de Fornecedores — comportamentos de interface.
 * - máscaras de CNPJ, telefone e preço
 * - conferência do tamanho do CNPJ no navegador (o servidor confere de novo)
 * - preenchimento dos modais de produto, contato e exclusão
 * - abas do detalhe do fornecedor lembram a seleção pelo hash da URL
 */
(function () {
    'use strict';

    /* ---------- Máscaras ---------- */

    const mascaras = {
        cnpj(valor) {
            return valor
                .replace(/\D/g, '')
                .slice(0, 14)
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
        },

        telefone(valor) {
            const d = valor.replace(/\D/g, '').slice(0, 11);
            if (d.length > 10) return d.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            if (d.length > 6) return d.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            if (d.length > 2) return d.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
            if (d.length > 0) return '(' + d;
            return '';
        },

        // Durante a digitação só aceita números, vírgula e ponto
        moeda(valor) {
            return valor.replace(/[^\d.,]/g, '');
        },
    };

    /** Converte "1.234,56" ou "12.5" em número (mesma regra do PHP). */
    function paraNumero(texto) {
        let v = texto.replace('R$', '').trim();
        if (v === '') return null;
        if (v.includes(',')) v = v.replace(/\./g, '').replace(',', '.');
        const n = Number(v);
        return Number.isFinite(n) ? n : null;
    }

    function formatarMoeda(input) {
        const n = paraNumero(input.value);
        if (n !== null) {
            input.value = n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    document.querySelectorAll('[data-mask]').forEach((input) => {
        const aplicar = mascaras[input.dataset.mask];
        if (!aplicar) return;

        input.addEventListener('input', () => {
            input.value = aplicar(input.value);
        });

        if (input.dataset.mask === 'moeda') {
            input.addEventListener('blur', () => formatarMoeda(input));
        }
    });

    /* ---------- Validação de CNPJ ---------- */

    // Confere apenas se o CNPJ tem 14 dígitos (não valida os dígitos verificadores)
    function cnpjCompleto(valor) {
        return valor.replace(/\D/g, '').length === 14;
    }

    const campoCnpj = document.getElementById('cnpj');
    if (campoCnpj) {
        const validar = () => {
            const preenchido = campoCnpj.value.trim() !== '';
            campoCnpj.setCustomValidity(!preenchido || cnpjCompleto(campoCnpj.value) ? '' : 'CNPJ incompleto');
        };
        campoCnpj.addEventListener('input', validar);
        validar();
    }

    /* ---------- Validação de formulários (Bootstrap) ---------- */

    document.querySelectorAll('.needs-validation').forEach((form) => {
        form.addEventListener('submit', (evento) => {
            if (!form.checkValidity()) {
                evento.preventDefault();
                evento.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    /* ---------- Modais de produto e contato (novo / editar) ---------- */

    /**
     * O botão que abre o modal traz os dados do registro em data-attributes
     * com o mesmo nome dos campos do formulário (id, nome, cargo, email...).
     * Sem data-id, o modal abre vazio para um novo cadastro.
     */
    function configurarModalDeCadastro(modal) {
        const form = modal.querySelector('form');
        const titulo = modal.querySelector('.modal-title');

        modal.addEventListener('show.bs.modal', (evento) => {
            const dados = evento.relatedTarget ? evento.relatedTarget.dataset : {};
            const editando = Boolean(dados.id);

            form.reset();
            form.classList.remove('was-validated');
            form.elements.id.value = '';
            titulo.textContent = editando ? titulo.dataset.edicao : titulo.dataset.novo;

            if (!editando) return;

            Object.entries(dados).forEach(([campo, valor]) => {
                const elemento = form.elements[campo];
                if (!elemento || campo === 'fornecedorId') return;

                if (elemento.type === 'checkbox') {
                    elemento.checked = valor === '1';
                } else {
                    elemento.value = valor;
                }
            });
        });

        modal.addEventListener('shown.bs.modal', () => {
            const primeiro = form.querySelector('input[type="text"], input[type="email"]');
            if (primeiro) primeiro.focus();
        });
    }

    ['modalProduto', 'modalContato'].forEach((idModal) => {
        const modal = document.getElementById(idModal);
        if (modal) configurarModalDeCadastro(modal);
    });

    /* ---------- Modal de exclusão ---------- */

    const modalExcluir = document.getElementById('modalExcluir');
    if (modalExcluir) {
        modalExcluir.addEventListener('show.bs.modal', (evento) => {
            const botao = evento.relatedTarget;
            if (!botao) return;

            const form = modalExcluir.querySelector('form');
            form.action = botao.dataset.action;
            form.elements.id.value = botao.dataset.id || '';
            form.elements.fornecedor_id.value = botao.dataset.fornecedor || '';

            modalExcluir.querySelector('.modal-title').textContent = botao.dataset.titulo || 'Excluir';
            modalExcluir.querySelector('[data-role="texto"]').textContent = botao.dataset.texto || '';
        });
    }

    /* ---------- Abas: lembrar a seleção pelo hash da URL ---------- */

    const abas = document.querySelectorAll('[data-bs-toggle="tab"]');
    if (abas.length) {
        if (/^#[\w-]+$/.test(location.hash)) {
            const alvo = document.querySelector('[data-bs-toggle="tab"][data-bs-target="' + location.hash + '"]');
            if (alvo) bootstrap.Tab.getOrCreateInstance(alvo).show();
        }

        abas.forEach((aba) => {
            aba.addEventListener('shown.bs.tab', (evento) => {
                history.replaceState(null, '', evento.target.dataset.bsTarget);
            });
        });
    }
})();

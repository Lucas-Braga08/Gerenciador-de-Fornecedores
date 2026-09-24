<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM fornecedores WHERE id = :id');
$stmt->execute([':id' => $id]);
$f = $stmt->fetch();

if (!$f) {
    flash('warning', 'Fornecedor não encontrado.');
    redirect('fornecedores.php');
}

$stmt = $pdo->prepare('SELECT * FROM produtos WHERE fornecedor_id = :id ORDER BY nome');
$stmt->execute([':id' => $id]);
$produtos = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT * FROM contatos WHERE fornecedor_id = :id ORDER BY principal DESC, nome');
$stmt->execute([':id' => $id]);
$contatos = $stmt->fetchAll();

$tituloPagina = $f['razao_social'];
$menuAtivo    = 'fornecedores';
require __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="fornecedores.php">Fornecedores</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= h($f['razao_social']) ?></li>
    </ol>
</nav>

<div class="page-head">
    <div>
        <h1><?= h($f['razao_social']) ?></h1>
        <p class="text-muted mb-0">
            <?= h($f['nome_fantasia'] ?: 'Sem nome') ?>
            <span class="badge-status <?= h($f['status']) ?> ms-2"><?= $f['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="fornecedor_form.php?id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <button type="button" class="btn btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#modalExcluir"
                data-action="actions/fornecedor_excluir.php"
                data-id="<?= $id ?>"
                data-titulo="Excluir fornecedor"
                data-texto="Excluir &quot;<?= h($f['razao_social']) ?>&quot;? Os produtos e contatos dessa empresa também serão removidos.">
            <i class="bi bi-trash"></i> Excluir
        </button>
    </div>
</div>

<section class="panel mb-4">
    <dl class="info-grid mb-0">
        <div>
            <dt>CNPJ</dt>
            <dd class="num"><?= h(formatar_cnpj($f['cnpj'])) ?></dd>
        </div>
        <div>
            <dt>E-mail</dt>
            <dd><?= $f['email'] ? '<a href="mailto:' . h($f['email']) . '">' . h($f['email']) . '</a>' : '&mdash;' ?></dd>
        </div>
        <div>
            <dt>Telefone</dt>
            <dd class="num"><?= $f['telefone'] ? h(formatar_telefone($f['telefone'])) : '&mdash;' ?></dd>
        </div>
        <div>
            <dt>Cidade</dt>
            <dd><?= h(trim($f['cidade'] . ($f['uf'] ? ' - ' . $f['uf'] : ''), ' -')) ?: '&mdash;' ?></dd>
        </div>
    </dl>
</section>

<section class="panel">
    <ul class="nav nav-tabs panel-tabs" id="abas" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="aba-produtos" data-bs-toggle="tab" data-bs-target="#produtos"
                    type="button" role="tab" aria-controls="produtos" aria-selected="true">
                Produtos <span class="count"><?= count($produtos) ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="aba-contatos" data-bs-toggle="tab" data-bs-target="#contatos"
                    type="button" role="tab" aria-controls="contatos" aria-selected="false">
                Contatos <span class="count"><?= count($contatos) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- ===== Produtos ===== -->
        <div class="tab-pane fade show active" id="produtos" role="tabpanel" aria-labelledby="aba-produtos" tabindex="0">
            <div class="tab-toolbar">
                <span class="text-muted">Itens que esta empresa fornece.</span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProduto">
                    <i class="bi bi-plus-lg"></i> Adicionar produto
                </button>
            </div>

            <?php if (!$produtos): ?>
                <div class="empty">
                    <i class="bi bi-box"></i>
                    <p>Nenhum produto associado a este fornecedor.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Descrição</th>
                                <th>Unidade</th>
                                <th class="text-end">Preço</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td class="fw-medium"><?= h($p['nome']) ?></td>
                                <td class="text-muted"><?= h($p['descricao']) ?: '&mdash;' ?></td>
                                <td><?= h($p['unidade']) ?></td>
                                <td class="text-end num"><?= h(formatar_moeda($p['preco'])) ?></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-icon" title="Editar" aria-label="Editar produto"
                                            data-bs-toggle="modal" data-bs-target="#modalProduto"
                                            data-id="<?= (int) $p['id'] ?>"
                                            data-nome="<?= h($p['nome']) ?>"
                                            data-descricao="<?= h($p['descricao']) ?>"
                                            data-unidade="<?= h($p['unidade']) ?>"
                                            data-preco="<?= h(moeda_campo($p['preco'])) ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon text-danger" title="Excluir" aria-label="Excluir produto"
                                            data-bs-toggle="modal" data-bs-target="#modalExcluir"
                                            data-action="actions/produto_excluir.php"
                                            data-id="<?= (int) $p['id'] ?>"
                                            data-fornecedor="<?= $id ?>"
                                            data-titulo="Excluir produto"
                                            data-texto="Excluir o produto &quot;<?= h($p['nome']) ?>&quot;?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== Contatos ===== -->
        <div class="tab-pane fade" id="contatos" role="tabpanel" aria-labelledby="aba-contatos" tabindex="0">
            <div class="tab-toolbar">
                <span class="text-muted">Pessoas responsáveis pelo relacionamento com esta empresa.</span>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalContato">
                    <i class="bi bi-plus-lg"></i> Adicionar contato
                </button>
            </div>

            <?php if (!$contatos): ?>
                <div class="empty">
                    <i class="bi bi-person-lines-fill"></i>
                    <p>Nenhum contato responsável cadastrado.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Cargo</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($contatos as $c): ?>
                            <tr>
                                <td class="fw-medium">
                                    <?= h($c['nome']) ?>
                                    <?php if ($c['principal']): ?>
                                        <span class="badge-principal">Principal</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($c['cargo']) ?: '&mdash;' ?></td>
                                <td><?= $c['email'] ? '<a href="mailto:' . h($c['email']) . '">' . h($c['email']) . '</a>' : '&mdash;' ?></td>
                                <td class="num"><?= $c['telefone'] ? h(formatar_telefone($c['telefone'])) : '&mdash;' ?></td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-icon" title="Editar" aria-label="Editar contato"
                                            data-bs-toggle="modal" data-bs-target="#modalContato"
                                            data-id="<?= (int) $c['id'] ?>"
                                            data-nome="<?= h($c['nome']) ?>"
                                            data-cargo="<?= h($c['cargo']) ?>"
                                            data-email="<?= h($c['email']) ?>"
                                            data-telefone="<?= h(formatar_telefone($c['telefone'])) ?>"
                                            data-principal="<?= (int) $c['principal'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon text-danger" title="Excluir" aria-label="Excluir contato"
                                            data-bs-toggle="modal" data-bs-target="#modalExcluir"
                                            data-action="actions/contato_excluir.php"
                                            data-id="<?= (int) $c['id'] ?>"
                                            data-fornecedor="<?= $id ?>"
                                            data-titulo="Excluir contato"
                                            data-texto="Excluir o contato &quot;<?= h($c['nome']) ?>&quot;?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== Modal: produto (novo/editar) ===== -->
<div class="modal fade" id="modalProduto" tabindex="-1" aria-labelledby="modalProdutoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content needs-validation" method="post" action="actions/produto_salvar.php" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <input type="hidden" name="fornecedor_id" value="<?= $id ?>">

            <div class="modal-header">
                <h2 class="modal-title fs-5" id="modalProdutoTitulo" data-novo="Novo produto" data-edicao="Editar produto">Novo produto</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="produto_nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="produto_nome" name="nome" maxlength="120" required>
                        <div class="invalid-feedback">Informe o nome do produto.</div>
                    </div>
                    <div class="col-12">
                        <label for="produto_descricao" class="form-label">Descrição</label>
                        <input type="text" class="form-control" id="produto_descricao" name="descricao" maxlength="255">
                    </div>
                    <div class="col-6">
                        <label for="produto_unidade" class="form-label">Unidade <span class="text-danger">*</span></label>
                        <select class="form-select" id="produto_unidade" name="unidade" required>
                            <?php foreach (['un' => 'Unidade (un)', 'kg' => 'Quilo (kg)', 'l' => 'Litro (l)', 'cx' => 'Caixa (cx)', 'pct' => 'Pacote (pct)', 'rl' => 'Rolo (rl)', 'm' => 'Metro (m)'] as $sigla => $rotulo): ?>
                                <option value="<?= $sigla ?>"><?= $rotulo ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label for="produto_preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control num" id="produto_preco" name="preco"
                               data-mask="moeda" inputmode="decimal" placeholder="0,00" required>
                        <div class="invalid-feedback">Informe o preço.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar produto</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== Modal: contato (novo/editar) ===== -->
<div class="modal fade" id="modalContato" tabindex="-1" aria-labelledby="modalContatoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content needs-validation" method="post" action="actions/contato_salvar.php" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <input type="hidden" name="fornecedor_id" value="<?= $id ?>">

            <div class="modal-header">
                <h2 class="modal-title fs-5" id="modalContatoTitulo" data-novo="Novo contato" data-edicao="Editar contato">Novo contato</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="contato_nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="contato_nome" name="nome" maxlength="100" required>
                        <div class="invalid-feedback">Informe o nome do contato.</div>
                    </div>
                    <div class="col-md-5">
                        <label for="contato_cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control" id="contato_cargo" name="cargo" maxlength="80">
                    </div>
                    <div class="col-md-7">
                        <label for="contato_email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="contato_email" name="email" maxlength="120">
                        <div class="invalid-feedback">Informe um e-mail válido.</div>
                    </div>
                    <div class="col-md-5">
                        <label for="contato_telefone" class="form-label">Telefone</label>
                        <input type="text" class="form-control num" id="contato_telefone" name="telefone"
                               data-mask="telefone" inputmode="tel" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="contato_principal" name="principal" value="1">
                            <label class="form-check-label" for="contato_principal">Contato principal desta empresa</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar contato</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/modal_excluir.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

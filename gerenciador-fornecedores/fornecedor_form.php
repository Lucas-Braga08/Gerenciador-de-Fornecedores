<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$registro = [];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM fornecedores WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $registro = $stmt->fetch();

    if (!$registro) {
        flash('warning', 'Fornecedor não encontrado.');
        redirect('fornecedores.php');
    }
    $registro['cnpj']     = formatar_cnpj($registro['cnpj']);
    $registro['telefone'] = formatar_telefone($registro['telefone']);
}

// Se o salvamento falhou, reaproveita o que a pessoa digitou
$antigo = $_SESSION['old'] ?? null;
unset($_SESSION['old']);
$f = is_array($antigo) ? $antigo : $registro;

$valor = fn(string $campo, string $padrao = '') => h($f[$campo] ?? $padrao);
$editando = $id > 0;

$tituloPagina = $editando ? 'Editar fornecedor' : 'Novo fornecedor';
$menuAtivo    = 'fornecedores';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1><?= h($tituloPagina) ?></h1>
        <p class="text-muted mb-0">
            <?= $editando ? 'Atualize os dados da empresa.' : 'Depois de salvar, você poderá adicionar produtos e contatos.' ?>
        </p>
    </div>
</div>

<section class="panel panel-form">
    <form method="post" action="actions/fornecedor_salvar.php" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="row g-3">
            <div class="col-md-8">
                <label for="razao_social" class="form-label">Razão social <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="razao_social" name="razao_social"
                       maxlength="150" required value="<?= $valor('razao_social') ?>">
                <div class="invalid-feedback">Informe a razão social.</div>
            </div>

            <div class="col-md-4">
                <label for="cnpj" class="form-label">CNPJ <span class="text-danger">*</span></label>
                <input type="text" class="form-control num" id="cnpj" name="cnpj" data-mask="cnpj"
                       inputmode="numeric" placeholder="00.000.000/0000-00" maxlength="18" required
                       value="<?= $valor('cnpj') ?>">
                <div class="invalid-feedback">Informe o CNPJ com 14 dígitos.</div>
            </div>

            <div class="col-md-8">
                <label for="nome_fantasia" class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nome_fantasia" name="nome_fantasia"
                       maxlength="150" required value="<?= $valor('nome_fantasia') ?>">
                <div class="invalid-feedback">Informe o nome.</div>
            </div>

            <div class="col-md-4">
                <label for="status" class="form-label">Situação</label>
                <select class="form-select" id="status" name="status">
                    <option value="ativo" <?= ($f['status'] ?? 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= ($f['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email"
                       maxlength="120" required value="<?= $valor('email') ?>">
                <div class="invalid-feedback">Informe um e-mail válido.</div>
            </div>

            <div class="col-md-6">
                <label for="telefone" class="form-label">Telefone <span class="text-danger">*</span></label>
                <input type="text" class="form-control num" id="telefone" name="telefone" data-mask="telefone"
                       inputmode="tel" placeholder="(00) 00000-0000" minlength="14" maxlength="15" required
                       value="<?= $valor('telefone') ?>">
                <div class="invalid-feedback">Informe o telefone com DDD.</div>
            </div>

            <div class="col-md-9">
                <label for="cidade" class="form-label">Cidade <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="cidade" name="cidade"
                       maxlength="80" required value="<?= $valor('cidade') ?>">
                <div class="invalid-feedback">Informe a cidade.</div>
            </div>

            <div class="col-md-3">
                <label for="uf" class="form-label">UF <span class="text-danger">*</span></label>
                <select class="form-select" id="uf" name="uf" required>
                    <option value="">Selecione</option>
                    <?php foreach (UFS as $uf): ?>
                        <option value="<?= $uf ?>" <?= ($f['uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Selecione a UF.</div>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= $editando ? 'fornecedor_detalhe.php?id=' . $id : 'fornecedores.php' ?>" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Salvar fornecedor</button>
        </div>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

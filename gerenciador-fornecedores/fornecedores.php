<?php
require_once __DIR__ . '/includes/functions.php';

$busca  = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

$condicoes = [];
$params    = [];

if ($busca !== '') {
    $partes = ['f.razao_social LIKE :q1', 'f.nome_fantasia LIKE :q2'];
    $params[':q1'] = '%' . $busca . '%';
    $params[':q2'] = '%' . $busca . '%';

    // Permite buscar pelo CNPJ com ou sem pontuação
    $digitos = so_digitos($busca);
    if ($digitos !== '') {
        $partes[] = 'f.cnpj LIKE :q3';
        $params[':q3'] = '%' . $digitos . '%';
    }
    $condicoes[] = '(' . implode(' OR ', $partes) . ')';
}

if (in_array($status, ['ativo', 'inativo'], true)) {
    $condicoes[] = 'f.status = :status';
    $params[':status'] = $status;
}

$sql = "SELECT f.*,
               (SELECT COUNT(*) FROM produtos p WHERE p.fornecedor_id = f.id) AS total_produtos,
               (SELECT COUNT(*) FROM contatos c WHERE c.fornecedor_id = f.id) AS total_contatos
          FROM fornecedores f"
     . ($condicoes ? ' WHERE ' . implode(' AND ', $condicoes) : '')
     . ' ORDER BY f.razao_social';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$fornecedores = $stmt->fetchAll();

$tituloPagina = 'Fornecedores';
$menuAtivo    = 'fornecedores';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Fornecedores</h1>
        <p class="text-muted mb-0"><?= count($fornecedores) ?> <?= count($fornecedores) === 1 ? 'empresa encontrada' : 'empresas encontradas' ?></p>
    </div>
    <a href="fornecedor_form.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo fornecedor
    </a>
</div>

<section class="panel">
    <form class="filters" method="get" action="fornecedores.php">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="q" class="form-control" value="<?= h($busca) ?>"
                   placeholder="Buscar por razão social, nome ou CNPJ" aria-label="Buscar fornecedor">
        </div>
        <select name="status" class="form-select" aria-label="Filtrar por situação">
            <option value="">Todas as situações</option>
            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativos</option>
            <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativos</option>
        </select>
        <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
        <?php if ($busca !== '' || $status !== ''): ?>
            <a href="fornecedores.php" class="btn btn-link">Limpar</a>
        <?php endif; ?>
    </form>

    <?php if (!$fornecedores): ?>
        <div class="empty">
            <i class="bi bi-search"></i>
            <p><?= ($busca !== '' || $status !== '') ? 'Nenhum fornecedor corresponde ao filtro.' : 'Nenhum fornecedor cadastrado ainda.' ?></p>
            <?php if ($busca === '' && $status === ''): ?>
                <a href="fornecedor_form.php" class="btn btn-primary btn-sm">Cadastrar fornecedor</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>CNPJ</th>
                        <th>Cidade</th>
                        <th class="text-center">Produtos</th>
                        <th class="text-center">Contatos</th>
                        <th>Situação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fornecedores as $f): ?>
                    <tr>
                        <td>
                            <a class="link-strong" href="fornecedor_detalhe.php?id=<?= (int) $f['id'] ?>"><?= h($f['razao_social']) ?></a>
                            <?php if ($f['nome_fantasia']): ?>
                                <div class="small text-muted"><?= h($f['nome_fantasia']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="num"><?= h(formatar_cnpj($f['cnpj'])) ?></td>
                        <td><?= h(trim($f['cidade'] . ($f['uf'] ? ' - ' . $f['uf'] : ''), ' -')) ?: '&mdash;' ?></td>
                        <td class="text-center num"><?= (int) $f['total_produtos'] ?></td>
                        <td class="text-center num"><?= (int) $f['total_contatos'] ?></td>
                        <td><span class="badge-status <?= h($f['status']) ?>"><?= $f['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?></span></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-icon" href="fornecedor_detalhe.php?id=<?= (int) $f['id'] ?>" title="Ver detalhes" aria-label="Ver detalhes">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-icon" href="fornecedor_form.php?id=<?= (int) $f['id'] ?>" title="Editar" aria-label="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-icon text-danger" title="Excluir" aria-label="Excluir"
                                    data-bs-toggle="modal" data-bs-target="#modalExcluir"
                                    data-action="actions/fornecedor_excluir.php"
                                    data-id="<?= (int) $f['id'] ?>"
                                    data-titulo="Excluir fornecedor"
                                    data-texto="Excluir &quot;<?= h($f['razao_social']) ?>&quot;? Os produtos e contatos dessa empresa também serão removidos.">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/modal_excluir.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

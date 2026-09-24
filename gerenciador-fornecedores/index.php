<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = db();

$resumo = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM fornecedores)                        AS fornecedores,
        (SELECT COUNT(*) FROM fornecedores WHERE status = 'ativo') AS ativos,
        (SELECT COUNT(*) FROM produtos)                            AS produtos,
        (SELECT COUNT(*) FROM contatos)                            AS contatos"
)->fetch();

$recentes = $pdo->query(
    "SELECT f.id, f.razao_social, f.cnpj, f.cidade, f.uf, f.status,
            (SELECT COUNT(*) FROM produtos p WHERE p.fornecedor_id = f.id) AS total_produtos
       FROM fornecedores f
      ORDER BY f.criado_em DESC, f.id DESC
      LIMIT 5"
)->fetchAll();

$tituloPagina = 'Início';
$menuAtivo    = 'inicio';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Início</h1>
        <p class="text-muted mb-0">Visão geral do cadastro de fornecedores.</p>
    </div>
</div>

<section class="panel summary mb-4" aria-label="Resumo">
    <div class="summary-item">
        <span class="summary-value"><?= (int) $resumo['fornecedores'] ?></span>
        <span class="summary-label">Fornecedores</span>
    </div>
    <div class="summary-item">
        <span class="summary-value"><?= (int) $resumo['ativos'] ?></span>
        <span class="summary-label">Ativos</span>
    </div>
    <div class="summary-item">
        <span class="summary-value"><?= (int) $resumo['produtos'] ?></span>
        <span class="summary-label">Produtos cadastrados</span>
    </div>
    <div class="summary-item">
        <span class="summary-value"><?= (int) $resumo['contatos'] ?></span>
        <span class="summary-label">Contatos</span>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <h2>Últimos cadastrados</h2>
        <a href="fornecedores.php" class="btn btn-outline-secondary btn-sm">Ver todos</a>
    </div>

    <?php if (!$recentes): ?>
        <div class="empty">
            <i class="bi bi-inbox"></i>
            <p>Nenhum fornecedor cadastrado ainda.</p>
            <a href="fornecedor_form.php" class="btn btn-primary btn-sm">Cadastrar o primeiro fornecedor</a>
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
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentes as $f): ?>
                    <tr>
                        <td><a class="link-strong" href="fornecedor_detalhe.php?id=<?= (int) $f['id'] ?>"><?= h($f['razao_social']) ?></a></td>
                        <td class="num"><?= h(formatar_cnpj($f['cnpj'])) ?></td>
                        <td><?= h(trim($f['cidade'] . ($f['uf'] ? ' - ' . $f['uf'] : ''), ' -')) ?: '&mdash;' ?></td>
                        <td class="text-center num"><?= (int) $f['total_produtos'] ?></td>
                        <td><span class="badge-status <?= h($f['status']) ?>"><?= $f['status'] === 'ativo' ? 'Ativo' : 'Inativo' ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

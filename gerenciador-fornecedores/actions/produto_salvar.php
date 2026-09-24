<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../fornecedores.php');
}
csrf_check();

$id           = (int) ($_POST['id'] ?? 0);
$fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
$voltar       = '../fornecedor_detalhe.php?id=' . $fornecedorId . '#produtos';

// O fornecedor precisa existir
$stmt = db()->prepare('SELECT id FROM fornecedores WHERE id = :id');
$stmt->execute([':id' => $fornecedorId]);
if (!$stmt->fetch()) {
    flash('warning', 'Fornecedor não encontrado.');
    redirect('../fornecedores.php');
}

$nome      = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$unidade   = trim($_POST['unidade'] ?? '');
$preco     = parse_moeda($_POST['preco'] ?? '');

if ($nome === '') {
    flash('danger', 'Informe o nome do produto.');
    redirect($voltar);
}
if ($preco === null || $preco < 0 || $preco > 99999999.99) {
    flash('danger', 'Informe um preço válido.');
    redirect($voltar);
}
if ($unidade === '' || mb_strlen($unidade) > 10) {
    flash('danger', 'Selecione a unidade do produto.');
    redirect($voltar);
}

$params = [
    ':nome'      => $nome,
    ':descricao' => $descricao !== '' ? $descricao : null,
    ':unidade'   => $unidade,
    ':preco'     => $preco,
];

if ($id > 0) {
    // O "AND fornecedor_id" garante que o produto pertence mesmo a este fornecedor
    $stmt = db()->prepare(
        'UPDATE produtos SET nome = :nome, descricao = :descricao, unidade = :unidade, preco = :preco
          WHERE id = :id AND fornecedor_id = :fornecedor_id'
    );
    $stmt->execute($params + [':id' => $id, ':fornecedor_id' => $fornecedorId]);
    flash('success', 'Produto atualizado.');
} else {
    $stmt = db()->prepare(
        'INSERT INTO produtos (fornecedor_id, nome, descricao, unidade, preco)
         VALUES (:fornecedor_id, :nome, :descricao, :unidade, :preco)'
    );
    $stmt->execute($params + [':fornecedor_id' => $fornecedorId]);
    flash('success', 'Produto adicionado.');
}

redirect($voltar);

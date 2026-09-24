<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../fornecedores.php');
}
csrf_check();

$id           = (int) ($_POST['id'] ?? 0);
$fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
$voltar       = '../fornecedor_detalhe.php?id=' . $fornecedorId . '#contatos';

// O fornecedor precisa existir
$stmt = db()->prepare('SELECT id FROM fornecedores WHERE id = :id');
$stmt->execute([':id' => $fornecedorId]);
if (!$stmt->fetch()) {
    flash('warning', 'Fornecedor não encontrado.');
    redirect('../fornecedores.php');
}

$nome      = trim($_POST['nome'] ?? '');
$cargo     = trim($_POST['cargo'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefone  = so_digitos($_POST['telefone'] ?? '');
$principal = isset($_POST['principal']) ? 1 : 0;

if ($nome === '') {
    flash('danger', 'Informe o nome do contato.');
    redirect($voltar);
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'O e-mail informado é inválido.');
    redirect($voltar);
}
if ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) {
    flash('danger', 'O telefone deve ter DDD e 8 ou 9 dígitos.');
    redirect($voltar);
}

$params = [
    ':nome'      => $nome,
    ':cargo'     => $cargo !== '' ? $cargo : null,
    ':email'     => $email !== '' ? $email : null,
    ':telefone'  => $telefone !== '' ? $telefone : null,
    ':principal' => $principal,
];

$pdo = db();
$pdo->beginTransaction();

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE contatos
                SET nome = :nome, cargo = :cargo, email = :email, telefone = :telefone, principal = :principal
              WHERE id = :id AND fornecedor_id = :fornecedor_id'
        );
        $stmt->execute($params + [':id' => $id, ':fornecedor_id' => $fornecedorId]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO contatos (fornecedor_id, nome, cargo, email, telefone, principal)
             VALUES (:fornecedor_id, :nome, :cargo, :email, :telefone, :principal)'
        );
        $stmt->execute($params + [':fornecedor_id' => $fornecedorId]);
        $id = (int) $pdo->lastInsertId();
    }

    // Só pode existir um contato principal por fornecedor
    if ($principal === 1) {
        $stmt = $pdo->prepare('UPDATE contatos SET principal = 0 WHERE fornecedor_id = :fornecedor_id AND id <> :id');
        $stmt->execute([':fornecedor_id' => $fornecedorId, ':id' => $id]);
    }

    $pdo->commit();
    flash('success', 'Contato salvo.');
} catch (PDOException $e) {
    $pdo->rollBack();
    flash('danger', 'Não foi possível salvar o contato. Tente novamente.');
}

redirect($voltar);

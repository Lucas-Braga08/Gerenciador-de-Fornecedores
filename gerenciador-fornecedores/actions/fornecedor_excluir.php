<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../fornecedores.php');
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);

// Produtos e contatos são removidos junto (ON DELETE CASCADE no banco)
$stmt = db()->prepare('DELETE FROM fornecedores WHERE id = :id');
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() > 0) {
    flash('success', 'Fornecedor excluído.');
} else {
    flash('warning', 'Fornecedor não encontrado.');
}

redirect('../fornecedores.php');

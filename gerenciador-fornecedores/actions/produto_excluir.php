<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../fornecedores.php');
}
csrf_check();

$id           = (int) ($_POST['id'] ?? 0);
$fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);

$stmt = db()->prepare('DELETE FROM produtos WHERE id = :id AND fornecedor_id = :fornecedor_id');
$stmt->execute([':id' => $id, ':fornecedor_id' => $fornecedorId]);

flash($stmt->rowCount() > 0 ? 'success' : 'warning', $stmt->rowCount() > 0 ? 'Produto excluído.' : 'Produto não encontrado.');
redirect('../fornecedor_detalhe.php?id=' . $fornecedorId . '#produtos');

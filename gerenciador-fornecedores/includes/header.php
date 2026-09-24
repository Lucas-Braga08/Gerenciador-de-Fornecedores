<?php
require_once __DIR__ . '/functions.php';

$tituloPagina = $tituloPagina ?? 'Fornecedores';
$menuAtivo    = $menuAtivo ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($tituloPagina) ?> | Gerenciador de Fornecedores</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<header class="topbar">
    <nav class="navbar navbar-expand-sm container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-box-seam"></i> Fornecedores
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
                aria-expanded="false" aria-label="Abrir menu">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $menuAtivo === 'inicio' ? 'active' : '' ?>" href="index.php">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $menuAtivo === 'fornecedores' ? 'active' : '' ?>" href="fornecedores.php">Fornecedores</a>
                </li>
            </ul>
            <a class="btn btn-primary btn-sm" href="fornecedor_form.php">
                <i class="bi bi-plus-lg"></i> Novo fornecedor
            </a>
        </div>
    </nav>
</header>

<main class="container py-4">
    <?php foreach (get_flashes() as $msg): ?>
        <div class="alert alert-<?= h($msg['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= h($msg['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endforeach; ?>

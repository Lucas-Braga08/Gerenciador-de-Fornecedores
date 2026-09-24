<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../fornecedores.php');
}
csrf_check();

$id = (int) ($_POST['id'] ?? 0);

$dados = [
    'razao_social'  => trim($_POST['razao_social'] ?? ''),
    'nome_fantasia' => trim($_POST['nome_fantasia'] ?? ''),
    'cnpj'          => so_digitos($_POST['cnpj'] ?? ''),
    'email'         => trim($_POST['email'] ?? ''),
    'telefone'      => so_digitos($_POST['telefone'] ?? ''),
    'cidade'        => trim($_POST['cidade'] ?? ''),
    'uf'            => strtoupper(trim($_POST['uf'] ?? '')),
    'status'        => $_POST['status'] ?? 'ativo',
];

/* ---------- Validação ---------- */
$erros = [];

if ($dados['razao_social'] === '') {
    $erros[] = 'Informe a razão social.';
}
if ($dados['nome_fantasia'] === '') {
    $erros[] = 'Informe o nome.';
}
if (!validar_cnpj($dados['cnpj'])) {
    $erros[] = 'Informe o CNPJ com 14 dígitos.';
}
if ($dados['email'] === '') {
    $erros[] = 'Informe o e-mail.';
} elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    $erros[] = 'O e-mail informado é inválido.';
}
if ($dados['telefone'] === '') {
    $erros[] = 'Informe o telefone.';
} elseif (!in_array(strlen($dados['telefone']), [10, 11], true)) {
    $erros[] = 'O telefone deve ter DDD e 8 ou 9 dígitos.';
}
if ($dados['cidade'] === '') {
    $erros[] = 'Informe a cidade.';
}
if ($dados['uf'] === '') {
    $erros[] = 'Selecione a UF.';
} elseif (!in_array($dados['uf'], UFS, true)) {
    $erros[] = 'UF inválida.';
}
if (!in_array($dados['status'], ['ativo', 'inativo'], true)) {
    $dados['status'] = 'ativo';
}

// CNPJ não pode se repetir entre fornecedores
if (!$erros) {
    $stmt = db()->prepare('SELECT id FROM fornecedores WHERE cnpj = :cnpj AND id <> :id');
    $stmt->execute([':cnpj' => $dados['cnpj'], ':id' => $id]);
    if ($stmt->fetch()) {
        $erros[] = 'Já existe um fornecedor cadastrado com esse CNPJ.';
    }
}

if ($erros) {
    foreach ($erros as $erro) {
        flash('danger', $erro);
    }
    $_SESSION['old'] = $_POST; // devolve o que foi digitado ao formulário
    redirect('../fornecedor_form.php' . ($id > 0 ? '?id=' . $id : ''));
}

/* ---------- Persistência ---------- */
// PDO: parâmetros nomeados com ":" na frente
$params = [];
foreach ($dados as $campo => $valor) {
    $params[':' . $campo] = $valor;
}

if ($id > 0) {
    $stmt = db()->prepare(
        'UPDATE fornecedores
            SET razao_social = :razao_social, nome_fantasia = :nome_fantasia, cnpj = :cnpj,
                email = :email, telefone = :telefone, cidade = :cidade, uf = :uf, status = :status
          WHERE id = :id'
    );
    $stmt->execute($params + [':id' => $id]);
    flash('success', 'Fornecedor atualizado com sucesso.');
    redirect('../fornecedor_detalhe.php?id=' . $id);
}

$stmt = db()->prepare(
    'INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj, email, telefone, cidade, uf, status)
     VALUES (:razao_social, :nome_fantasia, :cnpj, :email, :telefone, :cidade, :uf, :status)'
);
$stmt->execute($params);
flash('success', 'Fornecedor cadastrado. Agora você pode adicionar produtos e contatos.');
redirect('../fornecedor_detalhe.php?id=' . (int) db()->lastInsertId());

<?php
/**
 * Funções auxiliares compartilhadas por todas as páginas.
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const UFS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA',
    'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
];

/* ---------- Segurança e saída ---------- */

/** Escapa texto para exibição em HTML (proteção contra XSS). */
function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Gera (ou reaproveita) o token CSRF da sessão. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Campo hidden com o token, para colocar dentro dos formulários. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

/** Interrompe a requisição se o token enviado não bater com o da sessão. */
function csrf_check(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(419);
        exit('Sessão expirada. Volte à página anterior, atualize e tente novamente.');
    }
}

/* ---------- Navegação e mensagens ---------- */

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Guarda uma mensagem para ser exibida na próxima página. */
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/** Lê e limpa as mensagens guardadas. */
function get_flashes(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensagens;
}

/* ---------- Validação ---------- */

function so_digitos(?string $valor): string
{
    return preg_replace('/\D+/', '', (string) $valor);
}

/**
 * Valida o CNPJ apenas pelo formato: precisa ter 14 dígitos.
 * (Não confere os dígitos verificadores, então CNPJs fictícios são aceitos.)
 */
function validar_cnpj(string $cnpj): bool
{
    return strlen(so_digitos($cnpj)) === 14;
}

/** Converte "1.234,56" ou "12.5" em float. Retorna null se inválido. */
function parse_moeda(string $valor): ?float
{
    $valor = trim(str_replace('R$', '', $valor));
    if ($valor === '') {
        return null;
    }
    if (strpos($valor, ',') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    }
    return is_numeric($valor) ? round((float) $valor, 2) : null;
}

/* ---------- Formatação para exibição ---------- */

function formatar_cnpj(string $cnpj): string
{
    $c = so_digitos($cnpj);
    if (strlen($c) !== 14) {
        return $cnpj;
    }
    return substr($c, 0, 2) . '.' . substr($c, 2, 3) . '.' . substr($c, 5, 3)
        . '/' . substr($c, 8, 4) . '-' . substr($c, 12, 2);
}

function formatar_telefone(?string $telefone): string
{
    $t = so_digitos($telefone);
    if (strlen($t) === 11) {
        return '(' . substr($t, 0, 2) . ') ' . substr($t, 2, 5) . '-' . substr($t, 7);
    }
    if (strlen($t) === 10) {
        return '(' . substr($t, 0, 2) . ') ' . substr($t, 2, 4) . '-' . substr($t, 6);
    }
    return (string) $telefone;
}

function formatar_moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/** Valor sem "R$", para preencher campos de formulário. */
function moeda_campo($valor): string
{
    return number_format((float) $valor, 2, ',', '.');
}

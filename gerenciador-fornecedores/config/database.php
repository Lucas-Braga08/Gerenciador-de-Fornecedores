<?php
/**
 * Configuração e conexão com o MySQL (PDO).
 * Os valores padrão servem para XAMPP/WAMP/Laragon.
 * Ajuste conforme o seu ambiente ou defina variáveis de ambiente (DB_HOST, DB_NAME, DB_USER, DB_PASS).
 */

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gerenciador_fornecedores');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');

/**
 * Retorna uma única conexão PDO reutilizável durante a requisição.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Não foi possível conectar ao banco de dados. Verifique config/database.php e se o schema.sql foi importado.');
        }
    }

    return $pdo;
}

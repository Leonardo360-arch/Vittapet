<?php
declare(strict_types=1);

$local = __DIR__ . '/local.php';
if (is_file($local)) { require_once $local; }

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
    $name = defined('DB_NAME') ? DB_NAME : getenv('DB_NAME');
    $user = defined('DB_USER') ? DB_USER : getenv('DB_USER');
    $pass = defined('DB_PASS') ? DB_PASS : getenv('DB_PASS');
    if (!$host || !$name || !$user) throw new RuntimeException('Banco não configurado.');
    $pdo = new PDO(
        'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4',
        (string)$user, (string)$pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
    return $pdo;
}

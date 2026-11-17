<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_SAPI !== 'cli') {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
            'path' => '/',
        ]);
    }
    session_start();
}
require_once dirname(__DIR__) . '/config/database.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function assertCsrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Requisição inválida.');
    }
}
function clearAdminSession(): void {
    unset($_SESSION['user_id'], $_SESSION['perfil'], $_SESSION['last_admin_activity']);
}
function requireAdmin(): void {
    $id = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    $last = (int)($_SESSION['last_admin_activity'] ?? 0);
    if (!$id || ($_SESSION['perfil'] ?? null) !== 'admin' || $last < time() - 3600) {
        clearAdminSession();
        header('Location: /login.php');
        exit;
    }

    try {
        $statement = db()->prepare('SELECT perfil, ativo FROM usuarios WHERE id=? LIMIT 1');
        $statement->execute([$id]);
        $user = $statement->fetch();
        if (!$user || $user['perfil'] !== 'admin' || !(int)$user['ativo']) {
            clearAdminSession();
            header('Location: /login.php');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Falha na autorização administrativa: ' . $exception->getMessage());
        http_response_code(503);
        exit('Área administrativa temporariamente indisponível.');
    }
    $_SESSION['last_admin_activity'] = time();
}
function money($value): string {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

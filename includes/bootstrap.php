<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    if (PHP_SAPI !== 'cli') {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax','path'=>'/']);
    }
    session_start();
}
require_once dirname(__DIR__) . '/config/database.php';
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function assertCsrf(): void {
    if (!hash_equals(csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(419); exit('Requisição inválida.'); }
}
function requireAdmin(): void {
    if (($_SESSION['perfil'] ?? null) !== 'admin') { header('Location: ../login.php'); exit; }
}
function money($n): string { return 'R$ ' . number_format((float)$n, 2, ',', '.'); }

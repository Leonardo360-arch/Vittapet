<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    assertCsrf();
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['senha'] ?? '');
    $error = 'Credenciais inválidas ou acesso indisponível.';

    try {
        $pdo = db();
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $ipHash = hash('sha256', $ip);
        $identityHash = hash('sha256', $email);
        $counter = $pdo->prepare('SELECT COUNT(*) FROM login_tentativas WHERE ip_hash=? AND criado_em >= (NOW() - INTERVAL 15 MINUTE)');
        $counter->execute([$ipHash]);
        $byIp = (int)$counter->fetchColumn();

        $counter = $pdo->prepare('SELECT COUNT(*) FROM login_tentativas WHERE ip_hash=? AND identificador_hash=? AND criado_em >= (NOW() - INTERVAL 15 MINUTE)');
        $counter->execute([$ipHash, $identityHash]);
        $byLogin = (int)$counter->fetchColumn();

        if ($byIp >= 30 || $byLogin >= 8) {
            http_response_code(429);
            $error = 'Muitas tentativas de login. Tente novamente em 15 minutos.';
        } elseif (mb_strlen($email) <= 190 && mb_strlen($password) <= 4096) {
            $statement = $pdo->prepare('SELECT id,senha_hash,perfil FROM usuarios WHERE email=? AND ativo=1 LIMIT 1');
            $statement->execute([$email]);
            $user = $statement->fetch();

            if ($user && $user['perfil'] === 'admin' && password_verify($password, (string)$user['senha_hash'])) {
                $clear = $pdo->prepare('DELETE FROM login_tentativas WHERE ip_hash=? AND identificador_hash=?');
                $clear->execute([$ipHash, $identityHash]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['perfil'] = 'admin';
                $_SESSION['last_admin_activity'] = time();
                unset($_SESSION['csrf']);
                header('Location: admin/dashboard.php');
                exit;
            }

            $record = $pdo->prepare('INSERT INTO login_tentativas(ip_hash,identificador_hash) VALUES(?,?)');
            $record->execute([$ipHash, $identityHash]);
        }
    } catch (Throwable $exception) {
        error_log('Login administrativo indisponível: ' . $exception->getMessage());
        $error = 'Acesso temporariamente indisponível.';
    }
}
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — VittaPet Store</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/tokens.css" rel="stylesheet">
</head>
<body>
<main class="container" style="max-width:460px">
<div class="card mt-5 p-4">
<h1 class="h3 fw-bold">Acesso administrativo</h1>
<p class="text-muted">Área restrita da VittaPet Store</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?=e($error)?></div><?php endif; ?>
<form method="post" autocomplete="on">
<input type="hidden" name="csrf" value="<?=e(csrf())?>">
<label class="form-label" for="email">E-mail</label>
<input required type="email" id="email" name="email" autocomplete="username" class="form-control mb-3" maxlength="190">
<label class="form-label" for="senha">Senha</label>
<input required type="password" id="senha" name="senha" autocomplete="current-password" class="form-control mb-3">
<button class="btn btn-primary w-100" type="submit">Entrar</button>
</form>
<a href="index.php" class="mt-3">Voltar à loja</a>
</div></main></body></html>

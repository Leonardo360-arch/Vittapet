<?php
declare(strict_types=1);

// Provisionamento manual, execução CLI, nunca acessível via navegador.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Uso exclusivo por linha de comando.');
}

require dirname(__DIR__) . '/config/database.php';

$email = mb_strtolower(trim((string)getenv('ADMIN_EMAIL')));
$name = trim((string)(getenv('ADMIN_NAME') ?: 'Administrador'));
$password = (string)getenv('ADMIN_PASSWORD');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
    fwrite(STDERR, "ADMIN_EMAIL inválido.\n");
    exit(1);
}
if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
    fwrite(STDERR, "ADMIN_NAME inválido.\n");
    exit(1);
}
if (strlen($password) < 12) {
    fwrite(STDERR, "ADMIN_PASSWORD precisa ter ao menos 12 caracteres.\n");
    exit(1);
}

try {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $statement = db()->prepare(
        "INSERT INTO usuarios(nome,email,senha_hash,perfil,ativo)
         VALUES(?, ?, ?, 'admin', 1)
         ON DUPLICATE KEY UPDATE nome=VALUES(nome), senha_hash=VALUES(senha_hash), perfil='admin', ativo=1"
    );
    $statement->execute([$name, $email, $passwordHash]);
    fwrite(STDOUT, "Conta administrativa criada/atualizada. Credenciais não foram impressas.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, "Erro ao provisionar administrador. Confira as migrations e o banco.\n");
    exit(1);
}

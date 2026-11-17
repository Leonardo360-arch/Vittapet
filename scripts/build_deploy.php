<?php
declare(strict_types=1);

/**
 * Empacota SOMENTE arquivos necessários ao runtime web compartilhado.
 * Executar na raiz do projeto: php scripts/build_deploy.php
 * Resultado: build/htdocs/ (nunca usar diretamente em produção antes do QA).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Uso exclusivo por terminal.');
}
$root = dirname(__DIR__);
$target = $root . '/build/htdocs';
$files = [
    '.htaccess',
    'config/database.php',
    'config/.htaccess',
];
$directories = ['public', 'includes'];

function copyDeploymentFile(string $source, string $destination): void
{
    if (!is_file($source) || is_link($source)) {
        throw new RuntimeException('Arquivo ausente ou link simbólico: ' . $source);
    }
    $directory = dirname($destination);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível criar: ' . $directory);
    }
    if (!copy($source, $destination)) {
        throw new RuntimeException('Falha na cópia: ' . $source);
    }
}

function copyDeploymentDirectory(string $root, string $dir, string $target): void
{
    $source = $root . '/' . $dir;
    if (!is_dir($source)) {
        throw new RuntimeException('Diretório inexistente: ' . $dir);
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $entry) {
        if (!$entry->isFile() || $entry->isLink()) {
            throw new RuntimeException('Entrada de implantação não permitida: ' . $entry->getPathname());
        }
        $relative = substr($entry->getPathname(), strlen($root) + 1);
        if (str_contains($relative, '/.git') || preg_match('/\.(?:env|sql|log|bak|zip)$/i', $relative)) {
            throw new RuntimeException('Arquivo proibido no pacote: ' . $relative);
        }
        copyDeploymentFile($entry->getPathname(), $target . '/' . $relative);
    }
}

if (is_dir($target)) {
    throw new RuntimeException('A pasta build/htdocs já existe. Remova manualmente após conferir que não há dados importantes.');
}
foreach ($files as $path) {
    copyDeploymentFile($root . '/' . $path, $target . '/' . $path);
}
foreach ($directories as $path) {
    copyDeploymentDirectory($root, $path, $target);
}

foreach (['config/local.php','database','docs','tests','scripts','.git','.github','config/local.example.php'] as $forbidden) {
    if (file_exists($target.'/'.$forbidden)) {
        throw new RuntimeException('Arquivo de desenvolvimento entrou no pacote: ' . $forbidden);
    }
}
echo "Pacote montado em build/htdocs. Configure config/local.php somente no servidor.\n";

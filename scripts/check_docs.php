<?php
declare(strict_types=1);

/**
 * Gate de documentação para o pacote acadêmico. Apenas inspeção estática.
 * Não valida processos de negócio, APIs reais nem hospedagem.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Uso exclusivo CLI.');
}
$root = dirname(__DIR__);
$required = [
    'README.md', 'VERSION', 'CHANGELOG.md',
    'docs/ARQUITETURA_TECNICA_V1.md', 'docs/INSTALACAO.md',
    'docs/MATRIZ_TESTES_V1.md', 'docs/HOMOLOGACAO_V1.md',
    'docs/QA_REVISAO_08.md', 'docs/PLANO_POS_ENTREGA.md',
    'docs/CHECKLIST_ENTREGA_V1.md', 'docs/MANUAL_OPERACIONAL_V1.md',
    'docs/RELATORIO_LIBERACAO_V1.md', 'deploy/INFINITYFREE.md',
];
foreach ($required as $path) {
    if (!is_file($root . '/' . $path)) {
        fwrite(STDERR, "Documento obrigatório ausente: $path\n");
        exit(1);
    }
}
$version = trim((string)file_get_contents($root . '/VERSION'));
if (!preg_match('/^1\.0\.0-dev\.[0-9]+$/D', $version)) {
    fwrite(STDERR, "Versão em homologação inválida: $version\n");
    exit(1);
}

$all = 0;
foreach ($required as $path) {
    if (!str_ends_with($path, '.md')) {
        continue;
    }
    $source = (string)file_get_contents($root . '/' . $path);
    if (trim($source) === '') {
        throw new RuntimeException("Documento vazio: $path");
    }
    preg_match_all('/\]\(([^)]+)\)/', $source, $matches);
    foreach ($matches[1] as $raw) {
        $url = trim((string)$raw, " \t\r\n\"'");
        $url = explode('#', $url, 2)[0];
        if ($url === '' || preg_match('/^(?:https?:|mailto:)/i', $url)) {
            continue;
        }
        $target = dirname($root . '/' . $path) . '/' . rawurldecode($url);
        if (!is_file($target) && !is_dir($target)) {
            throw new RuntimeException("Link local quebrado em $path: $url");
        }
        $all++;
    }
}
foreach (['001_store_base.sql','002_pedidos.sql','003_financeiro.sql',
          '004_localizacao_loja.sql','005_pagamentos_whatsapp.sql',
          '006_login_tentativas.sql','007_frete_fixo.sql',
          '008_cobertura_cep.sql','009_auditoria_pagamentos.sql'] as $migration) {
    if (!is_file($root . '/database/migrations/' . $migration)) {
        throw new RuntimeException('Migration não localizada: ' . $migration);
    }
}
foreach (['config/local.php','.env','build/htdocs/config/local.php'] as $private) {
    if (is_file($root . '/' . $private)) {
        throw new RuntimeException('Arquivo privado no workspace do CI: ' . $private);
    }
}
echo "DOCUMENTAÇÃO: arquivos, $all links locais, VERSION e 9 migrations conferidos.\n";

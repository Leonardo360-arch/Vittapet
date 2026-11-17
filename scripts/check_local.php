<?php
declare(strict_types=1);

/**
 * Diagnóstico CLI local não destrutivo (sem revelar senha).
 * docker compose -f compose.local.yaml exec -T app php scripts/check_local.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Apenas por terminal.');
}
require dirname(__DIR__) . '/config/database.php';
try {
    $pdo = db();
    echo "MySQL: conexão OK; versão " . $pdo->query('SELECT VERSION()')->fetchColumn() . PHP_EOL;

    $tables = ['usuarios','categorias','produtos','clientes','pedidos','pedido_itens',
        'pedido_historico','movimentacoes_estoque','despesas','configuracoes_loja',
        'login_tentativas','consultas_rastreio'];
    foreach ($tables as $table) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
        $exists->execute([$table]);
        if ((int)$exists->fetchColumn() !== 1) {
            throw new RuntimeException('Tabela ausente: ' . $table);
        }
    }

    $columns = [
        ['configuracoes_loja', 'frete_fixo'],
        ['configuracoes_loja', 'cep_prefixos_entrega'],
        ['configuracoes_loja', 'whatsapp'],
        ['pedidos', 'pago_em'],
        ['pedidos', 'estornado_em'],
    ];
    foreach ($columns as [$table, $column]) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
        $check->execute([$table, $column]);
        if ((int)$check->fetchColumn() !== 1) {
            throw new RuntimeException("Migration ausente: $table.$column");
        }
    }

    $admin = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin' AND ativo = 1")->fetchColumn();
    $products = (int)$pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
    $config = $pdo->query("SELECT frete_fixo, cep_prefixos_entrega, whatsapp FROM configuracoes_loja WHERE id=1")->fetch();

    echo "Esquema: migrations 001-009 verificadas pelas tabelas/colunas essenciais." . PHP_EOL;
    echo "Administradores ativos: $admin" . PHP_EOL;
    echo "Produtos ativos: $products" . PHP_EOL;
    echo "Frete: " . (($config['frete_fixo'] ?? null) === null ? 'NÃO CONFIGURADO' : 'configurado') . PHP_EOL;
    echo "Cobertura CEP: " . (empty($config['cep_prefixos_entrega']) ? 'NÃO CONFIGURADA' : 'configurada') . PHP_EOL;
    echo "WhatsApp comercial: " . (empty($config['whatsapp']) ? 'NÃO CONFIGURADO' : 'configurado') . PHP_EOL;
    echo "Diagnóstico OK. O teste não efetuou compras nem modificou o banco." . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, "Falha no diagnóstico: " . $exception->getMessage() . PHP_EOL);
    exit(1);
}

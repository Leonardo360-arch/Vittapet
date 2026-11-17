<?php
declare(strict_types=1);

/**
 * Dados DEMONSTRATIVOS opt-in. Não apaga dados e não roda em produção.
 * Uso: docker compose -f compose.local.yaml exec -T app php scripts/seed_local.php
 */
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'local') {
    fwrite(STDERR, "Apenas no ambiente APP_ENV=local, por terminal.\n");
    exit(1);
}
require dirname(__DIR__) . '/config/database.php';

try {
    $pdo = db();
    $existing = (int)$pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
    if ($existing > 0) {
        echo "Há $existing produtos cadastrados. Nenhum dado foi modificado.\n";
        exit(0);
    }
    $catalog = [
        ['Rações','DEMO - Ração para cães 1kg','Produto fictício para teste local','49.90','30.00',8],
        ['Brinquedos','DEMO - Bola de borracha','Produto fictício para teste local','19.90','8.00',12],
        ['Acessórios','DEMO - Coleira ajustável','Produto fictício para teste local','29.90','14.00',7],
        ['Higiene','DEMO - Shampoo pet 250ml','Produto fictício para teste local','24.90','11.00',10],
    ];

    $pdo->beginTransaction();
    $categoryQuery = $pdo->prepare('SELECT id FROM categorias WHERE nome=? AND ativo=1 LIMIT 1');
    $insert = $pdo->prepare('INSERT INTO produtos(categoria_id,nome,descricao,preco_venda,custo_unitario,estoque_atual,estoque_minimo,ativo) VALUES(?,?,?,?,?,?,2,1)');
    foreach ($catalog as [$category,$name,$description,$price,$cost,$stock]) {
        $categoryQuery->execute([$category]);
        $categoryId = $categoryQuery->fetchColumn();
        if (!$categoryId) {
            throw new RuntimeException("Categoria de exemplo ausente: $category");
        }
        $insert->execute([$categoryId,$name,$description,$price,$cost,$stock]);
    }
    $pdo->commit();
    echo "Quatro produtos DEMO criados. Não representam catálogo real.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Falha no seed local: ".$exception->getMessage()."\n");
    exit(1);
}

<?php
declare(strict_types=1);

/**
 * Teste de concorrência real entre dois processos PHP e duas conexões PDO
 * contra o mesmo registro InnoDB. Nunca execute em banco de produção.
 *
 * Em CI, PHP_BIN inicia dois workers. Eles aguardam a barreira antes de
 * disputar a última unidade, com SELECT ... FOR UPDATE.
 */
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/order_inventory.php';

$worker = ($argv[1] ?? '') === '--worker';

if ($worker) {
    $productId = (int)($argv[2] ?? 0);
    $clientId = (int)($argv[3] ?? 0);
    $sync = (string)($argv[4] ?? '');
    $label = (string)($argv[5] ?? '');
    file_put_contents($sync . '/ready-' . $label, 'ready');
    $deadline = microtime(true) + 10;
    while (!is_file($sync . '/go') && microtime(true) < $deadline) {
        usleep(10000);
    }
    if (!is_file($sync . '/go')) {
        fwrite(STDERR, "Barreira de concorrência expirou\n");
        exit(2);
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();
        $locked = lockCheckoutInventory($pdo, [$productId => 1]);
        // Amplia a janela de sobreposição sem liberar o lock InnoDB.
        usleep(350000);
        $token = 'VP-26-' . strtoupper(bin2hex(random_bytes(8)));
        $insert = $pdo->prepare(
            "INSERT INTO pedidos(
                cliente_id,token_rastreio,endereco_cep,endereco_logradouro,
                endereco_numero,endereco_bairro,endereco_cidade,endereco_uf,
                subtotal,frete,total,metodo_pagamento,status_pagamento,status_logistico
             ) VALUES(?,?,'01001000','Rua de Testes','1','Centro','São Paulo',
                      'SP',?,'0.00',?,'pix_manual','pendente','aguardando_pagamento')"
        );
        $amount = number_format($locked['subtotalCents'] / 100, 2, '.', '');
        $insert->execute([$clientId, $token, $amount, $amount]);
        $orderId = (int)$pdo->lastInsertId();
        persistCheckoutInventory($pdo, $orderId, $locked['products']);
        $pdo->commit();
        echo "COMMIT pedido " . $orderId . "\n";
        exit(0);
    } catch (InvalidArgumentException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "REJEITADO por estoque insuficiente\n";
        exit(10);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "ERRO: " . $exception->getMessage() . "\n");
        exit(2);
    }
}

if (PHP_SAPI !== 'cli' || !function_exists('proc_open')) {
    fwrite(STDERR, "Exige PHP CLI com proc_open.\n");
    exit(1);
}
$pdo = db();
$categoryId = (int)$pdo->query('SELECT id FROM categorias ORDER BY id LIMIT 1')->fetchColumn();
if ($categoryId < 1) {
    throw new RuntimeException('Importe primeiro as migrations.');
}
$pdo->prepare("INSERT INTO clientes(nome,email,telefone) VALUES('Concorrência QA','qa-stock@example.invalid','11999999999')")->execute();
$clientId = (int)$pdo->lastInsertId();
$pdo->prepare("INSERT INTO produtos(categoria_id,nome,preco_venda,custo_unitario,estoque_atual,estoque_minimo,ativo)
 VALUES(?,'Produto exclusivo concorrência','49.90','20.00',1,0,1)")->execute([$categoryId]);
$productId = (int)$pdo->lastInsertId();

$sync = sys_get_temp_dir() . '/vittapet-stock-' . bin2hex(random_bytes(6));
if (!mkdir($sync, 0700)) {
    throw new RuntimeException('Não foi possível criar sincronização.');
}
$jobs = [];
try {
    foreach (['a', 'b'] as $label) {
        $command = array_map('escapeshellarg', [
            PHP_BINARY, __FILE__, '--worker', (string)$productId,
            (string)$clientId, $sync, $label,
        ]);
        $process = proc_open(implode(' ', $command), [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Não foi possível iniciar dois workers.');
        }
        fclose($pipes[0]);
        $jobs[] = ['process' => $process, 'out' => $pipes[1], 'err' => $pipes[2]];
    }
    $deadline = microtime(true) + 10;
    while ((!file_exists($sync.'/ready-a') || !file_exists($sync.'/ready-b')) &&
           microtime(true) < $deadline) {
        usleep(20000);
    }
    if (!file_exists($sync.'/ready-a') || !file_exists($sync.'/ready-b')) {
        throw new RuntimeException('Workers não atingiram a barreira.');
    }
    touch($sync.'/go');

    $exitCodes = [];
    foreach ($jobs as $job) {
        $stdout = stream_get_contents($job['out']);
        $stderr = stream_get_contents($job['err']);
        fclose($job['out']);
        fclose($job['err']);
        $exitCodes[] = proc_close($job['process']);
        echo trim($stdout . $stderr) . "\n";
    }
    sort($exitCodes);
    if ($exitCodes !== [0, 10]) {
        throw new RuntimeException('Esperava exatamente 1 commit e 1 rejeição, recebeu: '.implode(',', $exitCodes));
    }

    $statement = $pdo->prepare('SELECT estoque_atual FROM produtos WHERE id=?');
    $statement->execute([$productId]);
    $stock = (int)$statement->fetchColumn();
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM pedido_itens WHERE produto_id=?"
    );
    $statement->execute([$productId]);
    $items = (int)$statement->fetchColumn();
    $statement = $pdo->prepare(
        "SELECT COUNT(*) FROM movimentacoes_estoque WHERE produto_id=? AND tipo='saida_venda'"
    );
    $statement->execute([$productId]);
    $movements = (int)$statement->fetchColumn();

    if ($stock !== 0 || $items !== 1 || $movements !== 1) {
        throw new RuntimeException("Invariante falhou: estoque=$stock, itens=$items, movimentos=$movements");
    }
    echo "SUCESSO: concorrência real — 1 venda, 1 rejeição, estoque 0, 1 item e 1 movimento.\n";
} finally {
    if (is_dir($sync)) {
        foreach (glob($sync . '/*') ?: [] as $entry) {
            unlink($entry);
        }
        rmdir($sync);
    }
}

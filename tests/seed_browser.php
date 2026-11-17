<?php
declare(strict_types=1);

/** Cria pedido Pix somente no MySQL efêmero do CI, nunca no ambiente real. */
if (PHP_SAPI !== 'cli' || getenv('TEST_BASE_URL') === false) {
    fwrite(STDERR, "Exclusivo do ambiente de homologação CLI.\n");
    exit(1);
}
require dirname(__DIR__) . '/config/database.php';
require dirname(__DIR__) . '/includes/order_inventory.php';
$pdo = db();
try {
    $pdo->beginTransaction();
    $category = (int)$pdo->query('SELECT id FROM categorias LIMIT 1')->fetchColumn();
    $pdo->prepare("INSERT INTO produtos(categoria_id,nome,preco_venda,custo_unitario,estoque_atual,estoque_minimo)
                   VALUES(?,'Produto QA Pix browser','49.90','20.00',2,0)")
        ->execute([$category]);
    $productId = (int)$pdo->lastInsertId();
    $pdo->exec("INSERT INTO clientes(nome,email,telefone) VALUES('Cliente QA Browser','browser@example.invalid','11999999999')");
    $clientId = (int)$pdo->lastInsertId();
    $locked = lockCheckoutInventory($pdo, [$productId=>1]);
    $token = 'VP-' . date('y') . '-' . strtoupper(bin2hex(random_bytes(8)));
    $pdo->prepare("INSERT INTO pedidos(cliente_id,token_rastreio,endereco_cep,endereco_logradouro,
                  endereco_numero,endereco_bairro,endereco_cidade,endereco_uf,subtotal,frete,total,
                  metodo_pagamento,status_pagamento,status_logistico)
                  VALUES(?,?,'01001000','Rua QA','1','Sé','São Paulo','SP','49.90','8.50',
                  '58.40','pix_manual','pendente','aguardando_pagamento')")
        ->execute([$clientId,$token]);
    $orderId = (int)$pdo->lastInsertId();
    persistCheckoutInventory($pdo,$orderId,$locked['products']);
    $pdo->prepare("INSERT INTO pedido_historico(pedido_id,status_anterior,status_novo)
                   VALUES (?,NULL,'aguardando_pagamento')")->execute([$orderId]);
    $pdo->commit();
    echo $token . "\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Erro ao criar pedido QA browser: " . $exception->getMessage() . "\n");
    exit(1);
}

<?php
declare(strict_types=1);

/**
 * Isolamento das regras de estoque do checkout.
 * Deve ser chamado dentro de uma transação InnoDB já iniciada.
 *
 * @return array{products: array<int,array<string,mixed>>, subtotalCents:int}
 */
function lockCheckoutInventory(PDO $pdo, array $cart): array
{
    if ($cart === [] || count($cart) > 100) {
        throw new InvalidArgumentException('Carrinho inválido.');
    }

    $ids = [];
    foreach ($cart as $productId => $quantity) {
        $id = filter_var($productId, FILTER_VALIDATE_INT);
        if (!$id || (int)$quantity < 1 || (int)$quantity > 99) {
            throw new InvalidArgumentException('Quantidade inválida no carrinho.');
        }
        $ids[] = (int)$id;
    }
    sort($ids, SORT_NUMERIC);

    // Ordem consistente de bloqueio reduz a chance de deadlocks em compras multiproduto.
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $statement = $pdo->prepare(
        "SELECT id,preco_venda,custo_unitario,estoque_atual,ativo
         FROM produtos WHERE id IN ($placeholders) ORDER BY id FOR UPDATE"
    );
    $statement->execute($ids);
    $products = $statement->fetchAll();

    if (count($products) !== count($ids)) {
        throw new InvalidArgumentException('Algum produto não está mais disponível.');
    }

    $subtotalCents = 0;
    foreach ($products as &$product) {
        $id = (int)$product['id'];
        $quantity = (int)($cart[$id] ?? 0);
        if ($quantity < 1 || $quantity > 99 ||
            !(int)$product['ativo'] || $quantity > (int)$product['estoque_atual']) {
            throw new InvalidArgumentException('Confira a disponibilidade dos produtos.');
        }
        $product['qty'] = $quantity;
        $product['price_cents'] = (int)round((float)$product['preco_venda'] * 100);
        $subtotalCents += $product['price_cents'] * $quantity;
    }
    unset($product);

    if ($subtotalCents < 1) {
        throw new InvalidArgumentException('O total do pedido deve ser maior que zero.');
    }

    return ['products' => $products, 'subtotalCents' => $subtotalCents];
}

/**
 * Insere itens e movimentos e decrementa estoque, mantendo a mesma transação.
 * Uma falha faz o controlador reverter todos os lançamentos.
 *
 * @param array<int,array<string,mixed>> $products Produtos previamente bloqueados
 */
function persistCheckoutInventory(PDO $pdo, int $orderId, array $products): void
{
    $insert = $pdo->prepare(
        'INSERT INTO pedido_itens(pedido_id,produto_id,quantidade,preco_unitario,custo_unitario)
         VALUES(?,?,?,?,?)'
    );
    $decrement = $pdo->prepare(
        'UPDATE produtos SET estoque_atual=estoque_atual-? WHERE id=? AND estoque_atual>=?'
    );
    $movement = $pdo->prepare(
        "INSERT INTO movimentacoes_estoque(produto_id,pedido_id,tipo,quantidade)
         VALUES(?,?,'saida_venda',?)"
    );

    foreach ($products as $product) {
        $quantity = (int)$product['qty'];
        $id = (int)$product['id'];
        $insert->execute([$orderId, $id, $quantity, $product['preco_venda'], $product['custo_unitario']]);
        $decrement->execute([$quantity, $id, $quantity]);
        if ($decrement->rowCount() !== 1) {
            throw new RuntimeException('Estoque inconsistente.');
        }
        $movement->execute([$id, $orderId, -$quantity]);
    }
}

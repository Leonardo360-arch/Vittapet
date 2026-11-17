<?php
declare(strict_types=1);

/**
 * Catálogo de DEMONSTRAÇÃO para testes visuais e operacionais da VittaPet.
 * Não executa automaticamente; não remove nem atualiza produtos existentes.
 * Uso: docker compose -f compose.local.yaml exec -T app php scripts/seed_local.php
 *
 * Para retirar depois: painel > Produtos > Excluir, um por um.
 */
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'local') {
    fwrite(STDERR, "Apenas PHP CLI no APP_ENV=local.\n");
    exit(1);
}

require dirname(__DIR__) . '/config/database.php';

// Valores, descrições e produtos são fictícios, apenas para homologação localhost.
$catalog = [
    ['Rações','Ração para cães adultos 1 kg','Alimento seco para demonstração de catálogo.','42.90','24.00',12],
    ['Rações','Ração para cães filhotes 2 kg','Alimento seco fictício para filhotes.','83.50','48.00',9],
    ['Rações','Ração para gatos adultos 1 kg','Alimento fictício para gatos adultos.','47.90','27.00',15],
    ['Rações','Ração para gatos filhotes 500 g','Embalagem fictícia para testes de checkout.','29.90','16.00',7],
    ['Rações','Petisco crocante para cães 150 g','Petisco demonstrativo para cães.','16.90','8.50',23],
    ['Rações','Petisco macio para gatos 80 g','Petisco demonstrativo para gatos.','14.90','7.50',13],
    ['Brinquedos','Bola interativa para cães','Brinquedo fictício para atividade e lazer.','22.90','10.00',18],
    ['Brinquedos','Corda com nós para mordida','Corda demonstrativa para brincadeiras.','27.50','12.00',8],
    ['Brinquedos','Ratinho de pelúcia para gatos','Brinquedo leve para testar a vitrine.','18.90','8.20',16],
    ['Brinquedos','Varinha com penas para gatos','Brinquedo demonstrativo para interação.','25.90','11.00',10],
    ['Brinquedos','Mordedor formato osso','Mordedor fictício para cães.','21.50','9.80',0],
    ['Acessórios','Coleira ajustável tamanho P','Acessório demonstrativo com regulagem.','29.90','13.00',14],
    ['Acessórios','Coleira ajustável tamanho G','Acessório demonstrativo para cães grandes.','34.90','16.00',8],
    ['Acessórios','Guia para passeio 1,5 m','Guia fictícia para passeio diário.','39.90','18.00',11],
    ['Acessórios','Comedouro inox 500 ml','Recipiente fictício para alimentação.','32.90','15.00',17],
    ['Acessórios','Caminha acolchoada tamanho M','Caminha fictícia para testes do carrinho.','119.90','68.00',4],
    ['Higiene','Shampoo suave para cães 250 ml','Shampoo fictício para demonstração.','24.90','11.00',17],
    ['Higiene','Condicionador pet 250 ml','Condicionador demonstrativo para cuidados.','26.90','12.20',11],
    ['Higiene','Escova para pelos macia','Escova demonstrativa para cuidados diários.','28.90','13.50',9],
    ['Higiene','Tapete higiênico 10 unidades','Pacote fictício para teste de estoque.','36.90','18.00',21],
];

try {
    $pdo = db();
    $pdo->beginTransaction();

    $findCategory = $pdo->prepare('SELECT id FROM categorias WHERE nome=? AND ativo=1 LIMIT 1');
    $insertCategory = $pdo->prepare(
        'INSERT IGNORE INTO categorias(nome,descricao,ativo) VALUES(?, ?, 1)'
    );
    $findProduct = $pdo->prepare('SELECT id FROM produtos WHERE nome=? LIMIT 1');
    $insertProduct = $pdo->prepare(
        'INSERT INTO produtos(categoria_id,nome,descricao,preco_venda,custo_unitario,estoque_atual,estoque_minimo,ativo)
         VALUES(?,?,?,?,?,?,2,1)'
    );

    $created = 0;
    $skipped = 0;
    foreach ($catalog as [$category, $name, $description, $price, $cost, $stock]) {
        $demoName = 'DEMO - ' . $name;
        $findProduct->execute([$demoName]);
        if ($findProduct->fetchColumn()) {
            $skipped++;
            continue; // Mesmo produtos DEMO desativados não são recriados.
        }

        $findCategory->execute([$category]);
        $categoryId = $findCategory->fetchColumn();
        if (!$categoryId) {
            $insertCategory->execute([$category, 'Categoria de demonstração local']);
            $findCategory->execute([$category]);
            $categoryId = $findCategory->fetchColumn();
        }
        if (!$categoryId) {
            throw new RuntimeException('Categoria não disponível: ' . $category);
        }

        $insertProduct->execute([
            (int)$categoryId, $demoName, $description, $price, $cost, $stock,
        ]);
        $created++;
    }

    $pdo->commit();
    echo "Catálogo DEMO local: $created produtos adicionados; $skipped já existentes preservados.\n";
    echo "Nenhum produto real foi modificado. Remova os DEMO manualmente no painel Produtos.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Falha na criação do catálogo DEMO: " . $exception->getMessage() . "\n");
    exit(1);
}

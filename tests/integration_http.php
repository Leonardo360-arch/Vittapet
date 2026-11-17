<?php
declare(strict_types=1);

/**
 * Suite de integração HTTP contra PHP -S + MySQL 8 isolado.
 * Não usa credenciais reais, não chama APIs externas e não acessa InfinityFree.
 *
 * Pré-requisitos: DB_HOST, DB_NAME, DB_USER, DB_PASS, ADMIN_EMAIL,
 * ADMIN_PASSWORD, TEST_BASE_URL e banco preparado via migrations 001–006.
 */
if (PHP_SAPI !== 'cli' || !extension_loaded('curl')) {
    fwrite(STDERR, "O teste exige PHP CLI e extensão curl.\n");
    exit(1);
}
require dirname(__DIR__) . '/config/database.php';

$base = rtrim((string)(getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8000'), '/');
$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FALHOU: ' . $message);
    }
    $checks++;
    echo "OK [$checks] $message\n";
}
function http(string $path, array $data = [], string $jar = 'visitor', bool $json = false, bool $post = false): array {
    global $base;
    $cookieFile = sys_get_temp_dir() . '/vp-ci-' . $jar . '.cookie';
    $curl = curl_init($base . $path);
    $headers = ['Accept: ' . ($json ? 'application/json' : 'text/html')];
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_COOKIEJAR => $cookieFile,
    ]);
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $response = curl_exec($curl);
    if ($response === false) {
        throw new RuntimeException('Erro de HTTP: ' . curl_error($curl));
    }
    $code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = (int)curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $head = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    curl_close($curl);
    preg_match('/^Location:\\s*(.+)$/mi', $head, $match);
    return ['status' => $code, 'body' => $body, 'location' => trim($match[1] ?? '')];
}
function field(string $html, string $name): string {
    if (!preg_match('/name="' . preg_quote($name, '/') . '" value="([^"]*)"/', $html, $matches)) {
        throw new RuntimeException('Campo ausente: ' . $name);
    }
    return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function orderByToken(PDO $pdo, string $token): array {
    $statement = $pdo->prepare('SELECT * FROM pedidos WHERE token_rastreio=?');
    $statement->execute([$token]);
    return $statement->fetch() ?: throw new RuntimeException('Pedido ausente: ' . $token);
}
function placeOrder(string $method, string $jar = 'visitor'): string {
    $page = http('/checkout.php', [], $jar);
    check($page['status'] === 200, 'Checkout GET acessível');
    $data = [
        'csrf' => field($page['body'], 'csrf'),
        'nonce' => field($page['body'], 'nonce'),
        'nome' => 'Cliente Teste',
        'email' => 'cliente-teste@example.invalid',
        'telefone' => '11999999999',
        'cep' => '01001000',
        'logradouro' => 'Rua de Testes',
        'numero' => '100',
        'complemento' => '',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'uf' => 'SP',
        'metodo' => $method,
    ];
    $response = http('/checkout.php', $data, $jar, false, true);
    check($response['status'] === 302 && str_contains($response['location'], 'rastrear.php?token='), 'Checkout gera redirecionamento de pedido');
    parse_str((string)parse_url($response['location'], PHP_URL_QUERY), $query);
    $token = (string)($query['token'] ?? '');
    check((bool)preg_match('/^VP-[0-9]{2}-[A-F0-9]{16}$/', $token), 'Token de rastreio forte e formatado');
    return $token;
}

try {
    $pdo = db();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    check((int)$pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn() >= 4, 'Migration 001: categorias presentes');
    foreach (['pedidos', 'pedido_itens', 'pedido_historico', 'despesas', 'configuracoes_loja', 'login_tentativas', 'consultas_rastreio'] as $table) {
        check((bool)$pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetchColumn(), "Migration: tabela $table presente");
    }
    $pdo->exec("UPDATE configuracoes_loja SET whatsapp='5511999999999' WHERE id=1");
    $cat = (int)$pdo->query('SELECT id FROM categorias ORDER BY id LIMIT 1')->fetchColumn();
    $seed = $pdo->prepare('INSERT INTO produtos(categoria_id,nome,descricao,preco_venda,custo_unitario,estoque_atual,estoque_minimo) VALUES(?,?,?,?,?,?,?)');
    $seed->execute([$cat, 'Ração de teste QA', 'Produto isolado do CI', '49.90', '25.00', 10, 2]);
    $productId = (int)$pdo->lastInsertId();

    $home = http('/index.php');
    check($home['status'] === 200 && str_contains($home['body'], 'Ração de teste QA'), 'Home exibe produto persistido');
    check(http('/admin/produtos.php', [], 'admin')['status'] === 302, 'Admin protegido sem login');

    $loginPage = http('/login.php', [], 'admin');
    check($loginPage['status'] === 200, 'Formulário login acessível');
    $csrfAdmin = field($loginPage['body'], 'csrf');
    $wrong = http('/login.php', ['csrf'=>$csrfAdmin, 'email'=>(string)getenv('ADMIN_EMAIL'), 'senha'=>'senha errada'], 'admin', false, true);
    check($wrong['status'] === 200 && str_contains($wrong['body'], 'Credenciais inválidas'), 'Senha inválida rejeitada');
    $valid = http('/login.php', ['csrf'=>$csrfAdmin, 'email'=>(string)getenv('ADMIN_EMAIL'), 'senha'=>(string)getenv('ADMIN_PASSWORD')], 'admin', false, true);
    check($valid['status'] === 302 && str_contains($valid['location'], 'dashboard.php'), 'Login válido cria sessão administrativa');
    $panel = http('/admin/produtos.php', [], 'admin');
    check($panel['status'] === 200, 'CRUD administrativo acessível após login');
    $adminCsrf = field($panel['body'], 'csrf');
    $new = [
        'csrf'=>$adminCsrf, 'action'=>'create','nome'=>'Brinquedo QA',
        'categoria_id'=>$cat, 'descricao'=>'Cadastro teste','preco_venda'=>'29.90',
        'custo_unitario'=>'12.50','estoque_atual'=>4,'estoque_minimo'=>1,
    ];
    http('/admin/produtos.php', $new, 'admin', false, true);
    $created = (int)$pdo->query("SELECT id FROM produtos WHERE nome='Brinquedo QA' AND ativo=1")->fetchColumn();
    check($created > 0, 'CRUD: cria produto com persistência SQL');
    $new['action']='update';$new['id']=$created;$new['nome']='Brinquedo QA Editado';$new['preco_venda']='31.90';
    http('/admin/produtos.php', $new, 'admin', false, true);
    check((string)$pdo->query("SELECT preco_venda FROM produtos WHERE id=$created")->fetchColumn()==='31.90', 'CRUD: edita preço persistido');
    http('/admin/produtos.php', ['csrf'=>$adminCsrf,'action'=>'delete','id'=>$created], 'admin', false, true);
    check((int)$pdo->query("SELECT ativo FROM produtos WHERE id=$created")->fetchColumn()===0, 'CRUD: exclusão lógica não destrói cadastro');

    $cartPage = http('/carrinho.php');
    $cartCsrf = field($cartPage['body'], 'csrf');
    $add = http('/carrinho.php', ['csrf'=>$cartCsrf,'id'=>$productId,'quantity'=>1,'action'=>'add'], 'visitor', false, true);
    check($add['status'] === 302, 'Carrinho: inclusão de produto');
    $changed = http('/carrinho.php', ['csrf'=>$cartCsrf,'id'=>$productId,'quantity'=>2], 'visitor', true, true);
    $data = json_decode($changed['body'], true, 512, JSON_THROW_ON_ERROR);
    check($changed['status']===200 && $data['count']===2 && abs($data['subtotal']-99.80)<0.001, 'Carrinho: atualização dinâmica para duas unidades');
    $invalid = http('/carrinho.php', ['csrf'=>$cartCsrf,'id'=>$productId,'quantity'=>999], 'visitor', true, true);
    check($invalid['status']===422, 'Carrinho: quantidade acima do limite recusada');
    $remove = http('/carrinho.php', ['csrf'=>$cartCsrf,'id'=>$productId,'quantity'=>0], 'visitor', true, true);
    $data=json_decode($remove['body'],true,512,JSON_THROW_ON_ERROR);
    check($data['count']===0 && $data['subtotal']===0, 'Carrinho: remove produto e zera subtotal');
    http('/carrinho.php', ['csrf'=>$cartCsrf,'id'=>$productId,'quantity'=>1,'action'=>'add'], 'visitor', false, true);

    $pixToken=placeOrder('pix_manual');
    $pix=orderByToken($pdo,$pixToken);
    check($pix['status_pagamento']==='pendente' && $pix['status_logistico']==='aguardando_pagamento', 'Pix inicia aguardando confirmação');
    check((int)$pdo->query("SELECT estoque_atual FROM produtos WHERE id=$productId")->fetchColumn()===9, 'Checkout atualiza estoque de forma transacional');
    $track=http('/rastrear.php?token='.urlencode($pixToken));
    check($track['status']===200 && str_contains($track['body'],'https://wa.me/5511999999999'), 'Rastreio exibe WhatsApp comercial configurado');
    check(str_contains($track['body'], urlencode($pixToken)), 'Mensagem WhatsApp inclui o código do pedido');
    $confirm=http('/admin/pedidos.php', ['csrf'=>$adminCsrf,'id'=>$pix['id'],'acao'=>'confirmar_pagamento'], 'admin', false, true);
    check($confirm['status']===200, 'Admin aceita confirmação manual de Pix');
    $pix=orderByToken($pdo,$pixToken);
    check($pix['status_pagamento']==='confirmado' && $pix['status_logistico']==='em_separacao', 'Pix confirmado avança automaticamente para separação');

    $cartAgain=http('/carrinho.php', [], 'cardbuyer');
    $cardCsrf=field($cartAgain['body'],'csrf');
    http('/carrinho.php', ['csrf'=>$cardCsrf,'id'=>$productId,'quantity'=>1,'action'=>'add'], 'cardbuyer', false, true);
    $cardToken=placeOrder('debito_entrega','cardbuyer');
    $card=orderByToken($pdo,$cardToken);
    check($card['status_pagamento']==='a_receber_na_entrega' && $card['status_logistico']==='em_separacao', 'Débito na entrega inicia em separação, sem pagamento confirmado');
    $premature=http('/admin/pedidos.php', ['csrf'=>$adminCsrf,'id'=>$card['id'],'acao'=>'confirmar_pagamento'], 'admin', false, true);
    check(str_contains($premature['body'],'só pode ser confirmado após a entrega'), 'Bloqueio: cartão não confirma antes de entregue');
    foreach(['embalado','saiu_para_entrega','entregue'] as $step) {
        http('/admin/pedidos.php', ['csrf'=>$adminCsrf,'id'=>$card['id'],'acao'=>'logistica','status'=>$step], 'admin', false, true);
        check(orderByToken($pdo,$cardToken)['status_logistico']===$step, "Logística: transição $step");
    }
    http('/admin/pedidos.php', ['csrf'=>$adminCsrf,'id'=>$card['id'],'acao'=>'confirmar_pagamento'], 'admin', false, true);
    check(orderByToken($pdo,$cardToken)['status_pagamento']==='confirmado', 'Cartão confirmado apenas após entrega');
    $history = $pdo->prepare('SELECT COUNT(*) FROM pedido_historico WHERE pedido_id=?');
    $history->execute([$card['id']]);
    check((int)$history->fetchColumn()===4, 'Histórico grava criação e três movimentações logísticas');

    $financial=http('/admin/financeiro.php', [], 'admin');
    check($financial['status']===200, 'Controle financeiro acessível');
    $expense=http('/admin/financeiro.php', ['csrf'=>$adminCsrf,'descricao'=>'Despesa CI','categoria'=>'Operacional','valor'=>'5.50','data_despesa'=>date('Y-m-d')], 'admin', false, true);
    check($expense['status']===302, 'Despesa criada via painel');
    check((float)$pdo->query("SELECT valor FROM despesas WHERE descricao='Despesa CI'")->fetchColumn()===5.5, 'Despesa persistida no MySQL');
    $dashboard=http('/admin/dashboard.php', [], 'admin');
    check($dashboard['status']===200 && str_contains($dashboard['body'],'Visão geral'), 'Dashboard abre após pedidos e financeiro');

    $logoutGet=http('/logout.php', [], 'admin');
    check($logoutGet['status']===405, 'Logout rejeita GET');
    $logoutPost=http('/logout.php', ['csrf'=>$adminCsrf], 'admin', false, true);
    check($logoutPost['status']===302, 'Logout via POST e CSRF');
    check(http('/admin/dashboard.php', [], 'admin')['status']===302, 'Sessão encerrada não acessa o painel');

    echo "\nSUCESSO: $checks asserções HTTP + MySQL executadas. APIs externas, concorrência e InfinityFree permanecem fora desta suíte.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "INTEGRAÇÃO REPROVADA: " . $exception->getMessage() . "\n");
    exit(1);
}

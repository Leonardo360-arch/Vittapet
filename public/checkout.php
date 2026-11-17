<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$error = null;
$successToken = null;
$cart = $_SESSION['cart'] ?? [];
$checkoutNonce = $_SESSION['checkout_nonce'] ??= bin2hex(random_bytes(16));
$values = ['nome'=>'','email'=>'','telefone'=>'','cep'=>'','logradouro'=>'','numero'=>'','complemento'=>'','bairro'=>'','cidade'=>'','uf'=>'','metodo'=>'pix_manual'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = null;
    try {
        assertCsrf();
        if (!hash_equals((string)($_SESSION['checkout_nonce'] ?? ''), (string)($_POST['nonce'] ?? ''))) {
            throw new InvalidArgumentException('Este pedido já foi enviado ou o formulário expirou.');
        }
        foreach ($values as $key => $v) $values[$key] = trim((string)($_POST[$key] ?? $v));
        if (mb_strlen($values['nome']) < 3 || mb_strlen($values['nome']) > 120
            || !filter_var($values['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($values['telefone']) < 8
            || !preg_match('/^\d{8}$/D', preg_replace('/\D/', '', $values['cep']))
            || $values['logradouro'] === '' || $values['numero'] === '' || $values['bairro'] === ''
            || $values['cidade'] === '' || !preg_match('/^[A-Z]{2}$/D', strtoupper($values['uf']))
            || !in_array($values['metodo'], ['pix_manual','debito_entrega','credito_entrega'],true)) {
            throw new InvalidArgumentException('Confira os dados de contato, endereço e pagamento.');
        }
        $lengths=['telefone'=>30,'logradouro'=>190,'numero'=>20,'complemento'=>100,'bairro'=>120,'cidade'=>120];
        foreach($lengths as $k=>$max) if(mb_strlen($values[$k])>$max) throw new InvalidArgumentException('Campo muito longo: '.$k);
        if (!$cart) throw new InvalidArgumentException('O carrinho está vazio.');
        if (count($cart) > 100) throw new InvalidArgumentException('Carrinho excedeu o limite.');

        $pdo = db();
        $pdo->beginTransaction();
        $ids = array_map('intval',array_keys($cart));
        sort($ids, SORT_NUMERIC);
        $marks = implode(',',array_fill(0,count($ids),'?'));
        $stmt=$pdo->prepare("SELECT id,preco_venda,custo_unitario,estoque_atual,ativo FROM produtos WHERE id IN ($marks) ORDER BY id FOR UPDATE");
        $stmt->execute($ids);
        $products=$stmt->fetchAll();
        if(count($products)!==count($ids)) throw new InvalidArgumentException('Algum produto não está mais disponível.');
        $subtotalCents=0;
        foreach($products as &$item) {
            $id=(int)$item['id'];
            $qty=(int)($cart[$id]??0);
            if($qty < 1 || $qty>99 || !(int)$item['ativo'] || $qty>(int)$item['estoque_atual']) throw new InvalidArgumentException('Confira a disponibilidade dos produtos.');
            $item['qty']=$qty;
            $item['price_cents']=(int)round((float)$item['preco_venda']*100);
            $subtotalCents+=$item['price_cents']*$qty;
        }
        unset($item);
        if($subtotalCents<1) throw new InvalidArgumentException('O total do pedido deve ser maior que zero.');
        // Frete será calculado por política de entrega; ainda não cobrar sem regra configurada.
        $freteCents=0;
        $isPix=$values['metodo']==='pix_manual';
        $paymentStatus=$isPix?'pendente':'a_receber_na_entrega';
        $logisticStatus=$isPix?'aguardando_pagamento':'em_separacao';
        $cliente=$pdo->prepare('INSERT INTO clientes(nome,email,telefone) VALUES(?,?,?)');
        $cliente->execute([$values['nome'],$values['email'],$values['telefone']]);
        $clienteId=(int)$pdo->lastInsertId();
        $token='VP-'.date('y').'-'.strtoupper(bin2hex(random_bytes(8)));
        $order=$pdo->prepare("INSERT INTO pedidos(cliente_id,token_rastreio,endereco_cep,endereco_logradouro,endereco_numero,endereco_complemento,endereco_bairro,endereco_cidade,endereco_uf,subtotal,frete,desconto,total,metodo_pagamento,status_pagamento,status_logistico) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $order->execute([$clienteId,$token,preg_replace('/\D/','',$values['cep']),$values['logradouro'],$values['numero'],$values['complemento']?:null,$values['bairro'],$values['cidade'],strtoupper($values['uf']),number_format($subtotalCents/100,2,'.','.'),'0.00','0.00',number_format(($subtotalCents+$freteCents)/100,2,'.','.'),$values['metodo'],$paymentStatus,$logisticStatus]);
        $orderId=(int)$pdo->lastInsertId();
        $insert=$pdo->prepare('INSERT INTO pedido_itens(pedido_id,produto_id,quantidade,preco_unitario,custo_unitario) VALUES(?,?,?,?,?)');
        $decrement=$pdo->prepare('UPDATE produtos SET estoque_atual=estoque_atual-? WHERE id=? AND estoque_atual>=?');
        $movement=$pdo->prepare("INSERT INTO movimentacoes_estoque(produto_id,pedido_id,tipo,quantidade) VALUES(?,?,'saida_venda',?)");
        foreach($products as $item) {
            $insert->execute([$orderId,$item['id'],$item['qty'],$item['preco_venda'],$item['custo_unitario']]);
            $decrement->execute([$item['qty'],$item['id'],$item['qty']]);
            if($decrement->rowCount()!==1)throw new RuntimeException('Estoque inconsistente.');
            $movement->execute([$item['id'],$orderId,-$item['qty']]);
        }
        $hist=$pdo->prepare("INSERT INTO pedido_historico(pedido_id,status_anterior,status_novo) VALUES (?,NULL,?)");
        $hist->execute([$orderId,$logisticStatus]);
        $pdo->commit();
        $_SESSION['cart']=[];
        unset($_SESSION['checkout_nonce']);
        $_SESSION['ultimo_token']=$token;
        header('Location: rastrear.php?token='.rawurlencode($token));exit;
    } catch(InvalidArgumentException $ex) {
        if($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
        $error=$ex->getMessage();
    } catch(Throwable $ex) {
        if($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
        error_log($ex->getMessage());
        $error='Não foi possível concluir o pedido. Tente novamente.';
    }
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Checkout — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar border-bottom"><div class="container"><a class="navbar-brand" href="index.php">VittaPet Store</a><a href="carrinho.php">Voltar ao carrinho</a></div></nav>
<main class="container py-4" style="max-width:820px"><h1 class="h3 fw-bold">Finalizar pedido</h1>
<p class="text-muted">No Pix, você será encaminhado para solicitar a chave pelo WhatsApp após registrar o pedido. Para débito ou crédito, o pagamento será realizado na entrega. O frete está em R$ 0,00 apenas para demonstração; não utilizar para vendas reais sem política de frete.</p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<form method="post" class="row g-3" id="checkout"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="nonce" value="<?=e($checkoutNonce)?>">
<h2 class="h5 mt-4">Seus dados</h2>
<?php foreach(['nome'=>'Nome completo','email'=>'E-mail','telefone'=>'Telefone'] as $key=>$label):?><div class="col-md-<?= $key==='nome'?'12':'6' ?>"><label class="form-label" for="<?=$key?>"><?=$label?></label><input class="form-control" id="<?=$key?>" name="<?=$key?>" value="<?=e($values[$key])?>" <?=$key==='email'?'type="email"':'type="text"'?> required maxlength="190"></div><?php endforeach;?>
<h2 class="h5 mt-4">Endereço de entrega</h2>
<?php foreach(['cep'=>'CEP','logradouro'=>'Rua','numero'=>'Número','complemento'=>'Complemento','bairro'=>'Bairro','cidade'=>'Cidade','uf'=>'UF'] as $key=>$label):?>
<div class="col-md-<?=$key==='logradouro'||$key==='cidade'?'6':'3'?>"><label class="form-label" for="<?=$key?>"><?=$label?></label><input class="form-control" id="<?=$key?>" name="<?=$key?>" value="<?=e($values[$key])?>" <?=$key==='complemento'?'':'required'?>></div>
<?php endforeach;?><div id="cep-status" class="small text-muted" role="status"></div>
<h2 class="h5 mt-4">Pagamento</h2><div class="col-12"><label class="form-label" for="metodo">Método</label><select id="metodo" name="metodo" class="form-select"><option value="pix_manual">Pix antecipado — solicitar chave pelo WhatsApp</option><option value="debito_entrega">Débito na entrega</option><option value="credito_entrega">Crédito na entrega</option></select></div>
<div class="col-12"><p id="payment-hint" class="small text-muted" aria-live="polite">Pix: aguarde a confirmação manual do lojista antes da separação.</p></div>
<div class="col-12"><button type="submit" class="btn btn-primary btn-lg" <?=empty($cart)?'disabled':''?>>Registrar pedido</button></div></form>
</main>
<script>
const cep=document.getElementById('cep'), status=document.getElementById('cep-status');
const metodo=document.getElementById('metodo'), dica=document.getElementById('payment-hint');
metodo.addEventListener('change',()=>{dica.textContent=metodo.value==='pix_manual'?'Pix: solicite a chave no WhatsApp e aguarde o administrador confirmar o recebimento.':'Cartão na entrega: o pedido entra diretamente em separação; o pagamento só é confirmado após a cobrança efetiva.';});
cep.addEventListener('blur',async()=>{
 const value=cep.value.replace(/\D/g,''); if(value.length!==8){status.textContent='Informe um CEP com 8 dígitos.';return;}
 status.textContent='Consultando CEP…';
 try {
   const res=await fetch('https://viacep.com.br/ws/'+value+'/json/');
   if(!res.ok) throw new Error('indisponível');
   const data=await res.json();if(data.erro)throw new Error('não encontrado');
   for(const [field,key] of [['logradouro','logradouro'],['bairro','bairro'],['cidade','localidade'],['uf','uf']])document.getElementById(field).value=data[key]||'';
   status.textContent='Endereço localizado. Confira e informe o número.';
   document.getElementById('numero').focus();
 }catch(e){status.textContent='CEP não localizado. Preencha o endereço manualmente.';}
});
</script></body></html>

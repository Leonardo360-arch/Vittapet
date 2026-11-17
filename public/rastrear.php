<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$token=strtoupper(trim((string)($_GET['token']??'')));
$order=null;$history=[];$error=null;$whatsappUrl=null;
if($token!==''){
 if(!preg_match('/^VP-[0-9]{2}-[A-F0-9]{16}$/D',$token)) $error='Pedido não localizado.';
 else {
  try {
    $stmt=db()->prepare('SELECT id,token_rastreio,metodo_pagamento,total,status_logistico,status_pagamento,created_at,updated_at FROM pedidos WHERE token_rastreio=? LIMIT 1');
    $stmt->execute([$token]);$order=$stmt->fetch();
    if($order){
     if($order['metodo_pagamento']==='pix_manual' && $order['status_pagamento']==='pendente' && $order['status_logistico']!=='cancelado') {
       try {
         $phone=(string)(db()->query('SELECT whatsapp FROM configuracoes_loja WHERE id=1')->fetchColumn()?:'');
         if(preg_match('/^55[1-9][0-9]{9,10}$/D',$phone)) {
           $message='Olá! Fiz uma compra na VittaPet Store, pedido '.$order['token_rastreio'].', no valor de '.money($order['total']).'. Quero pagar antecipadamente por Pix. Pode me enviar a chave Pix?';
           $whatsappUrl='https://wa.me/'.$phone.'?text='.rawurlencode($message);
         }
       } catch(Throwable $ex) { error_log($ex->getMessage()); }
     }
     $h=db()->prepare('SELECT status_novo,criado_em FROM pedido_historico WHERE pedido_id=? ORDER BY id ASC');
     $h->execute([$order['id']]);$history=$h->fetchAll();
    }else $error='Pedido não localizado.';
  }catch(Throwable $ex){error_log($ex->getMessage());$error='Consulta temporariamente indisponível.';}
 }
}
$labels=['aguardando_pagamento'=>'Aguardando pagamento','em_separacao'=>'Em separação','embalado'=>'Pedido embalado','saiu_para_entrega'=>'Saiu para entrega','entregue'=>'Entregue','cancelado'=>'Cancelado'];
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acompanhar pedido — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar border-bottom"><div class="container"><a class="navbar-brand" href="index.php">VittaPet Store</a><a href="index.php">Voltar à loja</a></div></nav>
<main class="container py-5" style="max-width:700px"><h1 class="h3 fw-bold">Acompanhe seu pedido</h1><p class="text-muted">Consulte a situação usando o código recebido ao concluir a compra.</p>
<form method="get" class="d-flex gap-2 mb-4"><input type="text" name="token" class="form-control" placeholder="VP-26-XXXXXXXXXXXXXXXX" aria-label="Código do pedido" value="<?=e($token)?>" required maxlength="32"><button class="btn btn-primary">Consultar</button></form>
<?php if($error):?><div class="alert alert-warning"><?=e($error)?></div><?php endif;?>
<?php if($order):?><div class="card p-4"><p class="small text-muted">Código de acompanhamento</p><p class="fw-bold"><?=e($token)?></p>
<div class="mb-3">Situação: <strong><?=e($labels[$order['status_logistico']]??'Em processamento')?></strong></div>
<div class="mb-3">Pagamento: <strong><?=e(match($order['status_pagamento']){'confirmado'=>'Confirmado','estornado'=>'Estornado','a_receber_na_entrega'=>'A receber na entrega',default=>'Aguardando Pix'})?></strong></div>
<?php if($order['metodo_pagamento']==='pix_manual' && $order['status_pagamento']==='pendente'): ?>
<section class="alert alert-info"><p class="mb-2"><strong>Pix antecipado</strong>: solicite a chave diretamente à loja. O pagamento será confirmado manualmente antes da separação.</p>
<?php if($whatsappUrl): ?><a class="btn btn-success" href="<?=e($whatsappUrl)?>" target="_blank" rel="noopener noreferrer">Solicitar chave Pix pelo WhatsApp</a><p class="small mb-0 mt-2">A mensagem será preenchida; você deverá confirmar o envio no WhatsApp.</p><?php else: ?><p class="small mb-0">WhatsApp comercial ainda não configurado. Contate a loja.</p><?php endif; ?></section>
<?php elseif($order['status_pagamento']==='a_receber_na_entrega'): ?><p class="alert alert-secondary">Pagamento com cartão na entrega: pedido em preparação, cobrança pendente até a entrega.</p><?php endif; ?>
<h2 class="h6">Histórico</h2><ol class="list-group list-group-numbered"><?php foreach($history as $event):?><li class="list-group-item d-flex justify-content-between flex-wrap gap-2"><span><?=e($labels[$event['status_novo']]??$event['status_novo'])?></span><time class="text-muted small"><?=e($event['criado_em'])?></time></li><?php endforeach;?></ol></div><?php endif;?>
</main></body></html>
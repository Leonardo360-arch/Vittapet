<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$token=strtoupper(trim((string)($_GET['token']??'')));
$order=null;$history=[];$error=null;
if($token!==''){
 if(!preg_match('/^VP-[0-9]{2}-[A-F0-9]{16}$/D',$token)) $error='Pedido não localizado.';
 else {
  try {
    $stmt=db()->prepare('SELECT id,status_logistico,status_pagamento,created_at,updated_at FROM pedidos WHERE token_rastreio=? LIMIT 1');
    $stmt->execute([$token]);$order=$stmt->fetch();
    if($order){
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
<div class="mb-3">Pagamento: <strong><?=e($order['status_pagamento']==='confirmado'?'Confirmado':($order['status_pagamento']==='estornado'?'Estornado':'Pendente'))?></strong></div>
<h2 class="h6">Histórico</h2><ol class="list-group list-group-numbered"><?php foreach($history as $event):?><li class="list-group-item d-flex justify-content-between flex-wrap gap-2"><span><?=e($labels[$event['status_novo']]??$event['status_novo'])?></span><time class="text-muted small"><?=e($event['criado_em'])?></time></li><?php endforeach;?></ol></div><?php endif;?>
</main></body></html>
<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
requireAdmin();
$error=null;$success=null;
$labels=['aguardando_pagamento'=>'Aguardando pagamento','em_separacao'=>'Em separação','embalado'=>'Embalado','saiu_para_entrega'=>'Saiu para entrega','entregue'=>'Entregue','cancelado'=>'Cancelado'];
$next=['aguardando_pagamento'=>['em_separacao','cancelado'],'em_separacao'=>['embalado','cancelado'],'embalado'=>['saiu_para_entrega','cancelado'],'saiu_para_entrega'=>['entregue'],'entregue'=>[],'cancelado'=>[]];
if($_SERVER['REQUEST_METHOD']==='POST') {
 $pdo=null;
 try {
  assertCsrf();
  $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
  $acao=(string)($_POST['acao']??'');
  if(!$id)throw new InvalidArgumentException('Pedido inválido.');
  $pdo=db();$pdo->beginTransaction();
  $find=$pdo->prepare('SELECT * FROM pedidos WHERE id=? FOR UPDATE');$find->execute([$id]);$order=$find->fetch();
  if(!$order)throw new InvalidArgumentException('Pedido não encontrado.');
  if($acao==='confirmar_pagamento') {
    if(!in_array($order['status_pagamento'],['pendente','a_receber_na_entrega'],true)||$order['status_logistico']==='cancelado')throw new InvalidArgumentException('Confirmação indisponível para este pedido.');
    if($order['status_pagamento']==='a_receber_na_entrega' && $order['status_logistico']!=='entregue')throw new InvalidArgumentException('Pagamento com cartão só pode ser confirmado após a entrega.');
    $update=$pdo->prepare("UPDATE pedidos SET status_pagamento='confirmado' WHERE id=?");
    $update->execute([$id]);
    if($order['metodo_pagamento']==='pix_manual' && $order['status_logistico']==='aguardando_pagamento') {
      $log=$pdo->prepare("UPDATE pedidos SET status_logistico='em_separacao' WHERE id=?");$log->execute([$id]);
      $history=$pdo->prepare("INSERT INTO pedido_historico(pedido_id,status_anterior,status_novo,usuario_id) VALUES (?,'aguardando_pagamento','em_separacao',?)");
      $history->execute([$id,$_SESSION['user_id']??null]);
    }
    $success='Pagamento confirmado manualmente.';
  } elseif($acao==='logistica') {
    $to=(string)($_POST['status']??'');
    $from=$order['status_logistico'];
    if(!in_array($to,$next[$from]??[],true))throw new InvalidArgumentException('Transição de status inválida.');
    if($to==='em_separacao' && $order['status_pagamento']!=='confirmado'
      && !in_array($order['metodo_pagamento'],['dinheiro_entrega','cartao_entrega','debito_entrega','credito_entrega'],true))throw new InvalidArgumentException('Pagamento ainda não confirmado.');
    if($to==='cancelado' && $order['status_pagamento']==='confirmado')throw new InvalidArgumentException('Pedido pago precisa de procedimento de estorno; cancelamento bloqueado.');
    if($to==='cancelado'){
      $items=$pdo->prepare('SELECT produto_id,quantidade FROM pedido_itens WHERE pedido_id=? ORDER BY produto_id FOR UPDATE');$items->execute([$id]);
      $inc=$pdo->prepare('UPDATE produtos SET estoque_atual=estoque_atual+? WHERE id=?');
      $mov=$pdo->prepare("INSERT INTO movimentacoes_estoque(produto_id,pedido_id,tipo,quantidade) VALUES (?,?,'estorno_venda',?)");
      foreach($items->fetchAll() as $item){$inc->execute([$item['quantidade'],$item['produto_id']]);$mov->execute([$item['produto_id'],$id,$item['quantidade']]);}
    }
    $update=$pdo->prepare('UPDATE pedidos SET status_logistico=? WHERE id=?');$update->execute([$to,$id]);
    $hist=$pdo->prepare('INSERT INTO pedido_historico(pedido_id,status_anterior,status_novo,usuario_id) VALUES(?,?,?,?)');
    $hist->execute([$id,$from,$to,$_SESSION['user_id']??null]);$success='Status atualizado.';
  }else throw new InvalidArgumentException('Ação inválida.');
  $pdo->commit();
 }catch(InvalidArgumentException $ex){if($pdo instanceof PDO && $pdo->inTransaction())$pdo->rollBack();$error=$ex->getMessage();}
 catch(Throwable $ex){if($pdo instanceof PDO && $pdo->inTransaction())$pdo->rollBack();error_log($ex->getMessage());$error='Não foi possível concluir a operação.';}
}
try{$orders=db()->query('SELECT p.id,p.token_rastreio,p.total,p.status_pagamento,p.status_logistico,p.metodo_pagamento,p.created_at,c.nome cliente FROM pedidos p JOIN clientes c ON c.id=p.cliente_id ORDER BY p.id DESC LIMIT 100')->fetchAll();}
catch(Throwable $ex){error_log($ex->getMessage());$orders=[];$error='Pedidos indisponíveis.';}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Pedidos — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar border-bottom"><div class="container"><a class="navbar-brand" href="produtos.php">VittaPet Store</a><div class="d-flex gap-3"><a href="produtos.php">Produtos</a><a href="dashboard.php">Dashboard</a><a href="financeiro.php">Financeiro</a><form method="post" action="../logout.php" class="d-inline"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><button type="submit" class="btn btn-link p-0">Sair</button></form></div></div></nav>
<main class="container py-4"><h1 class="h3">Pedidos da loja</h1><p class="text-muted">Pagamentos confirmados manualmente. Um pedido pago não pode ser cancelado sem o procedimento de estorno.</p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?>
<?php if($success):?><div class="alert alert-success"><?=e($success)?></div><?php endif;?>
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Pedido</th><th>Cliente</th><th>Total</th><th>Pagamento</th><th>Entrega</th><th>Ações</th></tr></thead><tbody>
<?php foreach($orders as $o):?><tr><td><span class="small"><?=e($o['token_rastreio'])?></span><div class="text-muted small"><?=e($o['created_at'])?></div></td><td><?=e($o['cliente'])?></td><td><?=money($o['total'])?></td><td><?=e($o['status_pagamento']==='a_receber_na_entrega'?'A receber na entrega':($o['status_pagamento']==='pendente'?'Aguardando Pix':$o['status_pagamento']))?><div class="small text-muted"><?=e(match($o['metodo_pagamento']){'pix_manual'=>'Pix','debito_entrega'=>'Débito','credito_entrega'=>'Crédito',default=>'Pagamento na entrega'})?></div></td><td><?=e($labels[$o['status_logistico']]??$o['status_logistico'])?></td><td>
<?php if((($o['status_pagamento']==='pendente') || ($o['status_pagamento']==='a_receber_na_entrega' && $o['status_logistico']==='entregue')) && $o['status_logistico']!=='cancelado'):?>
<form method="post" class="mb-2" onsubmit="return confirm('Confirma que o valor foi efetivamente recebido?')"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="id" value="<?=(int)$o['id']?>"><input type="hidden" name="acao" value="confirmar_pagamento"><button class="btn btn-sm btn-outline-primary">Confirmar pagamento</button></form><?php endif;?>
<?php if(!empty($next[$o['status_logistico']])):?>
<form method="post" class="d-flex gap-1"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="id" value="<?=(int)$o['id']?>"><input type="hidden" name="acao" value="logistica"><select class="form-select form-select-sm" aria-label="Novo status" name="status">
<?php foreach($next[$o['status_logistico']] as $to):?>
<?php if($to==='cancelado' && $o['status_pagamento']==='confirmado')continue;?>
<option value="<?=e($to)?>"><?=e($labels[$to])?></option><?php endforeach;?></select><button class="btn btn-sm btn-primary">Salvar</button></form>
<?php endif;?></td></tr><?php endforeach;?></tbody></table></div>
<?php if(!$orders):?><p>Nenhum pedido registrado.</p><?php endif;?></main></body></html>
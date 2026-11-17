<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
requireAdmin();
$start=(string)($_GET['inicio']??date('Y-m-01')); $finish=(string)($_GET['fim']??date('Y-m-d'));
function isValidDate(string $v):bool{$d=DateTimeImmutable::createFromFormat('!Y-m-d',$v);return $d!==false&&$d->format('Y-m-d')===$v;}
if(!isValidDate($start)||!isValidDate($finish)||$start>$finish){http_response_code(400);exit('Período inválido.');}
$end=(new DateTimeImmutable($finish))->modify('+1 day')->format('Y-m-d');
$error=null;$legacyConfirmed=0;$receipt=0;$cost=0;$expense=0;$paid=0;$pending=0;$low=0;$daily=[];$top=[];
try{
 $pdo=db();
 $q=$pdo->prepare("SELECT COUNT(*) quantidade,COALESCE(SUM(total),0) total FROM pedidos WHERE status_pagamento='confirmado' AND pago_em>=? AND pago_em<?");
 $q->execute([$start,$end]);$r=$q->fetch();$paid=(int)$r['quantidade'];$receipt=(float)$r['total'];
 $q=$pdo->prepare("SELECT COALESCE(SUM(i.quantidade*i.custo_unitario),0) FROM pedido_itens i JOIN pedidos p ON p.id=i.pedido_id WHERE p.status_pagamento='confirmado' AND p.pago_em>=? AND p.pago_em<?");
 $q->execute([$start,$end]);$cost=(float)$q->fetchColumn();
 $q=$pdo->prepare('SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa>=? AND data_despesa<=?');
 $q->execute([$start,$finish]);$expense=(float)$q->fetchColumn();
 $legacyConfirmed=(int)$pdo->query("SELECT COUNT(*) FROM pedidos WHERE status_pagamento='confirmado' AND pago_em IS NULL")->fetchColumn();
 $pending=(int)$pdo->query("SELECT COUNT(*) FROM pedidos WHERE status_logistico NOT IN ('cancelado','entregue')")->fetchColumn();
 $low=(int)$pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo=1 AND estoque_atual<=estoque_minimo')->fetchColumn();
 $q=$pdo->prepare("SELECT DATE(pago_em) dia,SUM(total) valor FROM pedidos WHERE status_pagamento='confirmado' AND pago_em>=? AND pago_em<? GROUP BY DATE(pago_em) ORDER BY dia");
 $q->execute([$start,$end]);$daily=$q->fetchAll();
 $q=$pdo->prepare("SELECT p.nome,SUM(i.quantidade) unidades FROM pedido_itens i JOIN pedidos o ON o.id=i.pedido_id JOIN produtos p ON p.id=i.produto_id WHERE o.status_pagamento='confirmado' AND o.pago_em>=? AND o.pago_em<? GROUP BY i.produto_id,p.nome ORDER BY unidades DESC LIMIT 5");
 $q->execute([$start,$end]);$top=$q->fetchAll();
}catch(Throwable $ex){error_log($ex->getMessage());$error='Falha ao carregar indicadores. Verifique as migrations.';}
$cards=[['Receita confirmada',money($receipt)],['CMV estimado',money($cost)],['Despesas',money($expense)],['Resultado estimado',money($receipt-$cost-$expense)],['Ticket médio',money($paid?$receipt/$paid:0)],['Pedidos em andamento',(string)$pending],['Estoque baixo',(string)$low]];
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Dashboard VittaPet Store</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/tokens.css" rel="stylesheet"></head><body>
<header class="border-bottom bg-white"><nav class="container py-3 d-flex gap-3 align-items-center flex-wrap"><a class="navbar-brand me-auto" href="../index.php">VittaPet Store</a><a href="produtos.php">Produtos</a><a href="pedidos.php">Pedidos</a><a href="financeiro.php">Financeiro</a><form action="../logout.php" method="post" class="d-inline"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><button type="submit" class="btn btn-link p-0">Sair</button></form><a href="configuracoes.php">Localização</a></nav></header>
<main class="container py-4"><div class="d-flex justify-content-between gap-3 flex-wrap mb-4"><div><h1 class="h3 fw-bold">Visão geral</h1><p class="text-muted">Dados da operação comercial.</p></div><form class="d-flex gap-2 align-items-end" method="get"><label class="small">Início<input class="form-control form-control-sm" type="date" name="inicio" value="<?=e($start)?>" required></label><label class="small">Fim<input class="form-control form-control-sm" type="date" name="fim" value="<?=e($finish)?>" required></label><button class="btn btn-primary btn-sm">Filtrar</button></form></div>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php else:?>
<div class="row g-3 mb-4"><?php foreach($cards as [$label,$value]):?><div class="col-6 col-xl-3"><article class="card p-3 h-100"><p class="small text-muted mb-2"><?=e($label)?></p><h2 class="h5 fw-bold mb-0"><?=e($value)?></h2></article></div><?php endforeach;?></div>
<div class="row g-3"><section class="col-lg-7"><div class="card p-3 h-100"><h2 class="h5">Vendas por dia</h2><table class="table table-sm"><thead><tr><th>Data</th><th class="text-end">Receita</th></tr></thead><tbody><?php foreach($daily as $v):?><tr><td><?=e($v['dia'])?></td><td class="text-end"><?=money($v['valor'])?></td></tr><?php endforeach;?></tbody></table><?php if(!$daily):?><p class="text-muted">Sem vendas confirmadas neste período.</p><?php endif;?></div></section><section class="col-lg-5"><div class="card p-3 h-100"><h2 class="h5">Produtos mais vendidos</h2><?php foreach($top as $p):?><div class="d-flex justify-content-between border-bottom py-2"><span><?=e($p['nome'])?></span><strong><?=$p['unidades']?> un.</strong></div><?php endforeach;?><?php if(!$top):?><p class="text-muted">Sem dados para exibir.</p><?php endif;?></div></section></div>
<?php if($legacyConfirmed>0):?><p class="alert alert-warning">Existem <?=(int)$legacyConfirmed?> pedidos confirmados antes da migração 009 sem data de recebimento. Eles não entram nos indicadores filtrados até conciliação administrativa. Não atribuímos datas fictícias.</p><?php endif;?>
<p class="small text-muted mt-4">O filtro considera a data real de confirmação do recebimento (pago_em). Pedidos integralmente estornados deixam de compor a receita líquida de pedidos confirmados, inclusive em períodos históricos. Trata-se de um retrato do saldo atual, não de um fluxo de caixa histórico ou contabilidade fiscal. O CMV é estimado e não representa despesas de frete ou custos indiretos.</p>
<?php endif;?></main></body></html>

<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
requireAdmin();
$error=null;$notice=isset($_GET['salvo'])?'Despesa registrada.':null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  assertCsrf();
  $desc=trim((string)($_POST['descricao']??''));$category=trim((string)($_POST['categoria']??''));$amount=trim((string)($_POST['valor']??''));$date=(string)($_POST['data_despesa']??'');
  $d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
  if($desc===''||mb_strlen($desc)>190||$category===''||mb_strlen($category)>80||!preg_match('/^\d{1,8}(\.\d{1,2})?$/D',$amount)||(float)$amount<=0||!$d||$d->format('Y-m-d')!==$date)throw new InvalidArgumentException('Confira a descrição, categoria, valor e data.');
  $st=db()->prepare('INSERT INTO despesas(descricao,categoria,valor,data_despesa) VALUES(?,?,?,?)');$st->execute([$desc,$category,$amount,$date]);
  header('Location: financeiro.php?salvo=1');exit;
 }catch(InvalidArgumentException $ex){$error=$ex->getMessage();}catch(Throwable $ex){error_log($ex->getMessage());$error='Não foi possível registrar a despesa.';}
}
try{$items=db()->query('SELECT descricao,categoria,valor,data_despesa FROM despesas ORDER BY data_despesa DESC,id DESC LIMIT 100')->fetchAll();}catch(Throwable $ex){error_log($ex->getMessage());$error='Falha ao consultar despesas.';$items=[];}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Financeiro — VittaPet Store</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="container py-3 d-flex gap-3 border-bottom"><a href="dashboard.php">Dashboard</a><a href="pedidos.php">Pedidos</a><a href="produtos.php">Produtos</a><form action="../logout.php" method="post" class="d-inline"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><button type="submit" class="btn btn-link p-0">Sair</button></form></nav>
<main class="container py-4"><h1 class="h3 fw-bold">Controle financeiro</h1><p class="text-muted">Registro de despesas operacionais. Os valores de receita são calculados a partir de pedidos com pagamento confirmado.</p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><?php if($notice):?><div class="alert alert-success"><?=e($notice)?></div><?php endif;?>
<div class="row g-4"><section class="col-lg-5"><div class="card p-3"><h2 class="h5">Nova despesa</h2><form method="post" class="d-grid gap-3"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><label>Descrição<input class="form-control" name="descricao" maxlength="190" required></label><label>Categoria<input class="form-control" name="categoria" maxlength="80" required></label><label>Valor (ex.: 12.90)<input class="form-control" name="valor" type="number" min="0.01" step="0.01" required></label><label>Data<input class="form-control" type="date" name="data_despesa" value="<?=date('Y-m-d')?>" required></label><button class="btn btn-primary">Registrar despesa</button></form></div></section>
<section class="col-lg-7"><div class="card p-3"><h2 class="h5">Últimas despesas</h2><div class="table-responsive"><table class="table"><thead><tr><th>Data</th><th>Descrição</th><th>Categoria</th><th class="text-end">Valor</th></tr></thead><tbody><?php foreach($items as $row):?><tr><td><?=e($row['data_despesa'])?></td><td><?=e($row['descricao'])?></td><td><?=e($row['categoria'])?></td><td class="text-end"><?=money($row['valor'])?></td></tr><?php endforeach;?></tbody></table></div><?php if(!$items):?><p class="text-muted">Nenhuma despesa cadastrada.</p><?php endif;?></div></section></div></main></body></html>

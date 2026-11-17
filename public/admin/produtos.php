<?php
require dirname(__DIR__, 2) . '/includes/bootstrap.php';
requireAdmin();
$error=null;$success=null;
try {
 $pdo=db();
 if ($_SERVER['REQUEST_METHOD']==='POST') {
   assertCsrf(); $action=(string)($_POST['action']??'');
   if ($action==='delete') {
     $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
     if (!$id) throw new InvalidArgumentException('Produto inválido.');
     $st=$pdo->prepare('UPDATE produtos SET ativo=0 WHERE id=?');$st->execute([$id]);$success='Produto desativado.';
   } elseif (in_array($action,['create','update'],true)) {
     $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
     $nome=trim((string)($_POST['nome']??''));
     $categoria=filter_var($_POST['categoria_id']??null,FILTER_VALIDATE_INT);
     $preco=(string)($_POST['preco_venda']??'');
     $custo=(string)($_POST['custo_unitario']??'0');
     $estoque=filter_var($_POST['estoque_atual']??null,FILTER_VALIDATE_INT);
     $minimo=filter_var($_POST['estoque_minimo']??null,FILTER_VALIDATE_INT);
     $descricao=trim((string)($_POST['descricao']??''));
     if ($nome==='' || mb_strlen($nome)>180 || !$categoria || !preg_match('/^\d{1,8}(\.\d{1,2})?$/D',$preco) || !preg_match('/^\d{1,8}(\.\d{1,2})?$/D',$custo) || $estoque===false || $estoque<0 || $minimo===false || $minimo<0) throw new InvalidArgumentException('Confira os campos e utilize preços como 29.90.');
     $check=$pdo->prepare('SELECT id FROM categorias WHERE id=? AND ativo=1');$check->execute([$categoria]);if(!$check->fetch())throw new InvalidArgumentException('Categoria não encontrada.');
     if ($action==='update') {
       if (!$id) throw new InvalidArgumentException('Produto inválido.');
       $st=$pdo->prepare('UPDATE produtos SET categoria_id=?,nome=?,descricao=?,preco_venda=?,custo_unitario=?,estoque_atual=?,estoque_minimo=? WHERE id=? AND ativo=1');
       $st->execute([$categoria,$nome,$descricao,$preco,$custo,$estoque,$minimo,$id]);$success='Produto atualizado.';
     } else {
       $st=$pdo->prepare('INSERT INTO produtos (categoria_id,nome,descricao,preco_venda,custo_unitario,estoque_atual,estoque_minimo) VALUES (?,?,?,?,?,?,?)');
       $st->execute([$categoria,$nome,$descricao,$preco,$custo,$estoque,$minimo]);$success='Produto cadastrado.';
     }
   }
 }
} catch(InvalidArgumentException $ex) {$error=$ex->getMessage();} catch(Throwable $ex){error_log($ex->getMessage());$error='Não foi possível concluir a operação.';}
try {
 $categorias=db()->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY nome')->fetchAll();
 $produtos=db()->query('SELECT p.*,c.nome categoria FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 ORDER BY p.id DESC')->fetchAll();
} catch(Throwable $ex){error_log($ex->getMessage());$categorias=[];$produtos=[];$error='Erro ao carregar os dados.';}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Produtos — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar border-bottom"><div class="container"><a class="navbar-brand" href="../index.php">VittaPet Store</a><a href="../logout.php">Sair</a></div></nav>
<main class="container py-4"><div class="d-flex flex-wrap gap-3 mb-3"><a href="dashboard.php">Dashboard</a><a href="pedidos.php">Pedidos</a><a href="financeiro.php">Financeiro</a></div><h1 class="h3 fw-bold">Gestão de produtos</h1><p class="text-muted">Cadastro, edição, consulta e desativação.</p>
<?php if($error):?><div class="alert alert-danger"><?=e($error)?></div><?php endif;?><?php if($success):?><div class="alert alert-success"><?=e($success)?></div><?php endif;?>
<div class="card p-3 mb-4"><h2 class="h5">Novo produto / editar</h2><form method="post" id="product-form" class="row g-3"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="id" id="id"><input type="hidden" name="action" id="action" value="create">
<div class="col-md-6"><label for="nome" class="form-label">Nome</label><input class="form-control" required maxlength="180" name="nome" id="nome"></div>
<div class="col-md-6"><label for="categoria_id" class="form-label">Categoria</label><select class="form-select" required name="categoria_id" id="categoria_id"><option value="">Selecione</option><?php foreach($categorias as $c):?><option value="<?=$c['id']?>"><?=e($c['nome'])?></option><?php endforeach;?></select></div>
<div class="col-md-6"><label for="preco_venda" class="form-label">Preço (ex.: 29.90)</label><input class="form-control" required type="number" step="0.01" min="0" name="preco_venda" id="preco_venda"></div>
<div class="col-md-6"><label for="custo_unitario" class="form-label">Custo unitário</label><input class="form-control" required type="number" step="0.01" min="0" name="custo_unitario" id="custo_unitario" value="0"></div>
<div class="col-md-6"><label for="estoque_atual" class="form-label">Estoque</label><input class="form-control" required type="number" min="0" name="estoque_atual" id="estoque_atual" value="0"></div>
<div class="col-md-6"><label for="estoque_minimo" class="form-label">Estoque mínimo</label><input class="form-control" required type="number" min="0" name="estoque_minimo" id="estoque_minimo" value="5"></div>
<div class="col-12"><label for="descricao" class="form-label">Descrição</label><textarea class="form-control" name="descricao" id="descricao" rows="2"></textarea></div>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Salvar produto</button><button class="btn btn-outline-secondary" type="reset" id="reset">Novo</button></div></form></div>
<div class="card p-3"><h2 class="h5">Produtos ativos (<?=count($produtos)?>)</h2><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Produto</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead><tbody>
<?php foreach($produtos as $p):?><tr><td><?=e($p['nome'])?></td><td><?=e($p['categoria'])?></td><td><?=money($p['preco_venda'])?></td><td><?=intval($p['estoque_atual'])?></td><td class="d-flex gap-2">
<button type="button" class="btn btn-sm btn-outline-primary edit" data-product="<?=e(json_encode(['id'=>$p['id'],'nome'=>$p['nome'],'categoria_id'=>$p['categoria_id'],'descricao'=>$p['descricao'],'preco_venda'=>$p['preco_venda'],'custo_unitario'=>$p['custo_unitario'],'estoque_atual'=>$p['estoque_atual'],'estoque_minimo'=>$p['estoque_minimo']],JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT))?>">Editar</button>
<form method="post" onsubmit="return confirm('Desativar este produto?')"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=intval($p['id'])?>"><button class="btn btn-sm btn-outline-danger">Excluir</button></form></td></tr><?php endforeach;?></tbody></table></div></div></main>
<script>const fields=['id','nome','categoria_id','descricao','preco_venda','custo_unitario','estoque_atual','estoque_minimo'];document.querySelectorAll('.edit').forEach(b=>b.addEventListener('click',()=>{const p=JSON.parse(b.dataset.product);fields.forEach(k=>document.getElementById(k).value=p[k]??'');document.getElementById('action').value='update';document.getElementById('product-form').scrollIntoView({behavior:'smooth'});}));document.getElementById('reset').addEventListener('click',()=>{document.getElementById('id').value='';document.getElementById('action').value='create';});</script></body></html>

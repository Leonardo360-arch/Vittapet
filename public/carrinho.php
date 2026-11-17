<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
header('Cache-Control: no-store');
function cartState(): array {
  $cart=$_SESSION['cart']??[];
  if (!$cart) return ['items'=>[],'count'=>0,'subtotal'=>0];
  $ids=array_keys($cart);
  $marks=implode(',',array_fill(0,count($ids),'?'));
  $st=db()->prepare("SELECT id,nome,preco_venda,estoque_atual,ativo FROM produtos WHERE id IN ($marks)");
  $st->execute($ids);$rows=$st->fetchAll();
  $items=[];$subtotal=0;$count=0;
  foreach($rows as $row){
    $id=(int)$row['id']; $qty=(int)($cart[$id]??0);
    if(!$row['ativo'] || $qty<1) continue;
    $qty=min($qty,(int)$row['estoque_atual']);
    if($qty===0)continue;
    $unit=(float)$row['preco_venda'];
    $items[]=['id'=>$id,'nome'=>$row['nome'],'quantidade'=>$qty,'preco'=>$unit,'total'=>round($unit*$qty,2),'estoque'=>(int)$row['estoque_atual']];
    $subtotal+=round($unit*$qty,2);$count+=$qty;
  }
  return ['items'=>$items,'count'=>$count,'subtotal'=>round($subtotal,2)];
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $json=($_SERVER['HTTP_ACCEPT']??'')==='application/json';
  try {
    assertCsrf();
    $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
    $qty=filter_var($_POST['quantity']??null,FILTER_VALIDATE_INT);
    if(!$id || $qty===false || $qty<0 || $qty>99) throw new InvalidArgumentException('Quantidade inválida.');
    $st=db()->prepare('SELECT estoque_atual FROM produtos WHERE id=? AND ativo=1');$st->execute([$id]);$product=$st->fetch();
    if(!$product)throw new InvalidArgumentException('Produto não encontrado.');
    if($qty>(int)$product['estoque_atual'])throw new InvalidArgumentException('Estoque insuficiente.');
    if(($_POST['action']??'')==='add') $qty=(int)($_SESSION['cart'][$id]??0)+$qty;
    if($qty>(int)$product['estoque_atual'] || $qty>99) throw new InvalidArgumentException('Estoque insuficiente.');
    if($qty===0)unset($_SESSION['cart'][$id]);else $_SESSION['cart'][$id]=$qty;
    if($json){header('Content-Type: application/json; charset=utf-8');echo json_encode(cartState(),JSON_UNESCAPED_UNICODE);exit;}
  } catch(Throwable $ex){
    if($json){http_response_code(422);header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>$ex instanceof InvalidArgumentException?$ex->getMessage():'Não foi possível atualizar o carrinho.']);exit;}
    $_SESSION['cart_error']='Não foi possível atualizar o carrinho.';
  }
  header('Location: carrinho.php');exit;
}
try{$state=cartState();$error=null;}catch(Throwable $ex){error_log($ex->getMessage());$state=['items'=>[],'subtotal'=>0,'count'=>0];$error='Carrinho indisponível no momento.';}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Meu carrinho — VittaPet</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar border-bottom"><div class="container"><a class="navbar-brand" href="index.php">VittaPet Store</a><a href="index.php">Voltar à loja</a></div></nav>
<main class="container py-4" style="max-width:950px"><h1 class="h3 fw-bold mb-4">Meu carrinho</h1><div id="alert" class="alert alert-danger d-none" role="alert"></div><?php if($error):?><div class="alert alert-warning"><?=e($error)?></div><?php endif;?>
<div class="row g-4"><div class="col-lg-8"><div id="items">
<?php foreach($state['items'] as $item):?><div class="card mb-3 p-3 cart-item" data-id="<?=$item['id']?>"><div class="d-flex justify-content-between gap-3 align-items-center flex-wrap"><div><div class="fw-semibold"><?=e($item['nome'])?></div><div class="text-muted small unit-price"><?=money($item['preco'])?> cada</div></div><div class="d-flex gap-2 align-items-center"><button class="btn btn-sm btn-outline-secondary minus" aria-label="Diminuir quantidade">−</button><span class="qty px-2"><?=$item['quantidade']?></span><button class="btn btn-sm btn-outline-secondary plus" aria-label="Aumentar quantidade">+</button><button class="btn btn-sm btn-link text-danger remove">Remover</button></div><strong class="item-total"><?=money($item['total'])?></strong></div></div><?php endforeach;?></div><p id="empty" class="text-muted <?=$state['count']?'d-none':''?>">Seu carrinho está vazio.</p></div>
<aside class="col-lg-4"><div class="card p-3"><h2 class="h5">Resumo do pedido</h2><div class="d-flex justify-content-between"><span>Itens</span><span id="count"><?=$state['count']?></span></div><div class="d-flex justify-content-between mt-3"><strong>Subtotal</strong><strong id="subtotal"><?=money($state['subtotal'])?></strong></div><p class="small text-muted mt-3">O frete fixo será informado no checkout, se estiver configurado pelo lojista.</p><a class="btn btn-primary" href="checkout.php">Continuar para checkout</a></div></aside></div></main>
<script>
const token=<?=json_encode(csrf())?>;
const format=new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'});
let busy=false;
document.getElementById('items').addEventListener('click',async ev=>{
 const button=ev.target.closest('button');if(!button||busy)return;
 const row=button.closest('.cart-item');const id=Number(row.dataset.id);const old=Number(row.querySelector('.qty').textContent);
 const qty=button.classList.contains('remove')?0:button.classList.contains('plus')?old+1:Math.max(0,old-1);
 busy=true;button.disabled=true;const alert=document.getElementById('alert');alert.classList.add('d-none');
 try{
  const data=new URLSearchParams({id:String(id),quantity:String(qty),csrf:token});
  const response=await fetch('carrinho.php',{method:'POST',body:data,headers:{Accept:'application/json'},credentials:'same-origin'});
  const result=await response.json();if(!response.ok)throw new Error(result.error||'Falha ao atualizar');
  const current=result.items.find(item=>item.id===id);
  if(!current)row.remove();else {row.querySelector('.qty').textContent=current.quantidade;row.querySelector('.item-total').textContent=format.format(current.total);}
  document.getElementById('subtotal').textContent=format.format(result.subtotal);
  document.getElementById('count').textContent=result.count;
  document.getElementById('empty').classList.toggle('d-none',result.count>0);
 }catch(err){alert.textContent=err.message;alert.classList.remove('d-none');}
 finally{busy=false;button.disabled=false;}
});
</script></body></html>
<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/includes/bootstrap.php';
requireAdmin();
$erro=null;$sucesso=isset($_GET['salvo']);
$default=['nome'=>'VittaPet Store','cep'=>'','logradouro'=>'','numero'=>'','complemento'=>'','bairro'=>'','cidade'=>'','uf'=>'','latitude'=>'','longitude'=>''];
if($_SERVER['REQUEST_METHOD']==='POST'){
 try {
   assertCsrf();
   $values=[];
   $limits=['nome'=>120,'cep'=>8,'logradouro'=>190,'numero'=>20,'complemento'=>100,'bairro'=>120,'cidade'=>120,'uf'=>2];
   foreach($limits as $k=>$limit){
     $values[$k]=trim((string)($_POST[$k]??''));
     if(mb_strlen($values[$k])>$limit)throw new InvalidArgumentException('Campo excedeu limite: '.$k);
   }
   $values['cep']=preg_replace('/\D/','',$values['cep']);
   $values['uf']=strtoupper($values['uf']);
   if($values['nome']==='')throw new InvalidArgumentException('Informe o nome da loja.');
   if($values['cep']!=='' && !preg_match('/^\d{8}$/D',$values['cep']))throw new InvalidArgumentException('CEP deve possuir oito números.');
   if($values['uf']!=='' && !preg_match('/^[A-Z]{2}$/D',$values['uf']))throw new InvalidArgumentException('UF inválida.');
   $lat=trim((string)($_POST['latitude']??''));
   $lon=trim((string)($_POST['longitude']??''));
   if(($lat==='')!==($lon===''))throw new InvalidArgumentException('Informe as duas coordenadas ou deixe ambas vazias.');
   if($lat!=='' && (!is_numeric($lat)||!is_numeric($lon)||(float)$lat < -90||(float)$lat > 90||(float)$lon < -180||(float)$lon > 180))throw new InvalidArgumentException('Coordenadas inválidas.');
   $db=db();
   $st=$db->prepare('UPDATE configuracoes_loja SET nome=?,cep=?,logradouro=?,numero=?,complemento=?,bairro=?,cidade=?,uf=?,latitude=?,longitude=?,geocodificado_em=? WHERE id=1');
   $st->execute([
     $values['nome'],$values['cep']?:null,$values['logradouro']?:null,$values['numero']?:null,
     $values['complemento']?:null,$values['bairro']?:null,$values['cidade']?:null,$values['uf']?:null,
     $lat===''?null:$lat,$lon===''?null:$lon,$lat===''?null:date('Y-m-d H:i:s')
   ]);
   header('Location: configuracoes.php?salvo=1');exit;
 }catch(InvalidArgumentException $ex){$erro=$ex->getMessage();}
 catch(Throwable $ex){error_log($ex->getMessage());$erro='Não foi possível salvar a localização. Verifique a migration 004.';}
}
try {
 $dados=db()->query('SELECT nome,cep,logradouro,numero,complemento,bairro,cidade,uf,latitude,longitude FROM configuracoes_loja WHERE id=1')->fetch();
 if(!$dados)throw new RuntimeException('Configuração ausente.');
 $dados=array_replace($default,array_map(static fn($x)=>$x===null?'':(string)$x,$dados));
}catch(Throwable $ex){error_log($ex->getMessage());$dados=$default;$erro='Configuração indisponível: execute migration 004.';}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Localização — VittaPet Store</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"><link rel="stylesheet" href="../assets/css/tokens.css"></head>
<body><nav class="container d-flex flex-wrap gap-3 border-bottom py-3"><a href="dashboard.php">Dashboard</a><a href="produtos.php">Produtos</a><a href="pedidos.php">Pedidos</a><a href="../index.php">Ver loja</a></nav>
<main class="container py-4" style="max-width:860px"><h1 class="h3 fw-bold">Localização da loja</h1><p class="text-muted">Configure o endereço uma vez e utilize as coordenadas salvas no mapa público. A busca de coordenadas é feita sob demanda, nunca a cada visita.</p>
<?php if($erro):?><div class="alert alert-danger" role="alert"><?=e($erro)?></div><?php endif;?><?php if($sucesso):?><div class="alert alert-success">Localização salva.</div><?php endif;?>
<form id="store-location-form" method="post" class="card p-3 p-md-4 row g-3">
<input type="hidden" name="csrf" value="<?=e(csrf())?>">
<?php
$labels=['nome'=>'Nome da loja','cep'=>'CEP','logradouro'=>'Logradouro','numero'=>'Número','complemento'=>'Complemento','bairro'=>'Bairro','cidade'=>'Cidade','uf'=>'UF'];
foreach($labels as $key=>$label):
?><div class="col-md-<?=$key==='nome'||$key==='logradouro'?'12':($key==='cidade'?'6':'4')?>"><label for="<?=e($key)?>" class="form-label"><?=e($label)?></label><input class="form-control" id="<?=e($key)?>" name="<?=e($key)?>" value="<?=e($dados[$key])?>" maxlength="<?=$key==='cep'?9:($key==='uf'?2:190)?>" <?=$key==='nome'?'required':''?>></div><?php endforeach;?>
<div class="col-12"><button type="button" class="btn btn-outline-primary" id="geocode">Localizar endereço no mapa</button><p class="small text-muted mt-2 mb-1">Consulta pontual à API Nominatim (OpenStreetMap), apenas quando você clicar; sem busca automática ao digitar.</p><div id="geo-status" role="status" aria-live="polite" class="small"></div></div>
<div class="col-md-6"><label for="latitude" class="form-label">Latitude</label><input id="latitude" name="latitude" class="form-control" inputmode="decimal" value="<?=e($dados['latitude'])?>" placeholder="-22.0000000"></div>
<div class="col-md-6"><label for="longitude" class="form-label">Longitude</label><input id="longitude" name="longitude" class="form-control" inputmode="decimal" value="<?=e($dados['longitude'])?>" placeholder="-47.0000000"></div>
<div class="col-12 d-flex gap-2 flex-wrap"><button class="btn btn-primary" type="submit">Salvar localização</button><a href="../index.php" class="btn btn-outline-secondary">Visualizar loja</a></div>
</form></main>
<script>
(() => {
 const form=document.getElementById('store-location-form');
 const button=document.getElementById('geocode');
 const status=document.getElementById('geo-status');
 const fields=['cep','logradouro','numero','complemento','bairro','cidade','uf'];
 let lastRequestAt=0,found=false;
 fields.forEach(k=>document.getElementById(k).addEventListener('input',()=>{
   document.getElementById('latitude').value='';
   document.getElementById('longitude').value='';
   found=false;
 }));
 button.addEventListener('click',async()=>{
   if(button.disabled)return;
   const address=['logradouro','numero','bairro','cidade','uf'].map(k=>document.getElementById(k).value.trim()).filter(Boolean).join(', ');
   if(!document.getElementById('logradouro').value.trim()||!document.getElementById('cidade').value.trim()||!document.getElementById('uf').value.trim()){
     status.textContent='Informe rua, cidade e UF antes de consultar.';return;
   }
   if(Date.now()-lastRequestAt<1200){status.textContent='Aguarde antes de consultar novamente.';return;}
   lastRequestAt=Date.now();
   button.disabled=true;status.textContent='Consultando coordenadas...';
   try{
     const query=new URLSearchParams({q:address+', Brasil',format:'jsonv2',limit:'1',countrycodes:'br'});
     const response=await fetch('https://nominatim.openstreetmap.org/search?'+query.toString(),{headers:{Accept:'application/json'}});
     if(!response.ok)throw new Error('Serviço indisponível');
     const data=await response.json();
     if(!Array.isArray(data)||!data.length)throw new Error('Endereço não localizado; refine os dados ou informe coordenadas manualmente.');
     const latitude=Number(data[0].lat),longitude=Number(data[0].lon);
     if(!Number.isFinite(latitude)||!Number.isFinite(longitude)||Math.abs(latitude)>90||Math.abs(longitude)>180)throw new Error('Resposta inválida');
     document.getElementById('latitude').value=latitude.toFixed(7);
     document.getElementById('longitude').value=longitude.toFixed(7);
     found=true;
     status.textContent='Coordenadas recebidas da API: '+latitude.toFixed(7)+', '+longitude.toFixed(7)+'. Verifique se correspondem à loja e salve.';
   }catch(err){status.textContent='Não foi possível localizar: '+err.message;}
   finally{button.disabled=false;}
 });
})();
</script></body></html>
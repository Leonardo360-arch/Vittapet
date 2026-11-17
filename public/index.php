<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
try {
    $categorias = db()->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY nome')->fetchAll();
    $produtos = db()->query('SELECT p.id,p.nome,p.descricao,p.preco_venda,p.estoque_atual,p.imagem_path,c.nome categoria FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND c.ativo=1 ORDER BY p.id DESC LIMIT 30')->fetchAll();
    $erro = null;
} catch (Throwable $ex) { error_log($ex->getMessage()); $erro='Não foi possível carregar o catálogo.'; $produtos=[]; $categorias=[]; }
$loja=null;
try {
    $loja=db()->query("SELECT nome,cep,logradouro,numero,complemento,bairro,cidade,uf,latitude,longitude FROM configuracoes_loja WHERE id=1")->fetch() ?: null;
} catch (Throwable $ex) { error_log($ex->getMessage()); }
$temMapa=$loja && $loja['latitude']!==null && $loja['longitude']!==null;
$endereco=$loja ? implode(', ',array_filter([
    $loja['logradouro']??'', $loja['numero']??'', $loja['bairro']??'',
    $loja['cidade']??'', $loja['uf']??'', $loja['cep']??''
],static fn($v)=>trim((string)$v)!=='')) : '';
$rotaGoogle=$temMapa?'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($loja['latitude'].','.$loja['longitude']):'';
$rotaWaze=$temMapa?'https://waze.com/ul?ll='.rawurlencode($loja['latitude'].','.$loja['longitude']).'&navigate=yes':'';
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>VittaPet Store — tudo para seu pet</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/tokens.css" rel="stylesheet">
<link href="assets/css/mapa.css" rel="stylesheet">
<?php if($temMapa): ?><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><?php endif;?>
</head><body>
<nav class="navbar navbar-expand bg-white border-bottom sticky-top"><div class="container py-2">
<a class="navbar-brand fw-bold" href="index.php" style="color:var(--color-primary)">VittaPet<span style="color:var(--color-accent)">.</span></a>
<div class="ms-auto d-flex gap-3 align-items-center"><a href="#produtos">Produtos</a><a href="login.php">Admin</a><a href="carrinho.php" class="btn btn-outline-primary btn-sm control-slim">Carrinho</a></div></div></nav>
<main class="container">
<section class="hero my-4 p-4 p-lg-5"><div class="row align-items-center"><div class="col-lg-8"><p class="text-uppercase fw-bold small" style="color:var(--color-primary)">VittaPet Store</p>
<h1 class="display-5 fw-bold">O cuidado começa nas pequenas escolhas.</h1><p class="lead">Alimentação, diversão e acessórios para seu melhor amigo.</p>
<a href="#produtos" class="btn btn-primary px-4 py-2 control-slim">Explorar produtos</a></div><div class="col-lg-4 text-center"><div class="p-5 rounded-4 bg-white fw-bold" style="color:var(--color-primary);font-family:Poppins,sans-serif;font-size:2.5rem">VittaPet</div></div></div></section>
<section id="produtos" class="py-4"><div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4"><h2 class="h3 fw-bold mb-0">Nossa loja</h2>
<select id="filtro" class="form-select control-slim" style="max-width:220px"><option value="">Todas as categorias</option><?php foreach($categorias as $cat): ?><option value="<?=e($cat['nome'])?>"><?=e($cat['nome'])?></option><?php endforeach; ?></select></div>
<?php if ($erro): ?><div class="alert alert-warning"><?=e($erro)?></div><?php endif; ?>
<div class="row g-3"><?php foreach($produtos as $p): ?><div class="col-6 col-lg-3 produto" data-category="<?=e($p['categoria'])?>"><article class="card h-100">
<div class="d-flex align-items-center justify-content-center bg-light" style="height:165px">
<?php if ($p['imagem_path']): ?><img src="<?=e($p['imagem_path'])?>" alt="<?=e($p['nome'])?>" loading="lazy" style="max-width:100%;max-height:165px;object-fit:contain"><?php else: ?><span class="text-muted">VittaPet</span><?php endif; ?></div>
<div class="card-body d-flex flex-column"><small class="text-muted"><?=e($p['categoria'])?></small><h3 class="h6 fw-semibold mt-1"><?=e($p['nome'])?></h3>
<p class="text-muted small flex-grow-1"><?=e(mb_strimwidth((string)($p['descricao']??''),0,90,'…'))?></p>
<div class="price mb-2"><?=money($p['preco_venda'])?></div><form method="post" action="carrinho.php"><input type="hidden" name="csrf" value="<?=e(csrf())?>"><input type="hidden" name="id" value="<?=intval($p['id'])?>"><input type="hidden" name="quantity" value="1"><input type="hidden" name="action" value="add"><button class="btn btn-primary btn-sm control-slim w-100" type="submit" <?=intval($p['estoque_atual'])===0?'disabled':''?>><?=intval($p['estoque_atual'])===0?'Esgotado':'Adicionar ao carrinho'?></button></form></div></article></div><?php endforeach; ?>
<?php if(!$produtos): ?><p class="text-muted">Nenhum produto disponível no momento.</p><?php endif;?></div></section>
</main>
<section class="store-location py-5 mt-5" id="onde-estamos" aria-labelledby="location-title">
 <div class="container location-grid">
  <div class="location-panel">
   <span class="small text-uppercase fw-bold" style="color:var(--color-primary)">VittaPet Store</span>
   <h2 class="h3" id="location-title">Onde estamos</h2>
   <p class="location-address mb-0"><?= $endereco!=='' ? e($endereco) : 'A localização da loja será informada em breve.' ?></p>
   <?php if($temMapa): ?>
   <div class="location-links">
    <a class="btn btn-primary control-slim" href="<?=e($rotaGoogle)?>" target="_blank" rel="noopener noreferrer">Rotas no Google Maps</a>
    <a class="btn btn-outline-primary control-slim" href="<?=e($rotaWaze)?>" target="_blank" rel="noopener noreferrer">Abrir no Waze</a>
   </div>
   <?php endif; ?>
   <p id="store-map-status" class="map-status text-muted" role="status" aria-live="polite"><?= $temMapa?'Carregando mapa...':'Localização ainda não configurada.' ?></p>
  </div>
  <?php if($temMapa): ?>
    <div id="store-map" class="map-frame" tabindex="0" role="region" aria-label="Mapa com a localização da VittaPet Store"
       data-lat="<?=e((string)$loja['latitude'])?>" data-lon="<?=e((string)$loja['longitude'])?>" data-name="<?=e((string)$loja['nome'])?>"></div>
  <?php else: ?>
    <div class="map-frame d-flex align-items-center justify-content-center text-muted px-4 text-center">Mapa disponível após configurar o endereço da loja.</div>
  <?php endif; ?>
 </div>
</section>
<footer class="border-top py-4"><div class="container d-flex justify-content-between flex-wrap gap-2"><span>© VittaPet Store — Projeto acadêmico</span><a href="#onde-estamos">Localização da loja</a></div></footer>
<script>document.getElementById('filtro').addEventListener('change',function(){document.querySelectorAll('.produto').forEach(el=>el.hidden=Boolean(this.value)&&el.dataset.category!==this.value);});</script>
<?php if($temMapa): ?>
<script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script defer src="assets/js/mapa.js"></script>
<?php endif; ?>
</body></html>

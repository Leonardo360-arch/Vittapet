<?php
require dirname(__DIR__) . '/includes/bootstrap.php';
try {
    $categorias = db()->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY nome')->fetchAll();
    $produtos = db()->query('SELECT p.id,p.nome,p.descricao,p.preco_venda,p.estoque_atual,p.imagem_path,c.nome categoria FROM produtos p JOIN categorias c ON c.id=p.categoria_id WHERE p.ativo=1 AND c.ativo=1 ORDER BY p.id DESC LIMIT 30')->fetchAll();
    $erro = null;
} catch (Throwable $ex) { error_log($ex->getMessage()); $erro='Não foi possível carregar o catálogo.'; $produtos=[]; $categorias=[]; }
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>VittaPet Store — tudo para seu pet</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/tokens.css" rel="stylesheet"></head><body>
<nav class="navbar navbar-expand bg-white border-bottom sticky-top"><div class="container py-2">
<a class="navbar-brand fw-bold" href="index.php" style="color:var(--color-primary)">VittaPet<span style="color:var(--color-accent)">.</span></a>
<div class="ms-auto d-flex gap-3 align-items-center"><a href="#produtos">Produtos</a><a href="login.php">Admin</a><a href="carrinho.php" class="btn btn-outline-primary btn-sm control-slim">Carrinho</a></div></div></nav>
<main class="container">
<section class="hero my-4 p-4 p-lg-5"><div class="row align-items-center"><div class="col-lg-8"><p class="text-uppercase fw-bold small" style="color:var(--color-primary)">VittaPet Store</p>
<h1 class="display-5 fw-bold">O cuidado começa nas pequenas escolhas.</h1><p class="lead">Alimentação, diversão e acessórios para seu melhor amigo.</p>
<a href="#produtos" class="btn btn-primary px-4 py-2 control-slim">Explorar produtos</a></div><div class="col-lg-4 text-center display-1" aria-hidden="true">🐾</div></div></section>
<section id="produtos" class="py-4"><div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4"><h2 class="h3 fw-bold mb-0">Nossa loja</h2>
<select id="filtro" class="form-select control-slim" style="max-width:220px"><option value="">Todas as categorias</option><?php foreach($categorias as $cat): ?><option value="<?=e($cat['nome'])?>"><?=e($cat['nome'])?></option><?php endforeach; ?></select></div>
<?php if ($erro): ?><div class="alert alert-warning"><?=e($erro)?></div><?php endif; ?>
<div class="row g-3"><?php foreach($produtos as $p): ?><div class="col-6 col-lg-3 produto" data-category="<?=e($p['categoria'])?>"><article class="card h-100">
<div class="d-flex align-items-center justify-content-center bg-light" style="height:165px">
<?php if ($p['imagem_path']): ?><img src="<?=e($p['imagem_path'])?>" alt="<?=e($p['nome'])?>" loading="lazy" style="max-width:100%;max-height:165px;object-fit:contain"><?php else: ?><span class="text-muted">VittaPet</span><?php endif; ?></div>
<div class="card-body d-flex flex-column"><small class="text-muted"><?=e($p['categoria'])?></small><h3 class="h6 fw-semibold mt-1"><?=e($p['nome'])?></h3>
<p class="text-muted small flex-grow-1"><?=e(mb_strimwidth((string)($p['descricao']??''),0,90,'…'))?></p>
<div class="price mb-2"><?=money($p['preco_venda'])?></div><button class="btn btn-primary btn-sm control-slim" type="button" disabled>Comprar em breve</button></div></article></div><?php endforeach; ?>
<?php if(!$produtos): ?><p class="text-muted">Nenhum produto disponível no momento.</p><?php endif;?></div></section>
</main>
<footer class="border-top mt-5 py-4"><div class="container d-flex justify-content-between flex-wrap gap-2"><span>© VittaPet Store — Projeto acadêmico</span><span>Localização e mapa: próxima etapa</span></div></footer>
<script>document.getElementById('filtro').addEventListener('change',function(){document.querySelectorAll('.produto').forEach(el=>el.hidden=Boolean(this.value)&&el.dataset.category!==this.value);});</script></body></html>

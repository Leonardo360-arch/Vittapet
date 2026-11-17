<?php
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$erro = null;
$categorias = [];
$produtos = [];
$loja = null;
try {
    $pdo = db();
    $categorias = $pdo->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY nome')->fetchAll();
    $produtos = $pdo->query(
        'SELECT p.id,p.nome,p.descricao,p.preco_venda,p.estoque_atual,p.imagem_path,c.nome categoria
         FROM produtos p JOIN categorias c ON c.id=p.categoria_id
         WHERE p.ativo=1 AND c.ativo=1 ORDER BY p.id DESC LIMIT 60'
    )->fetchAll();
    $loja = $pdo->query(
        'SELECT nome,cep,logradouro,numero,complemento,bairro,cidade,uf,latitude,longitude
         FROM configuracoes_loja WHERE id=1'
    )->fetch() ?: null;
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $erro = 'Não foi possível carregar os produtos. Tente novamente em instantes.';
}
$temMapa = $loja && $loja['latitude'] !== null && $loja['longitude'] !== null;
$endereco = $loja ? implode(', ', array_filter([
    $loja['logradouro'] ?? '', $loja['numero'] ?? '', $loja['bairro'] ?? '',
    $loja['cidade'] ?? '', $loja['uf'] ?? '', $loja['cep'] ?? '',
], static fn($value) => trim((string)$value) !== '')) : '';
$rotaGoogle = $temMapa ? 'https://www.google.com/maps/dir/?api=1&destination=' .
    rawurlencode($loja['latitude'].','.$loja['longitude']) : '';
$rotaWaze = $temMapa ? 'https://waze.com/ul?ll=' .
    rawurlencode($loja['latitude'].','.$loja['longitude']) . '&navigate=yes' : '';
$cartCount = 0;
foreach (($_SESSION['cart'] ?? []) as $quantity) {
    $cartCount += max(0, min(99, (int)$quantity));
}
$demoCount = count(array_filter($produtos, static fn($p) => str_starts_with((string)$p['nome'], 'DEMO - ')));
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="VittaPet Store: alimentação, brinquedos, higiene e acessórios para cuidar do seu pet.">
  <title>VittaPet Store — carinho em cada escolha</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/tokens.css" rel="stylesheet">
  <link href="assets/css/storefront.css" rel="stylesheet">
  <link href="assets/css/storefront-interactions.css" rel="stylesheet">
  <link href="assets/css/mapa.css" rel="stylesheet">
  <?php if ($temMapa): ?>
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
  <?php endif; ?>
</head>
<body class="storefront">
  <a class="visually-hidden-focusable position-absolute bg-white px-3 py-2" href="#conteudo">Pular para o conteúdo</a>
  <div class="store-topline"><div class="container">Um espaço pensado com carinho para cães, gatos e seus tutores</div></div>
  <header class="store-header">
    <nav class="container store-nav" aria-label="Navegação da loja">
      <a href="index.php" class="store-brand" aria-label="VittaPet Store, início">
        <span class="store-brand-logo" aria-hidden="true">
          <svg viewBox="0 0 48 48"><ellipse cx="24" cy="31" rx="13" ry="10" transform="rotate(-8 24 31)"/><ellipse cx="11" cy="17" rx="4.1" ry="6" transform="rotate(-22 11 17)"/><ellipse cx="21" cy="12" rx="4.3" ry="6" transform="rotate(-8 21 12)"/><ellipse cx="32" cy="14" rx="4.1" ry="6" transform="rotate(14 32 14)"/><ellipse cx="40" cy="23" rx="3.4" ry="5" transform="rotate(27 40 23)"/></svg>
        </span>
        <span>VittaPet<span class="store-brand-dot">.</span></span>
      </a>
      <div class="store-navlinks">
        <a href="#produtos">Produtos</a>
        <a href="#onde-estamos">Onde estamos</a>
        <a href="rastrear.php">Rastrear pedido</a>
      </div>
      <div class="store-nav-actions">
        <a href="carrinho.php" class="store-cart-link">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 10h10.7l2.1-7H6"/><circle cx="9" cy="19" r="1.5"/><circle cx="18" cy="19" r="1.5"/></svg>
          Carrinho
          <?php if ($cartCount > 0): ?><span class="store-cart-count" aria-label="<?= (int)$cartCount ?> itens"><?= (int)$cartCount ?></span><?php endif; ?>
        </a>
      </div>
    </nav>
  </header>

  <main id="conteudo">
    <div class="container">
      <section class="store-hero" aria-labelledby="hero-title">
        <div class="row g-0">
          <div class="col-lg-7">
            <div class="store-hero-inner">
              <span class="store-eyebrow">Cuidado em cada detalhe</span>
              <h1 id="hero-title">O melhor para quem faz sua vida mais feliz.</h1>
              <p>Produtos para alimentar, brincar e cuidar de quem está sempre ao seu lado. Escolha com carinho, compre com praticidade.</p>
              <a href="#produtos" class="store-main-button">Conhecer produtos <span aria-hidden="true">→</span></a>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="store-hero-promo" aria-hidden="true">
              <span class="store-hero-circle"></span>
              <div class="store-hero-graphic">
                <svg viewBox="0 0 240 240" role="presentation">
                  <path fill="#fff" d="M120 40C64 40 28 77 28 129s42 89 92 89 92-38 92-89-36-89-92-89Z"/>
                  <path fill="#d99656" d="M71 98Q38 77 52 42q27 0 52 32M169 98q33-21 19-56-27 0-52 32"/>
                  <path fill="#f2b775" d="M65 116C65 75 88 55 120 55s55 20 55 61v44c0 32-26 55-55 55s-55-23-55-55Z"/>
                  <ellipse cx="97" cy="125" rx="6" ry="8" fill="#2c423f"/><ellipse cx="143" cy="125" rx="6" ry="8" fill="#2c423f"/>
                  <ellipse cx="120" cy="156" rx="16" ry="12" fill="#fff3dc"/>
                  <path d="M113 149q7-6 14 0-1 7-7 9-6-2-7-9Z" fill="#3c5148"/>
                  <path d="M120 157v5m0 0q-7 8-16 1m16-1q7 8 16 1" fill="none" stroke="#3c5148" stroke-width="3" stroke-linecap="round"/>
                  <path d="M80 181Q120 201 160 181" stroke="#0f766e" stroke-width="13" fill="none" stroke-linecap="round"/>
                  <circle cx="122" cy="193" r="8" fill="#e7b05d"/>
                </svg>
              </div>
              <span class="store-hero-float">Amor que se cuida</span>
            </div>
          </div>
        </div>
      </section>
      <section class="store-benefits" aria-label="Benefícios da loja">
        <div class="store-benefit">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7M6 20h3m8 0h3M7 17v3m11-3v3"/></svg>
          <div><strong>Entrega organizada</strong><span>Consulte a cobertura antes de finalizar.</span></div>
        </div>
        <div class="store-benefit">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v13H4zM4 10h16M7 15h5"/></svg>
          <div><strong>Pagamento flexível</strong><span>Pix ou cartão na entrega.</span></div>
        </div>
        <div class="store-benefit">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-8-5-8-11a4.5 4.5 0 0 1 8-2.7A4.5 4.5 0 0 1 20 10c0 6-8 11-8 11Z"/></svg>
          <div><strong>Carinho em cada pedido</strong><span>Produtos escolhidos para o seu pet.</span></div>
        </div>
      </section>


      <?php if ($categorias): ?>
      <section class="store-showcase" aria-labelledby="showcase-categories">
        <div class="store-showcase-head">
          <div><span class="store-section-kicker">Escolha por categoria</span><h2 class="store-section-title" id="showcase-categories">Encontre o que seu pet precisa</h2></div>
          <div class="store-slider-controls" aria-label="Controles de categorias">
            <button type="button" data-prev="categories-slider" aria-label="Categorias anteriores">‹</button>
            <button type="button" data-next="categories-slider" aria-label="Próximas categorias">›</button>
          </div>
        </div>
        <div id="categories-slider" class="store-slider" data-showcase-slider role="region" aria-roledescription="carrossel" aria-label="Categorias da VittaPet" tabindex="0">
          <?php foreach ($categorias as $cat): ?>
          <a href="#produtos" class="store-slide" data-category-pick="<?=e((string)$cat['nome'])?>">
            <span class="store-slide-symbol" aria-hidden="true">✦</span>
            <strong><?=e((string)$cat['nome'])?></strong>
            <small>Explorar produtos desta categoria →</small>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="store-slider-dots" data-dots="categories-slider" aria-label="Selecionar categoria do carrossel"></div>
      </section>
      <?php endif; ?>
      <?php if ($produtos): ?>
      <section class="store-showcase" aria-labelledby="showcase-products">
        <div class="store-showcase-head">
          <div><span class="store-section-kicker">Seleção da loja</span><h2 class="store-section-title" id="showcase-products">Destaques da VittaPet</h2></div>
          <div class="store-slider-controls" aria-label="Controles dos destaques">
            <button type="button" data-prev="featured-slider" aria-label="Produtos anteriores">‹</button>
            <button type="button" data-next="featured-slider" aria-label="Próximos produtos">›</button>
          </div>
        </div>
        <div id="featured-slider" class="store-slider" data-showcase-slider role="region" aria-roledescription="carrossel" aria-label="Produtos em destaque" tabindex="0">
          <?php foreach (array_slice($produtos, 0, 8) as $featured):
            $featuredName = str_starts_with((string)$featured['nome'], 'DEMO - ') ? substr((string)$featured['nome'], 7) : (string)$featured['nome'];
          ?>
          <a href="#produto-<?= (int)$featured['id'] ?>" class="store-slide">
            <span class="store-slide-symbol" aria-hidden="true">✦</span>
            <small><?=e((string)$featured['categoria'])?></small>
            <strong><?=e($featuredName)?></strong>
            <span class="store-slide-price"><?=money($featured['preco_venda'])?></span>
            <small>Ver produto →</small>
          </a>
          <?php endforeach; ?>
        </div>
        <div class="store-slider-dots" data-dots="featured-slider" aria-label="Selecionar produto do carrossel"></div>
      </section>
      <?php endif; ?>

      <section id="produtos" class="store-section" aria-labelledby="products-title">
        <div class="store-section-kicker">Seu pet merece</div>
        <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
          <div>
            <h2 id="products-title" class="store-section-title">Nossa loja</h2>
            <p class="store-section-description mb-0">Explore alimentos, brinquedos e cuidados para o dia a dia.</p>
          </div>
          <span id="catalog-count" class="store-notice" aria-live="polite"><?=count($produtos)?> produtos disponíveis</span>
        </div>
        <?php if ($demoCount > 0): ?>
          <p class="store-notice mt-3">Catálogo de demonstração: produtos identificados como DEMO são fictícios e destinados apenas a testes.</p>
        <?php endif; ?>
        <div class="store-filters">
          <label class="store-search">
            <span class="visually-hidden">Buscar produtos</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
            <input id="busca-produtos" type="search" placeholder="Buscar por nome ou produto" autocomplete="off">
          </label>
          <label for="filtro" class="visually-hidden">Filtrar por categoria</label>
          <select id="filtro" class="form-select" aria-label="Filtrar por categoria">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?=e($cat['nome'])?>"><?=e($cat['nome'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php if ($erro): ?><div class="alert alert-warning" role="alert"><?=e($erro)?></div><?php endif; ?>
        <div class="row g-3 g-lg-4" id="catalog-grid">
          <?php foreach ($produtos as $p):
            $isDemo = str_starts_with((string)$p['nome'], 'DEMO - ');
            $labelName = $isDemo ? substr((string)$p['nome'], 7) : (string)$p['nome'];
          ?>
          <div class="col-6 col-md-4 col-xl-3 produto" data-category="<?=e($p['categoria'])?>" data-search="<?=e(mb_strtolower($labelName.' '.(string)$p['categoria'].' '.(string)($p['descricao']??'')))?>">
            <article id="produto-<?= (int)$p['id'] ?>" class="store-product-card">
              <div class="store-product-media" data-category="<?=e($p['categoria'])?>">
                <?php if ($p['imagem_path']): ?>
                  <img src="<?=e($p['imagem_path'])?>" alt="Produto <?=e($labelName)?>" loading="lazy">
                <?php else: ?>
                  <?php if ($p['categoria'] === 'Rações'): ?>
                    <svg viewBox="0 0 96 96" aria-hidden="true"><path d="M18 52h60l-8 27H26L18 52Z" stroke-linejoin="round"/><path d="M27 52c0-13 9-23 21-23s21 10 21 23" /><path d="M27 68h42"/><circle cx="39" cy="43" r="3"/><circle cx="54" cy="40" r="3"/></svg>
                  <?php elseif ($p['categoria'] === 'Brinquedos'): ?>
                    <svg viewBox="0 0 96 96" aria-hidden="true"><circle cx="48" cy="48" r="30"/><path d="M20 40Q50 30 77 59M38 22q25 24 17 55M20 54l51-25"/></svg>
                  <?php elseif ($p['categoria'] === 'Acessórios'): ?>
                    <svg viewBox="0 0 96 96" aria-hidden="true"><path d="M25 22q24-15 46 0v49q-24 16-46 0V22Z" stroke-linejoin="round"/><path d="M25 35q22 9 46 0M25 58q22 9 46 0"/><circle cx="48" cy="47" r="8"/></svg>
                  <?php else: ?>
                    <svg viewBox="0 0 96 96" aria-hidden="true"><rect x="28" y="36" width="40" height="43" rx="8"/><path d="M36 36V23h24v13M39 22v-7h18v7M40 59h16M48 48v22" stroke-linecap="round"/><circle cx="72" cy="20" r="6"/><circle cx="20" cy="33" r="4"/></svg>
                  <?php endif; ?>
                <?php endif; ?>
                <?php if ($isDemo): ?><span class="store-demo-tag">DEMO · TESTE</span><?php endif; ?>
                <span class="store-stock-tag <?= (int)$p['estoque_atual'] === 0 ? 'is-out' : '' ?>"><?= (int)$p['estoque_atual'] === 0 ? 'Esgotado' : 'Disponível' ?></span>
              </div>
              <div class="store-product-body">
                <span class="store-product-category"><?=e($p['categoria'])?></span>
                <h3 class="store-product-name"><?=e($labelName)?></h3>
                <p class="store-product-desc"><?=e(mb_strimwidth((string)($p['descricao']??''), 0, 95, '…'))?></p>
                <div class="store-product-price"><?=money($p['preco_venda'])?></div>
                <form method="post" action="carrinho.php">
                  <input type="hidden" name="csrf" value="<?=e(csrf())?>">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <input type="hidden" name="quantity" value="1">
                  <input type="hidden" name="action" value="add">
                  <button class="store-add-button" type="submit" <?= (int)$p['estoque_atual'] === 0 ? 'disabled' : '' ?>>
                    <?= (int)$p['estoque_atual'] === 0 ? 'Produto esgotado' : 'Adicionar ao carrinho' ?>
                  </button>
                </form>
              </div>
            </article>
          </div>
          <?php endforeach; ?>
        </div>
        <div id="catalog-empty" class="store-no-results" hidden>Nenhum produto corresponde à sua busca. Tente outro termo ou categoria.</div>
        <?php if (!$produtos): ?><div class="store-no-results">Nenhum produto disponível no momento.</div><?php endif; ?>
      </section>
    </div>

    <section class="store-location py-5 mt-4" id="onde-estamos" aria-labelledby="location-title">
      <div class="container location-grid">
        <div class="location-panel">
          <span class="store-section-kicker">Visite e conheça</span>
          <h2 class="store-section-title" id="location-title">Onde estamos</h2>
          <p class="location-address mb-0"><?= $endereco !== '' ? e($endereco) : 'A localização da loja será informada em breve.' ?></p>
          <?php if ($temMapa): ?>
            <div class="location-links">
              <a class="store-main-button" href="<?=e($rotaGoogle)?>" target="_blank" rel="noopener noreferrer">Rotas no Google Maps</a>
              <a class="btn btn-outline-primary" href="<?=e($rotaWaze)?>" target="_blank" rel="noopener noreferrer">Abrir no Waze</a>
            </div>
          <?php endif; ?>
          <p id="store-map-status" class="map-status text-muted" role="status" aria-live="polite"><?= $temMapa ? 'Carregando mapa...' : 'Localização ainda não configurada.' ?></p>
        </div>
        <?php if ($temMapa): ?>
          <div id="store-map" class="map-frame" tabindex="0" role="region" aria-label="Mapa com a localização da VittaPet Store"
               data-lat="<?=e((string)$loja['latitude'])?>" data-lon="<?=e((string)$loja['longitude'])?>" data-name="<?=e((string)$loja['nome'])?>"></div>
        <?php else: ?>
          <div class="map-frame d-flex align-items-center justify-content-center text-muted px-4 text-center">
            Mapa disponível após configurar o endereço da loja.
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="store-footer">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-7">
          <div class="store-footer-title">VittaPet<span class="store-brand-dot">.</span> Store</div>
          <p class="store-footer-note mt-2">Tudo o que seu pet precisa, com carinho, qualidade e praticidade. Explore nosso catálogo e acompanhe cada pedido.</p>
        </div>
        <div class="col-md-5">
          <div class="d-flex flex-column align-items-md-end gap-2">
            <a href="#produtos">Explorar produtos</a>
            <a href="#onde-estamos">Onde estamos</a>
            <a href="rastrear.php">Acompanhar pedido</a>
          </div>
        </div>
      </div>
      <div class="store-footer-bottom">
        <small>© <?=date('Y')?> VittaPet Store · Projeto acadêmico</small>
        <a class="store-admin-link ms-md-auto" href="login.php">Área administrativa</a>
      </div>
    </div>
  </footer>
  <script defer src="assets/js/storefront.js"></script>
  <script defer src="assets/js/storefront-interactions.js"></script>
  <?php if ($temMapa): ?>
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script defer src="assets/js/mapa.js"></script>
  <?php endif; ?>
</body>
</html>

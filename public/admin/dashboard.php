<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/admin_header.php';
requireAdmin();

$today = new DateTimeImmutable('today');
$start = (string)($_GET['inicio'] ?? $today->format('Y-m-01'));
$finish = (string)($_GET['fim'] ?? $today->format('Y-m-d'));

function isValidDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

if (!isValidDate($start) || !isValidDate($finish) || $start > $finish) {
    http_response_code(400);
    exit('Período inválido.');
}

$end = (new DateTimeImmutable($finish))->modify('+1 day')->format('Y-m-d');
$error = null;
$legacyConfirmed = 0;
$receipt = 0.0;
$cost = 0.0;
$expense = 0.0;
$paid = 0;
$pending = 0;
$low = 0;
$daily = [];
$top = [];

try {
    $pdo = db();

    // Preservar a regra existente: recebimentos ainda confirmados, filtrados por pago_em.
    $query = $pdo->prepare(
        "SELECT COUNT(*) quantidade, COALESCE(SUM(total),0) total
         FROM pedidos WHERE status_pagamento='confirmado' AND pago_em>=? AND pago_em<?"
    );
    $query->execute([$start, $end]);
    $summary = $query->fetch();
    $paid = (int)$summary['quantidade'];
    $receipt = (float)$summary['total'];

    $query = $pdo->prepare(
        "SELECT COALESCE(SUM(i.quantidade*i.custo_unitario),0)
         FROM pedido_itens i JOIN pedidos p ON p.id=i.pedido_id
         WHERE p.status_pagamento='confirmado' AND p.pago_em>=? AND p.pago_em<?"
    );
    $query->execute([$start, $end]);
    $cost = (float)$query->fetchColumn();

    $query = $pdo->prepare(
        'SELECT COALESCE(SUM(valor),0) FROM despesas WHERE data_despesa>=? AND data_despesa<=?'
    );
    $query->execute([$start, $finish]);
    $expense = (float)$query->fetchColumn();

    $legacyConfirmed = (int)$pdo->query(
        "SELECT COUNT(*) FROM pedidos WHERE status_pagamento='confirmado' AND pago_em IS NULL"
    )->fetchColumn();
    $pending = (int)$pdo->query(
        "SELECT COUNT(*) FROM pedidos WHERE status_logistico NOT IN ('cancelado','entregue')"
    )->fetchColumn();
    $low = (int)$pdo->query(
        'SELECT COUNT(*) FROM produtos WHERE ativo=1 AND estoque_atual<=estoque_minimo'
    )->fetchColumn();

    $query = $pdo->prepare(
        "SELECT DATE(pago_em) dia,SUM(total) valor
         FROM pedidos WHERE status_pagamento='confirmado' AND pago_em>=? AND pago_em<?
         GROUP BY DATE(pago_em) ORDER BY dia"
    );
    $query->execute([$start, $end]);
    $daily = $query->fetchAll();

    $query = $pdo->prepare(
        "SELECT p.nome,SUM(i.quantidade) unidades
         FROM pedido_itens i JOIN pedidos o ON o.id=i.pedido_id
         JOIN produtos p ON p.id=i.produto_id
         WHERE o.status_pagamento='confirmado' AND o.pago_em>=? AND o.pago_em<?
         GROUP BY i.produto_id,p.nome ORDER BY unidades DESC LIMIT 5"
    );
    $query->execute([$start, $end]);
    $top = $query->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Falha ao carregar indicadores. Verifique as migrations.';
}

// As barras usam dados existentes do SELECT, sem série artificial e sem JavaScript/CDN.
$chartDays = array_slice($daily, -14);
$chartMaximum = 0.0;
foreach ($chartDays as $day) {
    $chartMaximum = max($chartMaximum, (float)$day['valor']);
}

$metrics = [
    ['Receita confirmada', money($receipt), 'Recebimentos ainda confirmados', 'is-primary', 'R$'],
    ['Resultado estimado', money($receipt - $cost - $expense), 'Receita − CMV − despesas', '', '='],
    ['Pedidos em andamento', (string)$pending, 'Pedidos não finalizados', '', '#'],
    ['Estoque baixo', (string)$low, 'Produtos abaixo ou no mínimo', $low > 0 ? 'is-warning' : '', '!'],
    ['CMV estimado', money($cost), 'Custo dos itens vendidos', '', 'R$'],
    ['Despesas', money($expense), 'Registradas no período', '', 'R$'],
    ['Ticket médio', money($paid ? $receipt / $paid : 0), 'Por pedido confirmado', '', '÷'],
];

$presets = [
    ['Hoje', $today->format('Y-m-d'), $today->format('Y-m-d')],
    ['7 dias', $today->modify('-6 days')->format('Y-m-d'), $today->format('Y-m-d')],
    ['30 dias', $today->modify('-29 days')->format('Y-m-d'), $today->format('Y-m-d')],
];
?><!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Visão geral — VittaPet Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/tokens.css" rel="stylesheet">
  <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-ui">
<?php renderAdminHeader('dashboard'); ?>
<main id="conteudo" class="admin-shell">
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-4 mb-4">
    <div>
      <div class="admin-eyebrow">Painel administrativo</div>
      <h1 class="admin-title">Visão geral</h1>
      <p class="admin-subtitle">Acompanhe vendas, despesas e a operação da VittaPet.</p>
    </div>
    <div class="d-flex flex-column gap-2 align-items-start align-items-lg-end">
      <div class="admin-presets" aria-label="Períodos rápidos">
        <?php foreach ($presets as [$label, $from, $to]): ?>
        <a href="dashboard.php?inicio=<?=e($from)?>&amp;fim=<?=e($to)?>"><?=e($label)?></a>
        <?php endforeach; ?>
      </div>
      <form class="admin-filter-panel" method="get" aria-label="Filtrar indicadores por período">
        <label for="inicio">Início
          <input id="inicio" class="form-control form-control-sm" type="date" name="inicio" value="<?=e($start)?>" required>
        </label>
        <label for="fim">Fim
          <input id="fim" class="form-control form-control-sm" type="date" name="fim" value="<?=e($finish)?>" required>
        </label>
        <button class="btn btn-primary btn-sm" type="submit">Filtrar</button>
      </form>
    </div>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger" role="alert"><?=e($error)?></div>
  <?php else: ?>
    <section aria-label="Indicadores comerciais">
      <div class="row g-3 mb-4">
        <?php foreach ($metrics as [$label, $value, $hint, $variant, $icon]): ?>
          <div class="col-12 col-sm-6 col-xl-3">
            <article class="admin-metric h-100 <?=e($variant)?>">
              <span class="admin-metric-icon" aria-hidden="true"><?=e($icon)?></span>
              <div class="admin-metric-label"><?=e($label)?></div>
              <div class="admin-metric-value"><?=e($value)?></div>
              <p class="admin-metric-hint"><?=e($hint)?></p>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="row g-3">
      <section class="col-lg-7" aria-labelledby="sales-title">
        <div class="admin-surface">
          <h2 class="admin-section-title" id="sales-title">Vendas por dia</h2>
          <p class="admin-section-help">Receita de pagamentos ainda confirmados no período selecionado.</p>
          <?php if (!$daily): ?>
            <div class="admin-empty">
              <strong>Nenhuma venda confirmada no período</strong>
              <p>Quando houver recebimentos confirmados, os valores aparecerão aqui.</p>
              <a href="pedidos.php" class="btn btn-outline-primary btn-sm">Consultar pedidos</a>
            </div>
            <p class="small text-muted mt-3">Sem vendas confirmadas neste período.</p>
          <?php else: ?>
            <div class="admin-chart" role="img" aria-label="Gráfico de barras: últimos até 14 dias com vendas confirmadas no período">
              <?php foreach ($chartDays as $day):
                  $amount = (float)$day['valor'];
                  $width = $chartMaximum > 0 ? min(100, max(0, $amount / $chartMaximum * 100)) : 0;
                  $dateLabel = (new DateTimeImmutable((string)$day['dia']))->format('d/m/Y');
              ?>
                <div class="admin-chart-row">
                  <span class="admin-chart-label"><?=e($dateLabel)?></span>
                  <div class="admin-chart-track">
                    <div class="admin-chart-bar" style="width:<?=number_format($width, 2, '.', '')?>%"></div>
                  </div>
                  <span class="admin-chart-value"><?=e(money($amount))?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <p class="small text-muted">Exibidos os últimos <?=count($chartDays)?> dias com recebimentos registrados. Dias sem venda não são desenhados.</p>
            <details class="admin-table-details">
              <summary>Visualizar tabela completa do período</summary>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead><tr><th scope="col">Data</th><th scope="col" class="text-end">Receita</th></tr></thead>
                  <tbody>
                    <?php foreach ($daily as $day): ?>
                    <tr><td><?=e($day['dia'])?></td><td class="text-end"><?=e(money($day['valor']))?></td></tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </details>
          <?php endif; ?>
        </div>
      </section>
      <section class="col-lg-5" aria-labelledby="top-products-title">
        <div class="admin-surface">
          <h2 class="admin-section-title" id="top-products-title">Produtos mais vendidos</h2>
          <p class="admin-section-help">Quantidade em pedidos com pagamento confirmado.</p>
          <?php if (!$top): ?>
            <div class="admin-empty">
              <strong>Ainda não há produtos no ranking</strong>
              <p>O ranking será preenchido conforme os pagamentos forem confirmados.</p>
              <a href="produtos.php" class="btn btn-outline-primary btn-sm">Gerenciar produtos</a>
            </div>
          <?php else: ?>
            <?php foreach ($top as $index => $product): ?>
              <div class="admin-best-seller">
                <span><?= (int)$index + 1 ?>. <?=e($product['nome'])?></span>
                <strong><?= (int)$product['unidades'] ?> un.</strong>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </div>

    <?php if ($legacyConfirmed > 0): ?>
      <div class="alert alert-warning mt-4" role="alert">
        Existem <?= (int)$legacyConfirmed ?> pedidos confirmados antes da migração 009 sem data de recebimento.
        Eles não entram nos indicadores filtrados até conciliação administrativa. Não atribuímos datas fictícias.
      </div>
    <?php endif; ?>

    <p class="admin-note mt-4">
      O filtro utiliza a data de confirmação do recebimento (<code>pago_em</code>).
      Pedidos integralmente estornados deixam de compor o saldo de pedidos confirmados, inclusive em períodos históricos.
      Os indicadores são um retrato do saldo atual, não um fluxo de caixa histórico ou contabilidade fiscal.
      O CMV é estimado e não inclui custos indiretos de operação.
    </p>
  <?php endif; ?>
</main>
</body>
</html>

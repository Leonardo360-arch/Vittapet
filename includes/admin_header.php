<?php
declare(strict_types=1);

/**
 * Cabeçalho administrativo reutilizável.
 * O controller deve chamar requireAdmin() antes de renderizar este componente.
 */
function renderAdminHeader(string $active = 'dashboard'): void
{
    $items = [
        'dashboard' => ['dashboard.php', 'Visão geral'],
        'produtos' => ['produtos.php', 'Produtos'],
        'pedidos' => ['pedidos.php', 'Pedidos'],
        'financeiro' => ['financeiro.php', 'Financeiro'],
        'configuracoes' => ['configuracoes.php', 'Configurações'],
    ];
    ?>
    <a class="admin-skip" href="#conteudo">Pular para o conteúdo</a>
    <header class="admin-header">
      <div class="admin-header-inner">
        <a class="admin-brand" href="dashboard.php" aria-label="VittaPet — painel administrativo">
          <span class="admin-brand-mark" aria-hidden="true">V</span>
          <span class="admin-brand-label">VittaPet <small>PAINEL</small></span>
        </a>
        <nav class="admin-navigation" aria-label="Navegação administrativa">
          <?php foreach ($items as $key => [$href, $label]): ?>
            <a href="<?=e($href)?>" class="admin-nav-link<?= $active === $key ? ' is-active' : '' ?>"
               <?= $active === $key ? 'aria-current="page"' : '' ?>><?=e($label)?></a>
          <?php endforeach; ?>
        </nav>
        <div class="admin-account">
          <a href="../index.php" class="admin-view-store">Ver loja</a>
          <form method="post" action="../logout.php">
            <input type="hidden" name="csrf" value="<?=e(csrf())?>">
            <button class="admin-logout" type="submit">Sair</button>
          </form>
        </div>
      </div>
    </header>
    <?php
}

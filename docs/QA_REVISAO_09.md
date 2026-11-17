# QA Revisão 09 — Dashboard administrativo e revisão de design

**Versão:** 1.0.0-dev.8 (branch de desenvolvimento)  
**Data:** 17/11/2026  
**Base:** auditoria de `public/admin/dashboard.php`, `public/assets/css/tokens.css`, `docs/DESIGN_SYSTEM.md` e captura enviada da interface localhost.

## Diagnóstico constatado antes da implementação
- Dashboard apresentava sete métricas visualmente equivalentes; ausência de hierarquia de receita e resultado.
- Menu superior simples, com ação de logout inserida entre os links, sem indicação da página ativa.
- "Vendas por dia" era apenas uma tabela sem representação gráfica.
- Estado vazio era limitado a texto sem ação.
- Não havia opções rápidas de período.
- Estrutura administrativa possui repetição de menus, CSS e HTML entre controllers PHP.

## Solução dev.8
- Navegação compartilhada em `includes/admin_header.php` com link de pular para conteúdo, `aria-current` e saída em POST+CSRF; aplicado ao dashboard neste ciclo.
- `public/assets/css/admin.css` usa cores existentes (verde-petróleo/laranja), tipografia, cartões e media queries sem nova dependência de runtime.
- KPIs: receita confirmada em destaque; resultado estimado, pedidos em andamento, estoque baixo, CMV, despesas e ticket médio em cards de mesma estrutura.
- Gráfico horizontal de até 14 dias que possuem recebimentos confirmados, utilizando exclusivamente as linhas SQL já existentes. Tabela de todos os dias do período fica acessível via `details`.
- Estados vazios com links para consulta de pedidos e gerência de produtos. Não são inseridos pedidos fictícios.
- Filtros de Hoje, 7 e 30 dias, além do período personalizado; datas continuam validadas pelo servidor.

## Preservação de negócio
Sem alteração de tabelas ou migrations. As consultas financeiras mantêm a regra de `status_pagamento='confirmado'` e `pago_em`, com exclusão dos pedidos já estornados do saldo atual. Não se trata de relatório contábil histórico nem um gráfico de entrada/saída bancária. Pedidos legados sem `pago_em` seguem em alerta de conciliação.

## Evidências CI dev.8
- GitHub Actions: [execução 44124536164](https://github.com/Leonardo360-arch/Vittapet/actions/runs/44124536164), commit `76153ad6b2e6eada556313b171a17bd9525b95f3`.
- `php-lint`: aprovado.
- `local-smoke` (Docker, MySQL e vitrine): aprovado.
- `mysql-http`: aprovado com **78 asserções HTTP/MySQL** e teste concorrente real (1 venda, 1 rejeição, estoque final 0).
- Playwright Chromium: **18 testes aprovados**, incluindo três execuções do novo cenário administrativo em viewport mobile/tablet/desktop.
- Restrições: emulação não equivale a inspeção visual física; o componente compartilhado ainda não foi aplicado em todas as páginas administrativas.

## Gate de validação
- [x] `php-lint`: PHP dos componentes sem erro.
- [x] Suíte HTTP/MySQL: 78 asserções sem regressões.
- [x] Concorrência: sem regressão da venda da última unidade.
- [x] Playwright: dashboard testado nas três resoluções emuladas (18 cenários totais).
- [x] HTML sem overflow horizontal nas resoluções utilizadas pelo Playwright.
- [ ] Acessibilidade: foco, links e leitura das tabelas; inspeção manual com teclado/leitor de tela pendente.
- [ ] Revisão de screenshots reais após atualização do localhost.
- [ ] Aplicação do cabeçalho nas demais páginas administrativas em etapa posterior.

Não emitir selo de QA final ou promover para `rc.1` antes de conclusão dos testes obrigatórios de integração real e dispositivos físicos.

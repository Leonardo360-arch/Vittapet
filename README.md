# VittaPet Store — MVP acadêmico (v1.0)

Loja virtual de produtos pet em PHP 8, MySQL e JavaScript, preparada para evoluir para o ecossistema VittaPet.

## Escopo obrigatório da v1.0

**Somente loja:** catálogo, categorias, carrinho, checkout, histórico de pedidos, acompanhamento de embalagem e entrega, estoque, painel administrativo, indicadores e relatórios financeiros. Não existem serviços veterinários, banho e tosa ou agendamento nesta versão.

### Requisitos acadêmicos de DW2 — entrega em 23/11/2026
- [ ] CRUD completo e real de produtos usando MySQL e PHP
- [ ] Interface original e responsiva
- [ ] Consumo e apresentação de JSON de duas APIs (ViaCEP + Dog CEO como proposta)
- [ ] Publicação verificável no InfinityFree
- [ ] Documentação técnica e credenciais de teste (quando necessário), entregues no Teams

### Fluxo comercial alvo
Produto -> Carrinho -> Checkout -> Pedido aguardando pagamento -> Em separação -> Embalado -> Saiu para entrega -> Entregue. Cancelamento e exceções devem ser tratados com histórico. **Não marcar pagamento como confirmado sem confirmação confiável**; para o MVP podem existir meios manuais explicitamente identificados.

### Dashboard alvo
Receita confirmada, despesas, resultado estimado (com custo de mercadorias), pedidos por status, ticket médio, produtos mais vendidos, estoque baixo e gráficos por período. Dados demonstrativos devem ser identificados e não confundidos com dados reais.

### Segurança
Autenticação e autorização administrativa, password_hash/password_verify, PDO parametrizado, CSRF para alterações, transações de pedidos/estoque, segredos fora da pasta pública, rastreio por token não enumerável, logs sem dados sensíveis e validação de entradas.

## Versão 2.0 — Ecossistema integrado
A documentação VittaPet v2.1 anterior passa a ser **especificação de evolução**: tutores/pets, clínica, prontuários, vacinas, exames, banho e tosa, agenda, portal do tutor, VittaBot, mural, fidelidade, concursos, notificações e serviços integrados. A versão 2.0 do produto não significa que todos os módulos existentes no documento original tenham sido implementados.

## Arquitetura
Monólito modular em PHP/PDO/MySQL, com camadas de apresentação, controllers, services e repositories, migrations incrementais e apenas arquivos públicos expostos. Evitar padrões de projeto sem justificativa. A arquitetura deve permitir adicionar novos módulos sem refazer o catálogo, os usuários, o financeiro e os pedidos.

## Documentação e andamento
- [Roadmap e escopo detalhado](docs/ROADMAP.md)
- Estado atual: **planejamento atualizado; implementação e testes ainda pendentes**.

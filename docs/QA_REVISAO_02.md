# QA — Revisão 02: Financeiro e preparo InfinityFree

Data: 17/11/2026. Revisão **estática**; nenhuma execução PHP/MySQL, FTP ou teste HTTP na conta InfinityFree realizado nesta etapa.

## Implementação revisada
- Migration `003_financeiro.sql`: tabela de despesas e índice por data.
- `public/admin/dashboard.php`: cards de receita confirmada, CMV, despesas, resultado estimado, ticket médio, pedidos em andamento, estoque baixo; vendas por data e produtos mais vendidos.
- `public/admin/financeiro.php`: cadastro e listagem de despesas com CSRF e PDO preparado.
- `/.htaccess`: roteamento de URLs de `htdocs` para `public/`, proibição de rotas privadas; arquivos `.htaccess` internos como segunda camada.
- `deploy/INFINITYFREE.md`: implantação, banco MySQL do painel, regras de segurança e testes de bloqueio.
- Navegação entre telas administrativas.

## Restrições conhecidas
1. Não declarar esta etapa pronta para venda real: pagamento manual sem conciliação automática; frete zero demonstrativo; ausência de estorno completo.
2. Dashboard usa **data de criação do pedido**, não data exata de confirmação do pagamento (histórico financeiro futuro deverá corrigir essa dimensão).
3. Resultado é **estimado**: não cobre tributos, tarifas, perdas, ajustes e custos indiretos ausentes.
4. A proteção por `.htaccess` deve ser validada na instalação real; não enviar banco SQL, logs, backups, `.git`, documentação desnecessária nem segredos para `htdocs`.
5. Login e rastreio ainda exigem rate limiting e testes de segurança antes da implantação.
6. Rotas e rewrite precisam ser validados no servidor Apache da hospedagem; podem exigir ajuste conforme a configuração efetiva.
7. API 2 de geocodificação/mapa ainda não implementada; ViaCEP presente no checkout, mas falta teste real.
8. Falta teste de banco com as três migrations, teste de transações e teste visual no navegador.

## Checklist de implantação obrigatório
- [ ] PHP do host aceita código sem erro de sintaxe e dependências necessárias (PDO/mysql, mbstring).
- [ ] Migrations 001, 002 e 003 importadas no MySQL provisionado.
- [ ] Host MySQL e nome do banco copiados do painel, sem valores presumidos.
- [ ] `/`, `/login.php`, `/admin/dashboard.php`, `/assets/css/tokens.css` e checkout respondem corretamente.
- [ ] `/config/local.php`, `/includes/bootstrap.php`, `/database/migrations/001_store_base.sql` bloqueados.
- [ ] CRUD, carrinho, pedido, baixa de estoque, rastreio, pagamento e financeiro testados.
- [ ] HTTPS ativo; nenhum segredo apareceu no GitHub ou na página.
- [ ] API de geolocalização e mapa testados na versão final antes da entrega acadêmica.
- [ ] Evidências documentadas e URL pública testada por outro dispositivo.

**Parecer:** revisão estrutural concluída; execução ainda NÃO APROVADA. A branch pode integrar a base de desenvolvimento mediante transparência, mas não autoriza publicar como release estável.

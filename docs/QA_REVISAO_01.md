# QA — Revisão técnica 01 (17/11/2026)

**Objeto:** primeiro incremento da VittaPet Store v1.0 na branch `develop`.
**Método:** inspeção estática dos arquivos e revisão do fluxo. **Não foi possível executar a aplicação com um banco MySQL configurado ou validar a publicação no InfinityFree neste ciclo.** Logo, nenhum teste end-to-end foi aprovado.

## Estrutura revisada
- `config/database.php` conexão PDO e credenciais em `config/local.php` ignorado pelo Git.
- `database/migrations/001_store_base.sql`: usuários, categorias e produtos.
- `database/migrations/002_pedidos.sql`: clientes, pedidos, itens, histórico e movimentação de estoque.
- `includes/bootstrap.php`: sessão, utilitários e CSRF.
- `public/index.php`, `public/carrinho.php`, `public/checkout.php`, `public/rastrear.php`: início do fluxo de compras.
- `public/admin/produtos.php`, `public/admin/pedidos.php`: administração inicial.
- `public/assets/css/tokens.css`: tokens visuais iniciais.
- `docs/`: requisitos, identidade visual, roadmap e instalação.

## Pareceres
**Pontos positivos constatados por inspeção:**
- PDO com prepared statements; senha prevista com hash.
- Publicação pensada com webroot separado.
- Carrinho usa sessão e subtotal atualizado por Fetch; checkout recalcula preços via banco.
- Pedido, itens, baixa de estoque e histórico dentro de transação com bloqueio de linhas de produtos.
- Status financeiro separado do status logístico; confirmação de pagamento explicitamente manual.
- Rastreio não exibe nome, endereço ou itens.

**Pendências e riscos abertos (não aprovar produção):**
1. **Alta:** faltam testes integrados da migration, CRUD, login, checkout, concorrência de estoque e rastreio em PHP/MySQL real.
2. **Alta:** política de frete ainda não configurada; checkout usa frete zero como **demonstração**. Não utilizar para operação comercial real.
3. **Alta:** login ainda requer proteção persistente contra força bruta e limitação de tentativas; rota de rastreio necessita rate limit.
4. **Média:** logout por GET e ações administrativas precisam de revisão completa de CSRF/autorização; exigir logout via POST em endurecimento.
5. **Média:** upload de imagens não implementado; catálogo aceita caminho armazenado. Exigir validação/armazenamento de arquivos ao implementar uploads.
6. **Média:** regras de estorno de pagamento e reversão financeira ainda não foram implementadas; cancelamento de pedido pago foi intencionalmente bloqueado.
7. **Média:** dashboard financeiro, despesas e relatórios ainda não implementados.
8. **Média:** segunda API, geocodificação, mapa e envio dos dados JSON para demonstração ainda não implementados.
9. **Média:** verificar compatibilidade de paths e proteção de pastas privadas no InfinityFree; não publicar `config/` em `htdocs` desprotegido.
10. **Média:** falta observabilidade mínima de erro/limites e testes de acessibilidade e responsividade.
11. **Baixa:** código de interfaces e regras de negócio ainda concentrados em páginas PHP; migrar gradualmente para Controllers, Services e Repositories.
12. **Baixa:** validações e mensagens de erro necessitam testes com entrada inválida, consulta simultânea e fluxo repetido.

## Casos mínimos de teste para executar
| ID | Procedimento | Expectativa |
|---|---|---|
| QA01 | Importar 001 e 002 em MySQL 8 | Migrações sem erro |
| QA02 | Entrar com admin válido/inválido | Permissão correta, senha hash |
| QA03 | Criar, listar, editar e desativar produto | Quatro ações persistem |
| QA04 | Adicionar mesmo produto duas vezes | Quantidade e subtotal coerentes |
| QA05 | Editar/remover item sem recarregar página | Atualização instantânea |
| QA06 | Checkout com estoque limitado | Reserva/baixa única, rejeição de excesso |
| QA07 | Dois checkouts simultâneos do último produto | Sem overselling |
| QA08 | Repetir envio de checkout | Não criar duplicidade |
| QA09 | Consultar token válido/inválido | Apenas status/histórico públicos |
| QA10 | Avançar status e cancelar pedido não pago | Histórico e estoque consistentes |
| QA11 | Tentar cancelar pedido pago | Bloqueio até estorno seguro |
| QA12 | Testar ViaCEP e geocodificação | JSON tratado e resultado visível |
| QA13 | Testar hospedagem HTTPS no celular e desktop | Links, CSS, banco e mapa disponíveis |

**Decisão do QA:** incremento de código **entregue para testes**; qualidade ainda **não aprovada**. A documentação e os commits não substituem execução real e evidências.

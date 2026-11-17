# QA Revisão 07 — Concorrência e qualidade de código

**Versão:** 1.0.0-dev.4  
**Data:** 17/11/2026  
**Status inicial:** execução CI e aprovação da etapa ainda necessárias; não representa selo final.

## Etapa 1 — concorrência
Adicionado `tests/concurrency_stock.php`: dois processos PHP, cada um com PDO próprio, disputam a mesma unidade de produto InnoDB. Barreira garante que os dois estejam prontos antes de iniciar. Cada worker inicia transação, bloqueia produtos com `SELECT ... FOR UPDATE`, grava pedido, item e movimento, e finaliza ou faz rollback. A unidade está disponível apenas para um comprador. A validação exige:
- **exatamente 1** commit bem-sucedido e **1** rejeição por estoque insuficiente;
- estoque final 0, **nunca negativo**;
- somente 1 item e 1 movimentação de saída;
- erros inesperados provocam falha do CI;
- conjunto de testes HTTP/MySQL da dev.3 permanece como regressão.

O teste valida a operação transacional de inventário compartilhada pelo checkout; não simula exatamente dois navegadores nem um balanceador externo. Cenários de deadlock multiproduto, falhas de rede e alta carga continuam pendentes.

## Etapa 2 — refatoração SOLID justificada
Extraído `includes/order_inventory.php` com `lockCheckoutInventory(PDO,array)` e `persistCheckoutInventory(PDO,int,array)`. O controlador `public/checkout.php` permanece responsável pelos dados do cliente e pagamento, delegando bloqueio, validação de estoque, inserção de itens e movimentações.

**SRP:** separa responsabilidade de estoque de HTML/controlador, reduz repetição e melhora testes. **DIP pragmático:** PDO passado como dependência em vez de instanciar conexão no módulo. Não cria Factories/Observers artificiais; o MVP ainda não está totalmente em camadas. Não declarar PSR-12 integral.

## Frente Git e produção
Inspecionada árvore atual do repositório, sem arquivo descartável óbvio. `.gitignore` reforçado e empacotador de produção por allowlist adicionado. Não apagar documentação, CI e migrations necessárias ao desenvolvimento. Ver `AUDITORIA_GIT_V1.md`.

## Critérios
- [ ] PHP lint dos arquivos alterados passa.
- [ ] Testes existentes HTTP/MySQL continuam verdes.
- [ ] Dois processos concorrentes: 1 sucesso, 1 rejeição, estoque e movimentos corretos.
- [ ] Pacote `build/htdocs` contém arquivos de runtime e exclui material desnecessário.
- [ ] Nenhuma credencial real ou arquivo de editor incluído.
- [ ] Revisão Git das alterações e merge via PR sem force push.
- [ ] InfinityFree fica como última etapa, sem publicação agora.

**Selo QA global:** não emitido; restam verificações de navegador, APIs, UX, segurança e hosting.

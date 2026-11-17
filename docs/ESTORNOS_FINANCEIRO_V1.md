# Integridade financeira — VittaPet Store 1.0.0-dev.3

**Data:** 17/11/2026. **Escopo:** registro manual de estorno previamente efetuado fora do sistema e cálculo de receita por data efetiva de confirmação.

## 1. Princípio operacional
A VittaPet não envia Pix, estorna cartão, consulta saldo bancário ou devolve dinheiro. O administrador só deve clicar em **Registrar estorno já realizado** após confirmar, diretamente com o meio de pagamento, que o valor foi devolvido. Justificativa com no mínimo 10 e no máximo 500 caracteres é obrigatória. Este mecanismo não substitui comprovantes, conciliação bancária nem política de devolução.

## 2. Migration 009
`pedidos.pago_em` — data de confirmação manual do recebimento, preenchida com `NOW()` no banco no mesmo commit transacional do pagamento.
`pedidos.estornado_em` — data em que o administrador *registra* o estorno externo; pode ser diferente da data bancária efetiva.
`pedidos.observacao_estorno` — referência textual de justificativa e comprovante.

A migração não inventa datas históricas. Pedidos antigos confirmados antes da versão podem ter `pago_em = NULL` e ficar fora dos relatórios por período até reconciliação documental.

## 3. Regras de estorno v1
- Pedido deve estar com `status_pagamento='confirmado'`.
- Só pode estar em `aguardando_pagamento`, `em_separacao` ou `embalado`. Pedidos já despachados ou entregues precisam do procedimento específico de devolução física (fora do escopo do MVP).
- Em transação InnoDB, bloqueia `pedidos` com `FOR UPDATE`, bloqueia itens, valida pagamento e status, devolve quantidades ao estoque e registra `movimentacoes_estoque.tipo='estorno_venda'`.
- Atualiza `status_pagamento='estornado'`, `status_logistico='cancelado'`, `estornado_em=NOW()` e a justificativa; grava histórico `cancelado`.
- Uma segunda tentativa é rejeitada porque status não será mais `confirmado`, evitando reposição duplicada.
- Cancelamento comum de pedido não pago continua disponível e restaura estoque; **pedido pago deve seguir estorno manual previamente comprovado**.

## 4. Indicadores
Os relatórios por período passam a usar `pago_em` para pedidos que seguem com pagamento `confirmado`. Após estorno integral, o pedido deixa a receita líquida agregada, inclusive na consulta de datas anteriores. Isso é um **retrato do saldo financeiro atual**, não um razão contábil de transações por data; faturamento bruto, estornos datados, entradas/saídas reais, tarifas e impostos exigem evolução posterior com um ledger imutável.

**Limites:** `pago_em=NULL` em pedidos legados não compõe indicadores; o dashboard mostra aviso. `CMV` é estimado, sem custos indiretos; a devolução física de pedido despachado não está implementada.

## 5. Critérios de aceite
- [ ] Migration 009 após 008 em MySQL 8.
- [ ] Confirmar Pix ou cartão registra `pago_em`, sem modificar a data de criação.
- [ ] Cancelamento pago não usa caminho de cancelamento simples.
- [ ] Estorno pré-envio exige justificativa e pagamento externo previamente realizado.
- [ ] Em transação, marca estornado/cancelado, restaura estoque uma única vez e registra histórico.
- [ ] Segunda solicitação de estorno é rejeitada sem alteração de estoque.
- [ ] Pedido entregue não usa o atalho de estorno, requer devolução separada.
- [ ] Dashboard exclui pedidos estornados e considera `pago_em`.
- [ ] Mensagens explicam que a aplicação **não transfere dinheiro**.
- [ ] Testes HTTP/MySQL e cenários manuais completos aprovados antes da main.

**Hospedagem InfinityFree:** apenas última fase, após homologação.

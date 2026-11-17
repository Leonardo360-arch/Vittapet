# Fluxo de pagamento v1 — Pix via WhatsApp e cartão na entrega

Data: 17/11/2026. **Implementação inicial na branch; testes PHP/MySQL/InfinityFree pendentes.**

## Modalidades no checkout
| Modalidade | `metodo_pagamento` | `status_pagamento` inicial | `status_logistico` inicial |
|---|---|---|---|
| Pix antecipado | pix_manual | pendente | aguardando_pagamento |
| Débito na entrega | debito_entrega | a_receber_na_entrega | em_separacao |
| Crédito na entrega | credito_entrega | a_receber_na_entrega | em_separacao |

**Não é um gateway Pix**: o pedido é registrado e o cliente vê, no acompanhamento, um link `wa.me` com mensagem preenchida (código do pedido e valor calculado no servidor). O envio da mensagem depende de interação e confirmação do cliente no WhatsApp; nenhum texto é transmitido automaticamente em segundo plano.

Mensagem padrão: "Olá! Fiz uma compra na VittaPet Store, pedido [CÓDIGO], no valor de [TOTAL]. Quero pagar antecipadamente por Pix. Pode me enviar a chave Pix?"

O número comercial é cadastrado em **Admin > Configurações > WhatsApp**, formato internacional somente dígitos, `55 + DDD + número`. Não inventar número ou chave. Sem configuração, a tela informa que é necessário contatar o estabelecimento.

## Pix antecipado
1. Cliente fecha pedido -> `pendente/aguardando_pagamento`; carrinho e estoque são processados na mesma transação.
2. Tela de rastreio exibe botão "Solicitar chave Pix pelo WhatsApp".
3. Cliente inicia conversa; lojista envia a chave e aguarda recebimento real.
4. Admin confere o comprovante **e o crédito recebido no meio de pagamento** (comprovantes podem ser falsificados), clica em confirmar pagamento.
5. Transação do servidor marca `confirmado` e move automaticamente para `em_separacao`, com histórico.
6. O lojista avança para `embalado -> saiu_para_entrega -> entregue`.

## Débito/crédito na entrega
1. Cliente seleciona débito ou crédito e registra pedido.
2. O pedido inicia em `a_receber_na_entrega/em_separacao`. **Não exibe "Aguardando Pix" nem bloqueia preparação.**
3. Avança em `embalado -> saiu_para_entrega -> entregue`.
4. Depois que a cobrança é realmente realizada na entrega, o admin pode confirmar o pagamento e contabilizar a receita. A interface bloqueia confirmação antecipada desses pedidos.
5. Pedido não pago não é contabilizado como receita no dashboard.

## Operação e riscos
- O sistema não verifica pagamentos nem envia mensagens WhatsApp automaticamente; esse fluxo usa o aplicativo do cliente.
- A confirmação financeira é manual, sob responsabilidade do administrador, protegida por CSRF e autenticação.
- A confirmação do Pix atualiza o status logístico no mesmo banco e histórico, sem intervenção manual adicional.
- Pedidos pagos continuam bloqueados contra cancelamento simples: estorno e ajuste de estoque exigem funcionalidade específica futura.
- Frete em R$ 0,00 é demonstrativo e precisa ser configurado antes de vender de verdade.
- Migration incremental `005_pagamentos_whatsapp.sql` é obrigatória; as migrations 001–004 continuam necessárias. A coluna `cartao_entrega` é preservada como legado de pedidos anteriores.
- Não criar tag 1.0 nem publicar como versão comercial antes de testes integrados.

# QA revisão 04 — Pix/WhatsApp e cartão na entrega

**Escopo:** fluxo manual de pagamento solicitado para VittaPet Store v1.0.
**Método:** inspeção estática de código, verificação de estados e compatibilidade de migration. **Não houve teste de runtime PHP/MySQL, navegador, WhatsApp nem deploy InfinityFree nesta etapa.**

## Cenários obrigatórios
- [ ] Aplicar migration 005 após 001–004 em banco de teste, sem perder pedidos antigos.
- [ ] Configurar WhatsApp com DDI 55 e DDD; rejeitar números inválidos.
- [ ] Pedido Pix inicia `pendente/aguardando_pagamento`.
- [ ] Rastreio Pix exibe texto com token e total correto e abre conversa do lojista, sem enviar mensagem automaticamente.
- [ ] Sem WhatsApp configurado: fallback claro, sem URL inválida.
- [ ] Administrador confirma Pix apenas após verificar crédito; pedido passa a `confirmado/em_separacao` com histórico.
- [ ] Pedido débito/crédito inicia `a_receber_na_entrega/em_separacao`; não exibe aviso de Pix.
- [ ] Status de embalagem e saída para entrega disponíveis para pedido com cartão ainda não pago.
- [ ] Botão de confirmar cartão indisponível antes de `entregue`; confirmar valor recebido só depois da cobrança.
- [ ] Relatório de receita NÃO inclui cartão a receber nem Pix não confirmado.
- [ ] Estado cancelado, comportamento de estoque, transações e controle de duplicidade testados.
- [ ] Testar HTML/PHP em hosting InfinityFree HTTPS e navegação mobile.

## Parecer
A separação financeira e logística foi implementada em código, com mensagem WhatsApp preenchida por link e processamento manual do pagamento. **Não aprovado para produção ou release** até teste real, proteção contra abuso, política de frete e processo de estorno completo.

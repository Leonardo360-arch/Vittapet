# QA revisão 06 — integridade financeira (17/11/2026)

**Versão:** 1.0.0-dev.3. **Parecer inicial:** implementação em branch; nenhum selo emitido até execução real do CI.

## Casos automatizados acrescentados
1. Confirmar Pix registra `pago_em`.
2. Registrar estorno manual comprovado antes da expedição cancela pedido, marca `estornado_em` e restaura estoque.
3. Segunda tentativa de estorno é bloqueada e não duplica movimento.
4. Cartão na entrega registra `pago_em` ao confirmar.
5. Pedido entregue não pode receber estorno simplificado.
6. Dashboard filtra receita por recebimento e não contabiliza pedido estornado.
7. Todos os testes existentes do checkout, Pix WhatsApp, cobertura CEP, frete, login, estoque e CSRF permanecem.

## Riscos abertos
- Falta um *livro-razão* financeiro imutável para entradas e saídas históricas; o dashboard representa saldos atuais.
- O estorno apenas documenta devolução externa; não há processamento bancário no sistema.
- Não foi implementada devolução física de mercadoria já enviada.
- Contabilidade fiscal, impostos e tarifas não são calculados.
- Concorrência real (último item em duas sessões) requer teste especializado.
- Interfaces e integração externa ViaCEP, Nominatim e WhatsApp precisam de validação manual.
- InfinityFree somente no final.

**Conclusão:** executar CI e registrar log antes do merge; checar regras de estorno com responsável do comércio antes de uso real.

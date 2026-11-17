# Frete v1.0 — configuração de taxa única e segurança do checkout

**Data:** 17/11/2026 | **Status:** implementação e testes automatizados sujeitos a aprovação.

## Motivação
O checkout anterior gravava R$ 0,00 como frete sem que o lojista tivesse definido entrega grátis. Isso poderia produzir cobrança incorreta e invalidar os relatórios.

## Regra implementada
- A migration `007_frete_fixo.sql` adiciona `configuracoes_loja.frete_fixo DECIMAL(10,2) NULL`.
- `NULL` significa **não configurado**. O botão de finalizar fica desabilitado e um POST direto de checkout é rejeitado pelo servidor.
- Valor `0.00` representa **frete grátis escolhido conscientemente** pelo administrador.
- Valor positivo representa **taxa fixa por pedido**. Exemplo: subtotal R$ 49,90 + frete R$ 8,50 = total R$ 58,40.
- O administrador define ou remove a taxa em `/admin/configuracoes.php`, com CSRF, validação e persistência PDO.
- O checkout consulta e bloqueia a linha da configuração dentro da transação de pedido, evitando alteração simultânea do frete durante fechamento. O navegador não controla o valor de entrega.
- O valor definitivo é gravado em `pedidos.frete`, e `pedidos.total` inclui a taxa; a mensagem WhatsApp Pix usa o total persistido.

## Limitações e segurança
A taxa única ainda **não implementa áreas de cobertura, distância, taxas por bairro ou peso**. Portanto, o sistema não deve aceitar pedidos comerciais fora da região efetivamente atendida pela loja. É necessário definir e validar a área de entrega (CEP, bairros/cidade e regras de exceção) antes da liberação comercial. Não confundir essa configuração acadêmica com serviço de cotação de transportadora.

O valor no resumo do carrinho ainda é apenas subtotal; o frete completo aparece no checkout quando configurado. O backend não confia no valor informado pelo cliente.

## Testes adicionais exigidos
- [ ] Sem frete cadastrado, GET checkout informa indisponibilidade e POST não cria pedido.
- [ ] Com frete R$ 8,50, um item de R$ 49,90 registra subtotal R$ 49,90 e total R$ 58,40.
- [ ] Pedido Pix usa esse total ao abrir a mensagem pré-preenchida.
- [ ] Pedido no cartão usa o mesmo frete sem reconhecer pagamento antecipado.
- [ ] Valor inválido, negativo ou acima da precisão permitida não é salvo.
- [ ] Configuração com zero explícito permite frete grátis sem confundir com ausência.
- [ ] Mudança de frete não modifica valores históricos de pedidos já concluídos.
- [ ] Testar simultaneidade de salvamento de frete e checkout.

**Hospedagem InfinityFree:** permanece última etapa, depois de aprovação de qualidade.

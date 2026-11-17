# Manual operacional — VittaPet Store v1.0 (desenvolvimento)

**Versão base:** 1.0.0-dev.5; documentação revisada para o ciclo de homologação seguinte. **Não usar com clientes reais sem liberação.**

## Acesso e preparação
A loja é acessada pela página `/index.php`; o administrador usa `/login.php`. A conta não vem com e-mail ou senha padrão: executar `scripts/create_admin.php` por CLI no ambiente privado, com `ADMIN_EMAIL`, `ADMIN_NAME` e `ADMIN_PASSWORD` (mínimo 12 caracteres). Nunca incluir a senha neste manual, em prints ou commits.

Na administração, em `/admin/configuracoes.php`, configurar nome, endereço real, coordenadas, WhatsApp comercial (DDI 55 + DDD + número), taxa fixa de frete e prefixos de CEP aceitos. O número do WhatsApp é **destinatário da mensagem Pix**. Se frete ou cobertura estiverem vazios, o checkout não poderá concluir compras.

## Operação do catálogo
Em `/admin/produtos.php`, criar produtos com categoria válida, preços, custo, estoque e descrição. Revisar preços/quantidades antes de liberar a vitrine. A exclusão é lógica; itens históricos não devem ser apagados fisicamente.

## Compra pelo cliente
1. Acessar catálogo, filtrar categoria e adicionar produtos.
2. Abrir carrinho e ajustar quantidade; a tela recalcula subtotal.
3. Informar dados de contato, CEP e endereço. ViaCEP tenta preencher os campos, que podem ser editados manualmente.
4. O servidor confere disponibilidade, prefixo de CEP autorizado, frete, total e evita reenvio do mesmo checkout.
5. Selecionar **Pix antecipado** ou **débito/crédito na entrega**; receber o token para rastreamento.

### Pix via WhatsApp
Após registrar um pedido Pix, abrir `/rastrear.php?token=...` e clicar no link para o número comercial configurado, com mensagem preenchida contendo token e total. **O cliente precisa enviar a mensagem**; não há envio automático. O lojista informa sua chave por WhatsApp e confere o recebimento efetivo antes de confirmar o pagamento no painel. O pedido então avança para `em_separacao`.

### Débito/crédito na entrega
O pedido inicia em `em_separacao` com pagamento `a_receber_na_entrega`. O lojista pode embalar e despachar, mas deve confirmar o pagamento **somente depois da cobrança real na entrega**.

## Logística e cancelamentos
Administração `/admin/pedidos.php`: `em_separacao → embalado → saiu_para_entrega → entregue`. Pedido não pago só pode ser cancelado conforme regras existentes; ao cancelar, verificar a reposição do estoque e o histórico.

Para pagamento já confirmado, o botão **Registrar estorno já realizado** apenas anota a restituição previamente efetivada fora do sistema, com justificativa; disponível antes do despacho. **Não transfere dinheiro**. Pedidos enviados/entregues exigem processo separado de devolução física.

## Despesas e dashboard
Registrar despesas em `/admin/financeiro.php`. Dashboard apresenta estimativas de receita de pedidos que permanecem confirmados, custos dos itens, despesas e resultado. Filtros de recebimento usam `pago_em`; pedidos legados sem data não são atribuídos a datas fictícias. Não equivale a razão contábil, extrato bancário ou relatório fiscal.

## Localização e APIs
Na configuração do endereço, clicar em **Localizar endereço no mapa** consulta Nominatim sob demanda. Conferir as coordenadas e salvar. A home renderiza Leaflet com endereço e links de rota; se mapa indisponível, exibe alternativa textual. A API de CEP é o ViaCEP, consultado pelo navegador.

## Segurança e suporte
Não compartilhar senha, token de rastreio completo nem informações pessoais em capturas. Logout pelo botão do painel. Caso a loja apresente erro de configuração, verificar ordem das migrations, banco e logs privados. No InfinityFree, validar proteção das pastas antes de divulgar a URL pública.

## Testes antes de liberar
Executar [Checklist de entrega](CHECKLIST_ENTREGA_V1.md) e [Matriz de QA](MATRIZ_TESTES_V1.md). O último portão é a implantação no InfinityFree, sem antecipação.

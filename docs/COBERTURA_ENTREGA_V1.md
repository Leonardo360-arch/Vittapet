# Cobertura de entrega — 1.0.0-dev.2

**Objetivo:** impedir que o checkout registre pedidos para CEPs fora da cobertura autorizada pelo lojista, sem depender da disponibilidade de APIs externas no servidor InfinityFree.

## Implementação

- Migration 008 acrescenta `configuracoes_loja.cep_prefixos_entrega` (VARCHAR(500), NULL por padrão).
- Admin cadastra prefixos com **5 dígitos**, separados por vírgula, ponto e vírgula ou espaço, até 50 entradas. Normalização remove duplicatas e ordena.
- Se não existir frete fixo **ou** não houver prefixos, o checkout é desabilitado no navegador.
- No POST, o servidor valida novamente, dentro da transação que bloqueia a linha de configuração, se os cinco primeiros dígitos do CEP do comprador pertencem à lista permitida.
- CEP fora da cobertura retorna erro de negócio; não cria pedido, não baixa estoque, não gera token e não abre WhatsApp.
- CEP permitido usa o frete configurado na migration 007, sem alterar pedidos existentes.

### Exemplo **fictício** (não autoriza regiões reais)
Com `01001`, um CEP no formato `01001-000` atende ao filtro e `13010-000` é rejeitado. O código não assume qualquer prefixo válido sem cadastro administrativo.

## Limites da regra

- Cada prefixo de 5 dígitos autoriza aproximadamente 100 CEPs: não verifica se o logradouro realmente existe, se o bairro é acessível ou se há restrições pontuais.
- O formulário ViaCEP consulta endereço para facilitar preenchimento, mas o POST **não obtém prova independente do CEP**; comprador pode editar manualmente a rua. Para operação real, recomenda-se validar CEP e endereço contra fonte confiável e manter conferência operacional quando necessário.
- A taxa é fixa por pedido, não calcula quilometragem, peso, combustível, faixa de horário ou múltiplas zonas de preço.
- O lojista deve cadastrar **somente os prefixos que realmente atende**, confirmar a correspondência com seus bairros/rotas e testar os casos-limite antes de liberar pedidos comerciais.
- Zero prefixos = pedidos indisponíveis; zero de frete = grátis explicitamente, desde que haja cobertura.

## Critérios de aceite

- [ ] Migration 008 executa após 007 no MySQL 8.
- [ ] Configuração administrativa aceita prefixos válidos, normaliza e rejeita formato inválido.
- [ ] Checkout em GET bloqueia quando não há prefixos cadastrados.
- [ ] Checkout POST rejeita CEP fora da lista mesmo que usuário envie requisição manual.
- [ ] Checkout não grava cliente, pedido, itens nem movimentação de estoque no erro de cobertura.
- [ ] Pix e cartão autorizados preservam totais e fluxos anteriores.
- [ ] Testes de segurança, mobile, ViaCEP e restrições de área avaliados antes da homologação.
- [ ] Interface e documentação não afirmam validação automática de existência de endereço.

**Estado:** código desenvolvido para revisão e CI; não emitir selo antes da evidência da execução.

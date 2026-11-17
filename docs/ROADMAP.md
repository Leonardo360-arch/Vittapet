# Roadmap de desenvolvimento — VittaPet

## Diretriz e governança
**Produto v1.0 = VittaPet Store (somente pet shop comercial). Produto v2.0 = integração gradual de serviços e clínica.** Os especialistas de engenharia e consultores virtuais do nicho pet colaboram como papéis de análise e recomendação; não substituem validações profissionais nem representam pessoas contratadas.

## Marco acadêmico: 23/11/2026
1. **Fundação:** layout e design tokens, infraestrutura PHP/MySQL/PDO, migrations, autenticação administrativa e controle de acesso.
2. **Catálogo:** CRUD de produtos/categorias, listagem pública, estoque e imagens.
3. **Loja:** carrinho persistente em sessão, checkout, pedidos com itens e valores congelados, reserva/baixa de estoque consistente.
4. **Rastreio:** token forte e histórico dos estados aguardando pagamento, em separação, embalado, saiu para entrega, entregue, cancelado.
5. **Administração:** dashboard de pedidos/produtos, alteração de status e relatórios de receita confirmada, custo e despesas; indicar métricas estimadas.
6. **Integrações:** ViaCEP e serviço de geocodificação de endereço (JSON), com mapa Leaflet/OpenStreetMap, com falhas tratadas.
7. **Qualidade e entrega:** testes CRUD e compra ponta a ponta, revisão de segurança, deploy InfinityFree, link público e documentação para Teams.

**Prioridade de avaliação:** primeiro comprovar CRUD, 2 APIs, originalidade, hospedagem e documentação. Carrinho, pedidos e financeiro aumentam o escopo e só podem ser considerados concluídos após testes.

## Domínios e dados da v1.0
- Identidade: usuarios, clientes (ou cadastro simplificado com compatibilidade futura)
- Catálogo: categorias, produtos, movimentacoes_estoque
- Comercial: pedidos, pedido_itens, pedido_historico
- Financeiro: transacoes_financeiras, custos/CMV, despesas
- Configuração: configuracoes_loja

Não usar valores monetários em float; DECIMAL(10,2). Pedido, itens e estoque exigem transação. Separar estado do pagamento do estado logístico. Um pedido cancelado deve preservar trilha histórica e refletir estorno/reversão quando aplicável.

## Identidade visual inicial
- Primária: #0F766E (verde)
- Secundária: #2DD4BF (turquesa)
- Destaque: #F97316 (laranja)
- Fundo: #FFF7ED
- Texto: #1F2937
- Tipografia: Poppins 600/700 para títulos; Inter 400/500 para corpo e formulários.
- Tokens em `public/assets/css/tokens.css`, estilos globais em `base.css`, `layout.css`, `components.css`, páginas em `pages/` e dashboard em `admin/`.
- Testar contraste WCAG e versão mobile.

## V2.0 — Integração de serviços
1. Tutores e pets com histórico e permissões
2. Agenda, banho/tosa, duração de serviços e prevenção de conflitos
3. Consultas, prontuários, prescrições, exames e vacinação
4. Portal do Tutor e documentos com verificação
5. VittaBot e atendimento humano escalável
6. E-mails, fidelidade, mural e concursos
7. Segurança clínica, privacidade, auditoria, testes e operação

**Referência de requisitos futuros:** documento original 'VittaPet — Clínica Veterinária, Pet Shop & Portal do Paciente', versão 2.1. A implementação futura deverá confrontar todos os requisitos originais antes de declarar o ecossistema completo.

## Critério de conclusão
Nunca registrar etapa como concluída sem evidência de código, testes, integração e documentação; registrar pendências e falhas de deploy com transparência.

## Requisito UX obrigatório — carrinho reativo e controles slim
- Ao adicionar/remover produto, ou editar sua quantidade, recalcular imediatamente subtotal por item, subtotal geral, descontos, frete e total sem botão "Atualizar carrinho" e sem recarregar a página.
- Utilizar interface minimalista, controles compactos com ícones vetoriais SVG/Lucide (sem símbolos improvisados), espaçamento consistente, responsividade e estados de carregamento/erro acessíveis.
- A interação ocorre no JavaScript, mas o servidor PHP recalcula preços, descontos, impostos/frete aplicáveis e estoque com valores confiáveis do banco antes de fechar o pedido; jamais confiar no total enviado pelo cliente.
- Evitar condições de corrida em cliques rápidos: bloquear/desfazer estados pendentes e sincronizar com resposta do servidor. Não permitir quantidades negativas nem maiores que estoque.
- Botão de remover item explícito e confirmação apenas quando necessária; totais sempre derivados da lista atual de itens.
- Testes: adicionar, remover último item, aumentar/diminuir quantidade, atingir limite do estoque, calcular frete/desconto, falha de API e recálculo no checkout.

## Localização da loja (revisão da integração de APIs)
- **API 1: ViaCEP** para consulta de CEP e autopreenchimento do endereço do comprador no checkout, com tratamento de CEP inválido, indisponibilidade e preenchimento manual.
- **API 2: geocodificação de endereço** para obter latitude/longitude da loja em resposta JSON, preferencialmente na configuração administrativa, não a cada visita da homepage. Provedor inicialmente avaliado: Nominatim/OpenStreetMap, respeitando limite de uso, identificação da aplicação, cache e proibição de autocomplete no endpoint público.
- **Mapa:** Leaflet.js com tiles compatíveis com OpenStreetMap e atribuição obrigatória. Leaflet é biblioteca de mapa, não endpoint JSON; comprovar explicitamente o consumo da API de geocodificação na avaliação de DW2.
- **Rodapé/homepage:** mapa compacto responsivo (altura aproximada de 240–320 px), marcador da loja, nome, endereço completo e botão de rotas (Google Maps/Waze). O administrador poderá editar endereço, latitude e longitude na configuração da loja; nenhum endereço fictício será exibido como localização real.
- O mapa utiliza coordenadas persistidas em `configuracoes_loja` ou configuração equivalente, evitando geocodificação em toda abertura de página. Em caso de falha do mapa, mostrar endereço textual e link para rotas; evitar coleta da localização do visitante sem necessidade e consentimento do navegador.
- Critérios de aceite: CEP retorna JSON e preenche campos; a geocodificação retorna coordenadas verificáveis em JSON e elas são usadas para o marcador; visualização mobile, atribuição de mapa, links de rota e fallback testados.

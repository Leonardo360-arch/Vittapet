# Roadmap de desenvolvimento — VittaPet

## Diretriz e governança
**Produto v1.0 = VittaPet Store (somente pet shop comercial). Produto v2.0 = integração gradual de serviços e clínica.** Os especialistas de engenharia e consultores virtuais do nicho pet colaboram como papéis de análise e recomendação; não substituem validações profissionais nem representam pessoas contratadas.

## Marco acadêmico: 23/11/2026
1. **Fundação:** layout e design tokens, infraestrutura PHP/MySQL/PDO, migrations, autenticação administrativa e controle de acesso.
2. **Catálogo:** CRUD de produtos/categorias, listagem pública, estoque e imagens.
3. **Loja:** carrinho persistente em sessão, checkout, pedidos com itens e valores congelados, reserva/baixa de estoque consistente.
4. **Rastreio:** token forte e histórico dos estados aguardando pagamento, em separação, embalado, saiu para entrega, entregue, cancelado.
5. **Administração:** dashboard de pedidos/produtos, alteração de status e relatórios de receita confirmada, custo e despesas; indicar métricas estimadas.
6. **Integrações:** ViaCEP e Dog CEO (JSON), com falhas tratadas.
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

# Histórico de versões — VittaPet

Este histórico identifica versões **planejadas/documentadas** e versões **lançadas**. Não significa que o sistema já está implementado.

## [1.0.0] — Em desenvolvimento (17/11/2026)
- Adicionado em branch de recurso: migration 003 de despesas, dashboard de indicadores, registro financeiro e orientação/roteamento para InfinityFree gratuito; revisão de execução/hospedagem pendente.
**Produto:** VittaPet Store, sem serviços.
- Escopo definido: catálogo, CRUD MySQL, carrinho reativo, checkout, pedidos, embalagem e entrega, administração, estoque, financeiro e dashboard.
- Definidas duas APIs da primeira versão: ViaCEP e geocodificação de endereço da loja com mapa.
- Identidade proposta: verde/turquesa/laranja, Poppins e Inter, controles slim.
- Documentação funcional, técnica, visual e critérios de aceite organizados.
- **Segunda etapa iniciada:** migration 002 com clientes, pedidos, itens, histórico e movimentações de estoque; checkout com transação e ViaCEP; rastreio público por token; painel de status e pagamento manual. Revisão estática registrada em `docs/QA_REVISAO_01.md`, com bloqueadores de release.
- **Implementação inicial adicionada à develop:** schema MySQL de usuários/categorias/produtos, PDO, login administrativo, CRUD, catálogo e carrinho de sessão com atualização por Fetch. **Não testados em execução nem implantados.** Checkout, histórico de pedidos, financeiro, duas APIs e mapa ainda pendentes. Não existe release 1.0.0 final validada.

## [2.0.0] — Planejamento futuro
- Integração de banho/tosa, clínica, pets/tutores, Portal do Tutor, VittaBot, mural, fidelidade, concursos e APIs adicionais.
- Base de requisitos: documentação original VittaPet v2.1; a nomenclatura 2.0.0 aqui se refere à versão futura do produto integrado, não à versão do documento original.

## Convenção para futuras atualizações
SemVer: `MAJOR.MINOR.PATCH`. Atualizar CHANGELOG e documentação em cada mudança de escopo; criar tag/release oficial somente quando código, testes e implantação da versão estiverem validados.

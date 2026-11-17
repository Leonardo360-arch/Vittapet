# Histórico de versões — VittaPet

Este histórico identifica versões **planejadas/documentadas** e versões **lançadas**. Não significa que o sistema já está implementado.

## [1.0.0] — Em desenvolvimento (17/11/2026)
- Revisão de segurança em branch: migration 006 de limitação de login e rastreio, expiração e reverificação de sessão administrativa, logout com POST/CSRF, CI PHP lint e QA revisão 05. Integração/execução de testes ainda pendentes.
- Fluxo Pix antecipado via link de WhatsApp com mensagem preenchida e confirmação manual, e débito/crédito na entrega, sem status logístico de espera por pagamento. Migration 005, configuração do número, checkout, rastreio e painel adaptados. Revisão estática QA 04; testes pendentes.
- Etapa de localização (em branch, pendente de validação em navegador): migration 004 de configurações, painel administrativo com consulta pontual à API JSON de geocodificação, persistência das coordenadas, mapa Leaflet na homepage e links de rota; nenhum endereço fictício publicado.
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

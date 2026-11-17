# Histórico de versões — VittaPet

Este histórico identifica versões **planejadas/documentadas** e versões **lançadas**. Não significa que o sistema já está implementado.

## [1.0.0-dev.4] — Revisão de concorrência e código (17/11/2026)
- Extraído serviço de estoque/itens do checkout, preservando locks transacionais e prepared statements.
- CI adiciona teste de dois processos/duas conexões disputando a última unidade, com verificação de 1 venda, 1 rejeição e estoque não negativo.
- Script de deploy cria pacote por lista de permissões, excluindo testes, documentação, migrations e segredos; .gitignore ampliado.
- Auditoria Git confirma ausência de arquivos evidentemente temporários no histórico atual. Não houve exclusão destrutiva de arquivos úteis.
- QA e hospedagem permanecem separados; não publicar como estável antes do aceite final.

## [1.0.0-dev.3] — Em homologação (17/11/2026)
- Registro de estorno manual de pagamento já reembolsado externamente, com justificativa, cancelamento, estoque e histórico transacionais.
- Data de recebimento `pago_em`, data de registro `estornado_em` e aviso de pedidos legados, migration 009.
- Dashboard filtra datas de confirmação e exclui pedidos estornados do saldo atual; não é razão contábil.
- Testes incrementais e revisão QA 06; InfinityFree continua última etapa.

## [1.0.0-dev.2] — Em desenvolvimento (17/11/2026)
- Migration 008, cadastro de prefixos de CEP atendidos e verificação transacional de cobertura no checkout.
- Extração da validação para `includes/delivery_coverage.php` e testes de CEP permitido, negado e ausência de configuração.
- Frete continua taxa fixa; CEP e endereço ainda exigem homologação real.

## [1.0.0-dev.1] — Em homologação (17/11/2026)
- Identificador formal de desenvolvimento adicionado em `VERSION`. Novas correções integradas terão `dev.N` incrementado, com changelog, QA e documentação sincronizados. Não representa publicação estável.
- Frete fixo configurável em migration 007, administração e checkout transacional; ausência de taxa bloqueia pedidos e zero exige escolha explícita. Testes adicionais e documentação em `docs/FRETE_V1.md`. Área de entrega e estorno permanecem riscos.
- Gate de integração em PHP 8.2/MySQL 8: 55 asserções HTTP/SQL passaram no GitHub Actions (run 44097037063). Documentação técnica, matriz 42 cenários, padrão de código, homologação formal e plano futuro com datas. Ainda faltam casos manuais, frete real, estorno, acesso no InfinityFree e aprovação final.
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

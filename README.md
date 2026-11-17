# VittaPet Store

**Projeto:** loja virtual de produtos pet em PHP 8, MySQL, HTML5, CSS3, Bootstrap 5 e JavaScript. O código será preparado para evoluir, no futuro, para o VittaPet Ecosystem.

## Versões

| Versão | Escopo | Estado |
|---|---|---|
| **v1.0.0 — Store** | Catálogo, CRUD, carrinho reativo, checkout, pedidos/entregas, estoque, financeiro, dashboard, ViaCEP e mapa | **Documentação definida; implementação pendente** |
| **v2.0.0 — Ecosystem** | Serviços, clínica, tutores/pets, Portal do Tutor, VittaBot, mural, pontos e outras APIs | **Planejada; não implementada** |

> A versão 1.0 é **exclusivamente uma loja de produtos pet**. Não inclui veterinário, banho e tosa, serviços, agendamento, galeria de cães ou mural social.

## Entrega acadêmica — Desenvolvimento Web II

**Prazo: 23/11/2026.** Critérios de avaliação do exercício:
- [ ] CRUD funcional com PHP e MySQL — 2,5 pontos
- [ ] Originalidade e interface própria — 2,0 pontos
- [ ] Documentação técnica — 2,0 pontos
- [ ] Hospedagem e link público no InfinityFree — 2,0 pontos
- [ ] Consumo de **duas APIs JSON** — 1,5 ponto: **ViaCEP** no checkout e **geocodificação de endereço** para localizar a loja no mapa.

## Fluxo da loja
Catálogo → carrinho de atualização automática → checkout → pedido → aguardando pagamento → em separação → embalado → saiu para entrega → entregue. Cancelamentos e estados financeiros são tratados separadamente.

O dashboard apresentará dados do MySQL, com receita efetivamente confirmada, despesas, resultado estimado, pedidos e estoque. O valor final de compras será recalculado pelo servidor, não confiado ao navegador.

## Documentação oficial
- [Especificação completa da v1.0](docs/VERSAO_1_0.md)
- [Documentação técnica detalhada da arquitetura](docs/ARQUITETURA_TECNICA_V1.md)
- [Matriz de 42 cenários de QA](docs/MATRIZ_TESTES_V1.md)
- [Critérios formais de homologação](docs/HOMOLOGACAO_V1.md)
- [Evidência QA: 55 verificações HTTP/MySQL](docs/RELATORIO_QA_EXECUCAO_01.md)
- [Padrão de código limpo e SOLID](docs/PADRAO_CODIGO_V1.md)
- [Plano pós-entrega com datas](docs/PLANO_POS_ENTREGA.md)
- [Identidade visual, UX e CSS](docs/DESIGN_SYSTEM.md)
- [Roadmap e etapas de desenvolvimento](docs/ROADMAP.md)
- [Backlog da versão 2.0](docs/VERSAO_2_0_BACKLOG.md)
- [Histórico e controle de versões](CHANGELOG.md)
- [QA — Revisão técnica 01](docs/QA_REVISAO_01.md)
- [QA — Segurança e preparação de homologação](docs/QA_REVISAO_05.md)
- [Instalação local](docs/INSTALACAO.md)

## Arquitetura planejada
Monólito modular PHP com PDO/MySQL e camadas de controllers, services e repositories; migrations versionadas, testes, configuração privada fora do webroot e apenas `public/` acessível pelo servidor. A configuração de hospedagem InfinityFree deve ser testada sem expor segredos.

## Testes automatizados
GitHub Actions executa lint PHP e testes HTTP contra MySQL 8 isolado nos PRs para main. Os testes manuais de APIs, celular, concorrência e InfinityFree continuam obrigatórios e são registrados na matriz de QA.

## Controle de progresso
**Situação em 17/11/2026:** fundação, CRUD, carrinho, checkout inicial, pedidos e rastreio **enviados para a branch `develop`**. **Ainda não foram validados em execução com MySQL e não há publicação concluída.** Consulte a revisão de QA para bloqueadores. A branch `main` será atualizada mediante revisão e aprovação da entrega, sem confundir documentação aprovada com release funcional.

## Desenvolvimento e colaboração
Evitar segredos no Git, escrever commits explicativos, revisar PRs antes de integrar na `main`, e atualizar [CHANGELOG.md](CHANGELOG.md) a cada mudança significativa. Novos recursos devem preservar a compatibilidade com os módulos já implementados.

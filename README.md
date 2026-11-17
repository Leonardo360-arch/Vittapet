# VittaPet Store

**Versão atual em homologação:** `1.0.0-dev.5` (consulte [`VERSION`](VERSION)). Sem release final publicada.

**Projeto:** loja virtual de produtos pet em PHP 8, MySQL, HTML5, CSS3, Bootstrap 5 e JavaScript. O código será preparado para evoluir, no futuro, para o VittaPet Ecosystem.

## Versões

| Versão | Escopo | Estado |
|---|---|---|
| **v1.0.0 — Store** | Catálogo, CRUD, carrinho reativo, checkout, pedidos/entregas, estoque, financeiro, dashboard, ViaCEP e mapa | **Implementação em homologação; CI automatizado aprovado, testes manuais pendentes** |
| **v2.0.0 — Ecosystem** | Serviços, clínica, tutores/pets, Portal do Tutor, VittaBot, mural, pontos e outras APIs | **Planejada; não implementada** |

> A versão 1.0 é **exclusivamente uma loja de produtos pet**. Não inclui veterinário, banho e tosa, serviços, agendamento, galeria de cães ou mural social.

## Testar no localhost (Windows)
A VittaPet pode rodar em `http://localhost:8080/` com Docker Desktop, sem instalar PHP ou MySQL no Windows. Use `powershell -NoProfile -ExecutionPolicy Bypass -File .\\scripts\\setup_local.ps1` na raiz do projeto. O phpMyAdmin fica em `http://localhost:8081/`. Veja [Guia de localhost](docs/LOCALHOST_WINDOWS.md) para instalação, banco, cadastro do administrador, diagnóstico, configuração comercial e opção sem Docker. **A configuração local não é enviada ao InfinityFree.**

## Entrega acadêmica — Desenvolvimento Web II

**Prazo: 23/11/2026.** Critérios de avaliação do exercício:
- [ ] CRUD funcional com PHP e MySQL — 2,5 pontos
- [ ] Originalidade e interface própria — 2,0 pontos
- [ ] Documentação técnica — 2,0 pontos
- [ ] Hospedagem e link público no InfinityFree — 2,0 pontos
- [ ] Consumo de **duas APIs JSON** — 1,5 ponto: **ViaCEP** no checkout e **geocodificação de endereço** para localizar a loja no mapa.

## Fluxo da loja
Catálogo → carrinho de atualização automática → checkout → pedido → aguardando pagamento → em separação → embalado → saiu para entrega → entregue. Cancelamentos e estados financeiros são tratados separadamente.

O dashboard apresenta dados do MySQL, com receita efetivamente confirmada, despesas, resultado estimado, pedidos e estoque. O valor final de compras será recalculado pelo servidor, não confiado ao navegador.

## Documentação oficial
- [Especificação completa da v1.0](docs/VERSAO_1_0.md)
- [Regra de frete fixa e testes](docs/FRETE_V1.md)
- [Cobertura por prefixos de CEP](docs/COBERTURA_ENTREGA_V1.md)
- [Estornos manuais e datas de pagamento](docs/ESTORNOS_FINANCEIRO_V1.md)
- [QA revisão 06 de integridade financeira](docs/QA_REVISAO_06.md)
- [Documentação técnica detalhada da arquitetura](docs/ARQUITETURA_TECNICA_V1.md)
- [Matriz de 42 cenários de QA](docs/MATRIZ_TESTES_V1.md)
- [Critérios formais de homologação](docs/HOMOLOGACAO_V1.md)
- [Evidência QA: 55 verificações HTTP/MySQL](docs/RELATORIO_QA_EXECUCAO_01.md)
- [Padrão de código limpo e SOLID](docs/PADRAO_CODIGO_V1.md)
- [Revisão de concorrência e organização Git](docs/QA_REVISAO_07.md)
- [QA 08 — Navegador e integrações JSON](docs/QA_REVISAO_08.md)
- [QA 09 — Auditoria visual e dashboard](docs/QA_REVISAO_09.md)
- [QA 10 — Vitrine e produtos de teste](docs/QA_REVISAO_10.md)
- [Manual operacional v1](docs/MANUAL_OPERACIONAL_V1.md)
- [Checklist final de entrega](docs/CHECKLIST_ENTREGA_V1.md)
- [Parecer de liberação](docs/RELATORIO_LIBERACAO_V1.md)
- [Inventário de arquivos e empacotamento](docs/AUDITORIA_GIT_V1.md)
- [Plano pós-entrega com datas](docs/PLANO_POS_ENTREGA.md)
- [Identidade visual, UX e CSS](docs/DESIGN_SYSTEM.md)
- [Roadmap e etapas de desenvolvimento](docs/ROADMAP.md)
- [Backlog da versão 2.0](docs/VERSAO_2_0_BACKLOG.md)
- [Histórico e controle de versões](CHANGELOG.md)
- [Política de versionamento e critérios de atualização](docs/VERSIONAMENTO.md)
- [QA — Revisão técnica 01](docs/QA_REVISAO_01.md)
- [QA — Segurança e preparação de homologação](docs/QA_REVISAO_05.md)
- [Instalação local](docs/INSTALACAO.md)
- [Instalação rápida no Windows (Docker + localhost)](docs/LOCALHOST_WINDOWS.md)

## Arquitetura atual
Monólito PHP/PDO/MySQL com controladores PHP, algumas regras extraídas para `includes/` e views integradas. Ainda não há separação completa em camadas MVC. Existem nove migrations, testes automatizados e configuração privada. O pacote de deploy usa uma lista de arquivos permitidos; o InfinityFree ainda não foi implantado.

## Testes automatizados
GitHub Actions executa lint PHP, 78 asserções HTTP/MySQL, concorrência entre dois processos PHP e 15 testes Playwright em navegador Chromium emulado. Os serviços externos foram simulados nos testes de navegador; os testes reais de APIs, dispositivos físicos e InfinityFree permanecem obrigatórios.

## Controle de progresso
**Situação em 17/11/2026:** versão `1.0.0-dev.5` integrada à `main`; testes PHP/MySQL, concorrência e Chromium aprovados nas execuções de CI. APIs reais, WhatsApp em dispositivo físico, acessibilidade e implantação no InfinityFree ainda não homologados. Nenhuma tag estável publicada. Consulte `docs/CHECKLIST_ENTREGA_V1.md`.

## Desenvolvimento e colaboração
Evitar segredos no Git, escrever commits explicativos, revisar PRs antes de integrar na `main`, e atualizar [CHANGELOG.md](CHANGELOG.md) a cada mudança significativa. Novos recursos devem preservar a compatibilidade com os módulos já implementados.

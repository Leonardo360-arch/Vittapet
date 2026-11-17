# Homologação formal — VittaPet Store v1

**Ambiente atual:** código em GitHub, CI PHP/MySQL proposto; **InfinityFree não instalado**. Documento vivo: registrar resultados, não preencher com aprovações hipotéticas.

## Portões de qualidade
| Porta | Evidência obrigatória | Estado inicial |
|---|---|---|
| G1 — Sintaxe e estrutura | GitHub Actions PHP lint aprovado | **Aprovado no run 44097037063** |
| G2 — Banco e fluxo principal | CI MySQL 8/HTTP (CRUD, carrinho, checkout, pagamentos, estoque, sessão) aprovado | **55 asserts aprovados no run 44097037063** |
| G3 — Testes de borda/segurança | CSRF, login bloqueado, rastreio, concorrência, replay, injeção, auditoria | Pendente |
| G4 — Qualidade de código | Revisão de responsabilidades, formatação, nomes, duplicações, tratamento de falhas | Parcial; existem páginas monolíticas |
| G5 — Interface e APIs | Testes mobile/desktop, ViaCEP JSON, Nominatim JSON, mapa e WhatsApp | Pendente |
| G6 — Documentação técnica | Conferência de schema, requisitos, rastreio de testes, procedimentos, riscos e status real | Em elaboração |
| G7 — Hosting | **Somente após G1–G6:** upload em InfinityFree, importar SQL, HTTP/HTTPS, segredos e rotas | Última etapa — não iniciada |

## Evidência automatizada
[Relatório da execução QA 01](RELATORIO_QA_EXECUCAO_01.md). A aprovação G1/G2 não equivale à homologação geral.

## Selo de qualidade (metáfora interna)
**Status inicial: NÃO EMITIDO.** Emitir 'VittaPet Store 1.0 — QA Homologado' apenas quando G1–G7 forem aprovados, P0 = 0 pendentes/reprovados, evidências identificadas por commit e responsável, e revisão de segurança/documentação concluída. Não representa certificação ISO, auditoria externa ou homologação de pagamento.

## Configuração prévia de operação
- **E-mail de login:** definido no provisionamento por `ADMIN_EMAIL`; não usar credenciais fixas no repositório.
- **Senha:** por `ADMIN_PASSWORD` de forma segura, nunca em README ou relatório. Usar hash no DB.
- **Telefone comercial WhatsApp:** configurado no admin; DDI 55 + DDD + número. Testar com número autorizado em homologação.
- **Pix:** botão gera mensagem pré-preenchida; envio/recebimento/validação do pagamento continuam manuais.
- **Endereço:** cadastrado no admin e geocodificado na segunda API; não exibir localização fictícia.
- **Dados de teste:** banco temporário isolado; não misturar clientes reais.

## Bloqueadores conhecidos
- Frete demonstrativo 0,00 sem política de cobrança.
- Estorno de pedido pago não implementado.
- Relatórios financeiros usam data de criação, não de confirmação financeira.
- Testes de concorrência e replay não cobertos pela suíte inicial.
- Extensões, restrições, SSL e rewrites do InfinityFree precisam de teste final.
- Formatação/responsabilidades ainda exigem refatoração incremental; SOLID não deve ser declarado integralmente atendido sem evidência.

## Parecer
Não declarar sistema pronto para venda até esses bloqueadores serem resolvidos, mesmo que as funções básicas passem nos testes.

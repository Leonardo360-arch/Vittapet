# Plano de trabalho — entrega v1 e expansão v2
**Elaborado em:** 17/11/2026. **Data do exercício DW2:** segunda-feira, 23/11/2026.
As datas futuras são marcos de planejamento e **não significam automação, trabalho em segundo plano ou compromissos de entrega já executados**. A ordem pode ser revista após os resultados de QA.

## V1.0 — até 23/11/2026: loja de produtos
| Data planejada | Atividade | Aceite |
|---|---|---|
| 17/11 | Revisar requisitos, mapear riscos, criar CI MySQL/HTTP e documentação técnica | PR com testes executáveis, matriz e guia |
| 18/11 | Corrigir falhas identificadas na integração e formatar os arquivos mais críticos | CI verde, sem regressão nos P0 |
| 19/11 | Executar teste funcional manual no navegador, Pix/WhatsApp, débito/crédito na entrega e relatórios | Evidências dos fluxos e ajustes |
| 20/11 | Revisar segurança, integridade, UX mobile e validação das APIs JSON; relatório de defeitos | P0 aprovados ou falhas claramente bloqueadas |
| 21/11 | Congelar escopo da v1, preparar checklist de versão candidata e documentação final | Revisão da equipe e aprovação técnica |
| 22/11 | **Por último:** instalação/homologação no InfinityFree, proteção `htdocs`, conexões e SSL | URL funcional, verificações de acesso público/privado |
| 23/11 | Entrega no Teams de URL + documentação + evidências; registrar versão somente se homologada | Critérios de DW2 cumpridos, versão rastreável |

**Condição:** se um P0 permanecer aberto, não chamar a versão de estável e não ocultar falha no relatório. A hospedagem permanece última fase, mas o cronograma exige testes reais antes.

## Pós-entrega: somente a partir de 24/11/2026
| Janela proposta | Etapa | Resultado desejado |
|---|---|---|
| 24–27/11 | Revisão retrospectiva, dívida técnica, reestruturação MVC/SOLID de serviços e repositories | Regras extraídas com testes de regressão |
| 30/11–04/12 | Regras comerciais de frete, devolução/estorno e trilha financeira por data de confirmação | Melhor precisão operacional |
| 07–11/12 | Melhorias do catálogo, filtros, imagens, UX, acessibilidade e relatórios | Incremento Store 1.1 |
| 14–18/12 | Modelo tutores/pets, autorização e planejamento clínico | Base para v2, sem implantação clínica |
| 21/12/2026–08/01/2027 | Protótipos de serviços, agenda, banho/tosa e portal do tutor | Especificações + testes, sujeito a revisão |
| A partir de 11/01/2027 | Etapas graduais VittaPet Ecosystem 2.0: clínica, prontuário, atendimento e relacionamento | Fases separadas, exigências de segurança e conformidade |

**Dependências:** só iniciar domínios clínicos após decidir responsáveis, controle de dados sensíveis, validação profissional e impacto legal. Evitar misturar as regras de comércio já homologadas com funções futuras. Estimativas posteriores a 23/11 precisam ser reavaliadas com dados de esforço observados.

## Política de versionamento
- Até homologação: `1.0.0-dev`/candidato em documentação, sem tag oficial.
- Versão estável 1.0.0 somente depois de todos os critérios P0.
- Correções `1.0.x`; pequenas melhorias compatíveis `1.1.x`; integração de novos domínios `2.0.0`.
- Fluxo Git: `feat/*` ou `test/*` → lint/testes → revisão QA → PR → `main`. A `main` pode conter código incremental, mas isso não implica release.

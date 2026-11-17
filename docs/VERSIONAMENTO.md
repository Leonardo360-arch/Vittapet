# Política de versionamento da VittaPet Store

## Identificação atual
A primeira versão identificada em arquivo é **1.0.0-dev.5**, referência à base de desenvolvimento da Store incluindo a correção de frete configurável. Não é release pública nem significa homologação da loja.

## Regras de versão
- Durante a homologação da primeira entrega: `1.0.0-dev.1`, `1.0.0-dev.2`, `1.0.0-dev.3` etc.; incrementar o sufixo a cada conjunto de correções efetivamente integrado à `main`.
- `1.0.0-rc.1`: apenas quando todos os requisitos P0 forem aprovados em ambiente de homologação e faltar somente a verificação de implantação final.
- `1.0.0`: somente após QA, APIs, segurança, responsividade, dados de operação e InfinityFree validados. Tag/release GitHub oficial nessa ocasião, nunca antes.
- Após versão estável: `1.0.1` para correções compatíveis, `1.1.0` para novas funcionalidades compatíveis, `2.0.0` para o ecossistema de serviços e mudanças maiores.
- Uma integração que apenas melhora a documentação ou CI também pode incrementar `dev.N` se tiver impacto relevante na homologação; registrar claramente a natureza do incremento.

## Procedimento obrigatório em cada Pull Request
1. Abrir branch a partir da `main` atual.
2. Implementar alterações e testes; não expor segredos.
3. Atualizar `VERSION` e `CHANGELOG.md` com mudanças, testes executados e limitações.
4. Ajustar documentação técnica e matriz de casos afetados.
5. Exigir execução verde do gate PHP e MySQL, revisão QA e conferir diff.
6. Fazer merge em `main` sem `force push`; confirmar `VERSION` no destino.
7. **Não criar tag stable** quando frete/cobertura, estorno ou outras falhas P0 estiverem pendentes.

## Histórico resumido
| Identificador | Alteração | Estado |
|---|---|---|
| 1.0.0-dev.1 | Fundamentos da Store, checkout, Pix WhatsApp, mapa, segurança, QA HTTP/MySQL e frete fixo configurável | Candidato de desenvolvimento, dependente de QA |
| 1.0.0-dev.2 | Filtro administrativo e transacional por prefixos de CEP, testes de cobertura | Integrada na main, CI aprovado |
| 1.0.0-dev.3 | Registro de estorno manual pré-envio, auditoria temporal e atualização de dashboard | Integrada na main, CI aprovado |
| 1.0.0-dev.4 | Concorrência real de estoque, refatoração SRP e pacote de hospedagem allowlist | Integrada na main; CI aprovado |
| 1.0.0-dev.5 | Testes browser/viewport e integração JSON simulada + checklist real | Em homologação |

Histórico detalhado: [CHANGELOG.md](../CHANGELOG.md).

# Relatório de liberação — Revisão documental 09

**Data:** 17/11/2026  
**Última versão funcional aprovada no CI antes da revisão:** `1.0.0-dev.5`. **Ciclo de correções documentais:** `1.0.0-dev.6`.  
**Objetivo:** alinhar documentação com código implementado, registrar evidências e preparar decisões de release.

## Evidências existentes
- GitHub Actions `44111312517`: PHP lint aprovado.
- HTTP/MySQL: **78 asserções aprovadas** em banco temporário.
- Concorrência: dois processos com PDO, uma venda e uma rejeição; estoque final zero.
- Playwright: **15 cenários Chromium aprovados** (cinco cenários em três tamanhos emulados).
- APIs ViaCEP e Nominatim: somente respostas **simuladas** na suíte Playwright.
- Git: migrações 001 a 009, docs, testes e script de empacotamento presentes; arquivos de produção organizados por lista permitida.

## Inconsistências de documentação corrigidas nesta revisão
1. README mencionava implementação pendente e testes não executados apesar da `dev.5`.
2. Arquitetura citava apenas migrations 001–006, frete sempre zero e receitas por data de criação.
3. Instalação dizia que não havia testes executáveis ou proteção contra força bruta.
4. Matriz marcava concorrência como não testada e não refletia a cobertura de CEP, estorno e pagamentos em datas reais.
5. Guia de deploy tinha sequência de migrations mal formatada e listava diretórios que não devem compor o pacote.

## Bloqueadores ainda abertos
- Falta verificação de APIs reais/CORS, WhatsApp no celular, mapa com tiles e acessibilidade/dispositivos físicos.
- Revisão de XSS, autorização e dados pessoais com evidência manual.
- O filtro de prefixo CEP não verifica existência/viabilidade de endereço; operação real precisa homologação.
- O processo de estorno pós-expedição e livro-razão contábil completo não existem.
- Implantação no InfinityFree não executada por decisão do gestor de deixá-la por último.

## Decisão de release
**Não promover a rc.1 nesta revisão.** Um CI verde não substitui a etapa manual e operacional. O artefato pode continuar na série `1.0.0-dev.N` enquanto a documentação e os testes são fechados, com `VERSION` e `CHANGELOG` sincronizados.

Próximas ações: checklist de testes manuais, recolhimento de evidências e resolução de P0 → revisão técnica → rc.1 → InfinityFree (último portão) → v1.0 estável, somente se aprovado.

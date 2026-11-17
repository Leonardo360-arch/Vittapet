# Relatório de execução QA 01 — VittaPet Store 1.0

**Data:** 17/11/2026  
**Alvo:** branch `test/homologacao-v1-documentacao`  
**Commit do teste:** `2ab1b8189a29bd786c7cd917445011d04c4e819f`  
**Execução GitHub Actions:** https://github.com/Leonardo360-arch/Vittapet/actions/runs/44097037063  
**Ambiente:** GitHub Actions Linux, PHP 8.2, MySQL 8 isolado via container, servidor PHP embutido, dados exclusivamente fictícios.  
**Condição:** execução foi concluída com sucesso; não testou rede InfinityFree nem interfaces em navegador real.

## Resultado observável
| Gate | Resultado | Evidência |
|---|---|---|
| PHP lint em todos .php | **Aprovado** | job `php-lint`, conclusão success |
| Migrations SQL 001 a 006 | **Aprovado** | job `mysql-http` |
| Testes HTTP + MySQL | **55 verificações aprovadas; 0 falhas registradas nessa execução** | log `mysql-http` |
| Login, senha errada e sessão | Aprovado pela suite | script `tests/integration_http.php` |
| CRUD e categoria/estoque | Aprovado pela suite | logs 15–17 e 26 |
| Carrinho e checkout | Aprovado pela suite | adição/edição/remoção, token e replay |
| Pix/WhatsApp e cartão na entrega | Aprovado pela suite em simulação | estados, URL preenchida, confirmação manual |
| Permissões/CSRF e rate limit | Aprovado pela suite | CSRF inválido, logout, limites HTTP 429 |
| Estoque/histórico/financeiro | Aprovado pela suite | SQL após operações e estados |

## O que significa '55'
São **55 asserções automatizadas**, não 55 módulos concluídos nem aprovação de todos os 42 cenários da matriz. Parte dos cenários P0 permanece manual e pode bloquear a homologação.

## Não testado nesta execução
- Condições de corrida em dois servidores disputando o último item; sessão simultânea e múltiplos nós
- APIs ViaCEP e Nominatim com rede externa/browser real, CORS e fallback
- Interação real do aplicativo WhatsApp; a suíte valida somente a URL pré-preenchida
- Navegadores móveis, acessibilidade, contrastes, responsividade e mapa
- InfinityFree, FTP, SSL, rewrite e proteção efetiva de pastas privadas
- Cobrança de frete real, estorno operacional, conciliação bancária ou transferência real de dinheiro
- Auditoria de código completa PSR-12/SOLID e testes de carga/segurança especializados

## Avaliação e liberação
**Gate automatizado: APROVADO.**
**Selo interno final: NÃO EMITIDO.**
**Publicação comercial: BLOQUEADA** até encerramento dos P0, regras de frete/estorno e teste final no host.

Recomendação: registrar os casos manuais em `docs/RELATORIO_TESTE_MODELO.md`, concluir verificações de segurança/UX/integrações e somente então preparar a hospedagem, como última etapa.

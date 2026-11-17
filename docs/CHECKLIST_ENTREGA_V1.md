# Checklist de liberação — VittaPet Store v1

**Preparação:** 17/11/2026. **Prazo acadêmico:** 23/11/2026.  
**Versão do código atual antes desta revisão:** 1.0.0-dev.5.  
**Decisão:** **NÃO PROMOVER PARA rc.1 AINDA**; existem validações P0 manuais e de infraestrutura pendentes.

## Portões e evidência
| Portão | Evidência verificável | Parecer |
|---|---|---|
| Sintaxe PHP | GitHub Actions `44111312517` | Aprovado |
| HTTP/MySQL | 78 asserções, GitHub Actions `44111312517` | Aprovado nos cenários automatizados |
| Concorrência | 2 processos, 1 venda, 1 rejeição, estoque 0 | Aprovado no cenário automatizado |
| Visual | 15/15 cenários Playwright no Chromium emulado | Aprovado na emulação |
| ViaCEP/Nominatim | Respostas JSON **simuladas** no Playwright | Teste real de rede pendente |
| WhatsApp | URL `wa.me` e mensagem emulada | Envio/abertura em aparelho real pendente |
| Mapa | Fallback e links de rotas testados | CDN e tiles reais pendentes |
| Documentação | Links e schema conferidos por `scripts/check_docs.php` | Gate automatizado; revisão humana pendente |
| Segurança final | Autorização, privacidade, CSRF e arquivos internos | Cobertura parcial; revisão manual pendente |
| Implantação InfinityFree | HTTPS, MySQL, `.htaccess`, ciclo completo | **Não iniciada — última etapa** |

## Pendências para a candidata rc.1
- [ ] Executar ViaCEP **real** com CEP de teste, verificar Network/JSON/falha e resultado visível.
- [ ] Executar Nominatim **real**, sem spam, para endereço autorizado, conferir lat/lon e atribuições.
- [ ] Verificar Leaflet e tiles OSM reais, mapa responsivo e alternativas quando CDN indisponível.
- [ ] Testar WhatsApp em celular real com número comercial autorizado, valor/token e confirmação de envio sem transmissão automática.
- [ ] Conferir telas no Chrome Android físico e desktop, teclado, rótulos, contraste e ausência de rolagem lateral nas páginas públicas e administrativas.
- [ ] Testar XSS, controle de sessão, CSRF, rastreio com token inválido, limitação de consultas e proteção de dados em cenário manual.
- [ ] Confirmar com o lojista os CEPs atendidos, custo do frete, processo manual Pix e regras de devolução.
- [ ] Revisar documentos de ponta a ponta, corrigir lacunas entre especificação e execução, arquivar prints e vereditos da equipe.
- [ ] Verificar que não há P0 reprovado ou sem evidência; então criar `1.0.0-rc.1` como **candidata**, sem chamar de estável.

## Após rc.1 — **última etapa**
- [ ] Criar conta/banco e importar **001–009** no InfinityFree via phpMyAdmin.
- [ ] Gerar pacote com `scripts/build_deploy.php`; transferir apenas `build/htdocs`.
- [ ] Configurar `config/local.php` apenas no servidor e login com senha hash não exposta.
- [ ] Confirmar HTTP/HTTPS, rotas públicas, proteção de `config/` e `includes/` com 403/404.
- [ ] Testar compra fictícia, painel, Pix pelo WhatsApp, estoque, despesas e rastreio no domínio real.
- [ ] Documentar evidências da URL, data, commit e responsável. Corrigir falhas antes de `1.0.0`.

## Formato de evidência
Para cada teste: código QA-XXX, data/hora, responsável, commit, navegador/dispositivo, dados fictícios, passos, esperado, obtido, print/log redigido, defeito (se houver), reteste e veredito.

**Selo interno:** não emitido, pois não equivale a certificação independente e não deve esconder cenários ainda não testados.

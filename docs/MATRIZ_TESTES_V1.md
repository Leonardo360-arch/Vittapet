# Matriz de testes — VittaPet Store v1.0 (DW2)
**Data:** 17/11/2026 | **Revisão da matriz:** 1.1 | **Critério:** evidência objetiva, sem notas fictícias.

## Classificações
- **P0 bloqueador:** falha impede a homologação.
- **P1 alto:** falha deve ser corrigida antes da entrega final.
- **P2 melhoria:** documentar e decidir correção.
- **Automático (CI):** executado por GitHub Actions com PHP 8.2 e MySQL 8 temporário; ver resultado do workflow, não presumir aprovação.
- **Manual:** requer navegador, inspetor de rede ou evidência humana; marcar *pendente* até executar.

## Casos funcionais, dados e segurança
| ID | Prioridade | Área | Procedimento | Resultado esperado | Tipo |
|---|---|---|---|---|---|
| QA-001 | P0 | Infra | Aplicar migrations 001 a 009 em DB vazio | Todas tabelas criadas, FKs íntegras | CI |
| QA-002 | P0 | Admin | Criar login usando hash gerado no terminal | Admin autenticável sem senha exposta | CI |
| QA-003 | P0 | Segurança | Acessar /admin/produtos.php sem sessão | HTTP 302 para login | CI |
| QA-004 | P0 | Login | Senha incorreta | Não autentica | CI |
| QA-005 | P0 | Login | Senha correta | Sessão, dashboard acessível | CI |
| QA-006 | P0 | CRUD | Cadastrar produto | Persistido no MySQL | CI |
| QA-007 | P0 | CRUD | Editar produto/preço | Alteração persistida | CI |
| QA-008 | P0 | CRUD | Desativar produto | Exclusão lógica, sem destruir histórico | CI |
| QA-009 | P0 | Catálogo | Consultar homepage | Produto ativo listado | CI |
| QA-010 | P0 | Carrinho | Adicionar produto | Quantidade aumenta | CI |
| QA-011 | P0 | Carrinho | Alterar quantidade via Fetch | JSON e subtotal coerentes | CI |
| QA-012 | P0 | Carrinho | Adicionar acima estoque/limite | HTTP 422 e estado preservado | CI |
| QA-013 | P0 | Carrinho | Remover último item | Quantidade e subtotal zero | CI |
| QA-014 | P0 | Checkout | Pedido Pix com endereço válido | Cria cliente/pedido/itens com token | CI |
| QA-015 | P0 | Estoque | Registrar compra | Debita saldo uma vez e grava movimento | CI |
| QA-016 | P0 | Pagamento | Pix novo | pendente/aguardando_pagamento | CI |
| QA-017 | P0 | WhatsApp | Consultar rastreio Pix com WhatsApp configurado | Link wa.me com token e total | CI |
| QA-018 | P0 | Pagamento | Admin confirma Pix | confirmado/em_separacao com histórico | CI |
| QA-019 | P0 | Cartão | Novo pedido débito | a_receber_na_entrega/em_separacao | CI |
| QA-020 | P0 | Cartão | Confirmar antes da entrega | Bloqueado, receita não reconhecida | CI |
| QA-021 | P0 | Logística | Embalar/despachar/entregar | Histórico íntegro | CI |
| QA-022 | P0 | Cartão | Confirmar após entregue | Marca confirmado e relatório reconhece | CI |
| QA-023 | P1 | Financeiro | Registrar despesa | Persistida | CI |
| QA-024 | P1 | Dashboard | Consultar métricas com pedidos | Página responde, valores calculáveis | CI |
| QA-025 | P0 | Logout | GET/POST com CSRF | GET 405, POST encerra sessão | CI |
| QA-026 | P0 | Sessão | Tentar acessar painel após logout | Não autorizado | CI |
| QA-027 | P0 | Login | Exceder 8 falhas ou 30 falhas/IP | HTTP 429 | Manual + DB |
| QA-028 | P0 | Rastreio | Exceder 40 consultas/IP/10 min | HTTP 429 | Manual + DB |
| QA-029 | P0 | Concorrência | Dois processos PHP/PDO disputam última unidade | 1 grava, 1 rejeita; sem estoque negativo | CI, cenário isolado aprovado |
| QA-030 | P0 | Duplicidade | Reenviar checkout (duplo clique/replay) | Não duplica pedido | CI aprovado para replay; duplo clique físico pendente |
| QA-031 | P0 | Pagamento | Tentar cancelar pedido pago | Bloqueio até estorno | Manual + DB |
| QA-032 | P0 | API 1 | ViaCEP CEP válido/erro/rede indisponível | JSON e fallback manual | Mock Playwright aprovado; API real pendente |
| QA-033 | P0 | API 2 | Nominatim endereço real e retorno JSON | Coordenadas gravadas, mapa exibido | Mock Playwright aprovado; API e tiles reais pendentes |
| QA-034 | P1 | Mapa | Sem localização cadastrada | Sem endereço fictício nem marcador falso | Navegador |
| QA-035 | P1 | Responsividade | Mobile, tablet e 1366px | Sem overflow; acessibilidade e formulários operáveis | Playwright: catálogo aprovado; demais telas/dispositivo físico pendentes |
| QA-036 | P0 | Segurança | CSRF inválido em formulário administrativo | HTTP 419, sem alteração SQL | Manual |
| QA-037 | P0 | Segurança | HTML/JS em nome do produto | Escapado, sem execução de script | Manual |
| QA-038 | P0 | Privacidade | Token de rastreio válido | Não exibe nome, telefone, endereço | Manual |
| QA-039 | P1 | WhatsApp | Link wa.me no dispositivo móvel | Abre conversa, requer envio explícito | Navegador |
| QA-040 | P0 | Infra | Servidor InfinityFree publica com URLs/HTTPS | Rotas respondem e DB funciona | **Por último** |
| QA-041 | P0 | Infra | GET /config/local.php e /includes/bootstrap.php | 403/404, nunca código ou credenciais | **Por último** |
| QA-042 | P0 | Documento | Código, SQL, APIs, procedimentos e riscos | Conteúdo e evidências consistentes | Revisão manual |

## Casos complementares após dev.5
| ID | Prioridade | Área | Cenário | Estado |
|---|---|---|---|---|
| QA-043 | P0 | Frete | Frete ausente bloqueia compra; zero é grátis explícito; taxa somada no servidor | CI aprovado |
| QA-044 | P0 | CEP | Prefixo permitido/negado, sem criação de pedido fora da cobertura | CI aprovado; área real pendente |
| QA-045 | P0 | Estorno | Pago e ainda não despachado: estorno externo previamente realizado, log e reposição única | CI aprovado; validação operacional pendente |
| QA-046 | P1 | Browser | ViaCEP/Nominatim com respostas JSON simuladas | 15 cenários totais de browser aprovados com outras verificações; API real pendente |
| QA-047 | P0 | Release | Documentação coerente, evidência real e versão candidata sem P0 aberto | Em revisão, não aprovado |

## Qualidade interna ('selo' metafórico)
**NÃO EMITIDO — aguardando homologação.** Para registrar APROVADO:
1. 100% dos P0 aprovados com evidência; nenhum bloqueador aberto.
2. PHP lint e integração HTTP/MySQL do CI verdes.
3. Cenários manuais em celular/desktop, APIs, carga mínima, CSRF e privacidade documentados.
4. Sem credenciais ou dumps no repositório; resultados e falhas reportados com transparência.
5. Fluxo de Pix, cartão e frete conforme regras aprovadas; sem cobrança fictícia em produção.
6. Revisão final da documentação da v1 e versão futura separada.

## Evidências
Cada teste manual deve registrar: ID, data/hora, versão/commit, ambiente, passos, dados fictícios sem segredos, resultado esperado, resultado observado, print ou log relevante, responsável pelo teste, veredito, defeito e reteste. Use `docs/RELATORIO_TESTE_MODELO.md`.

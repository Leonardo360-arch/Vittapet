# VittaPet Store — Documentação técnica oficial v1.0
**Edição:** 1.0 — 17/11/2026  
**Repositório:** `Leonardo360-arch/Vittapet`  
**Entrega acadêmica:** 23/11/2026  
**Estado:** implementação incremental; a liberação depende de homologação documentada.

## 1. Objetivo e delimitação
O sistema é uma **loja virtual de produtos pet** com vitrine pública, CRUD administrativo, carrinho, checkout, pedidos, estoque, rastreio logístico, integração ViaCEP, localização via Nominatim, mapa e acompanhamento financeiro. Não contempla serviços veterinários, agenda, clínica, galeria de cães ou plataforma social na v1.0.

**Papéis:** visitante/cliente (compra sem conta); administrador (credenciais por e-mail + senha e autorização no banco). O número do WhatsApp é dado comercial público, cadastrado no painel; não é senha nem chave Pix.

## 2. Arquitetura existente
Monólito PHP 8 + MySQL 8/PDO + HTML/CSS/Bootstrap 5 + JavaScript ES6. Sem gateway de pagamento, sem SDK WhatsApp, sem serviço de cron obrigatório.
```
Vittapet/
├── .github/workflows/php-quality.yml   # lint e testes CI
├── config/database.php                  # construção da conexão PDO
├── config/local.example.php             # modelo sem segredos
├── includes/bootstrap.php               # sessão, CSRF, autorização e HTML escape
├── public/
│   ├── index.php                        # catálogo e localização
│   ├── carrinho.php                     # estado do carrinho na sessão + Fetch
│   ├── checkout.php                     # pedido e baixa de estoque na transação
│   ├── rastrear.php                     # token e contato Pix via WhatsApp
│   ├── login.php, logout.php            # acesso administrativo
│   ├── admin/
│   │   ├── produtos.php                 # CRUD
│   │   ├── pedidos.php                  # pagamentos e logística
│   │   ├── dashboard.php                # indicadores
│   │   ├── financeiro.php               # despesas
│   │   └── configuracoes.php            # endereço, mapa, telefone WhatsApp
│   └── assets/css, assets/js            # identidade e mapa
├── database/migrations/001...009*.sql   # esquema incremental
├── scripts/create_admin.php             # provisionamento só por CLI
├── scripts/build_deploy.php             # pacote allowlist
├── scripts/check_docs.php               # consistência documental
├── includes/order_inventory.php         # regras transacionais de estoque
├── includes/delivery_coverage.php       # cobertura por prefixos de CEP
├── tests/integration_http.php           # integração PHP/MySQL
├── tests/concurrency_stock.php          # última unidade com dois processos
├── tests/browser/store.spec.cjs         # Playwright Chromium
├── deploy/INFINITYFREE.md               # apenas última etapa
└── docs/                                # especificações, arquitetura, QA, roadmap
```
**Nota de arquitetura:** ainda há controllers, SQL e HTML nos mesmos arquivos PHP; portanto o código **não está integralmente separado em camadas**. Refatorações incrementais deverão extrair regras de pedidos, preços e pagamentos para Services, persistência para Repositories, sem introduzir padrões artificiais. A v1.0 prioriza fluxo correto, segurança e testes de regressão antes de uma reescrita cosmética.

## 3. Banco de dados e relacionamentos
Migrações em ordem, nunca importar arquivos fora da ordem:
| Migration | Entidades | Finalidade |
|---|---|---|
| 001 | `usuarios`, `categorias`, `produtos` | identificação, catálogo, preço/custo, estoque |
| 002 | `clientes`, `pedidos`, `pedido_itens`, `pedido_historico`, `movimentacoes_estoque` | pedidos, snapshots e auditoria de fluxo |
| 003 | `despesas` | despesas operacionais |
| 004 | `configuracoes_loja` | nome/endereço/latitude/longitude |
| 005 | alteração de `pedidos`, coluna `whatsapp` | Pix e débito/crédito na entrega |
| 006 | `login_tentativas`, `consultas_rastreio` | limites de acesso |
| 007 | coluna `configuracoes_loja.frete_fixo` | frete fixo configurável |
| 008 | coluna `configuracoes_loja.cep_prefixos_entrega` | cobertura por CEP |
| 009 | colunas `pedidos.pago_em`, `estornado_em`, `observacao_estorno` | registro de recebimento e estorno |

Relações: `produtos.categoria_id → categorias.id`; `pedidos.cliente_id → clientes.id`; `pedido_itens.pedido_id → pedidos.id`; `pedido_itens.produto_id → produtos.id`; `pedido_historico.pedido_id → pedidos.id`; `movimentacoes_estoque.produto_id → produtos.id`; `movimentacoes_estoque.pedido_id → pedidos.id`. Chaves estrangeiras, índices e transações usam InnoDB; dinheiro usa `DECIMAL(10,2)` no banco.

## 4. Regras comerciais implementadas
**Catálogo:** filtrar categorias, exibir produtos ativos e disponibilidade; CRUD usa exclusão lógica para preservar histórico.

**Carrinho:** sesssão PHP armazena IDs/quantidades; preços atuais vêm do banco; eventos JavaScript atualizam totais via Fetch. Limite por produto validado. O servidor recalcula valores ao fechar a compra; o total que vier do navegador não é confiável.

**Checkout:** recebe comprador e endereço, consulta ViaCEP por JSON ao sair do campo CEP; completa o endereço manualmente se API falhar. Bloqueio transacional de linhas de produtos `FOR UPDATE`, inserção de cliente/pedido/itens, baixa de estoque e histórico em transação. Token de rastreio com parte aleatória criptograficamente segura. Frete fixo é configurado pelo administrador: valor não definido impede checkout, zero é escolha explícita. O checkout valida prefixos de CEP autorizados; a existência do endereço e a rota real ainda exigem conferência.

**Pix antecipado:** `status_pagamento=pendente`, `status_logistico=aguardando_pagamento`; link `wa.me` abre mensagem pré-preenchida ao WhatsApp do estabelecimento, contendo código e valor total. Cliente confirma envio, lojista envia chave Pix, confere recebimento real e confirma no painel; status vira `confirmado/em_separacao` e o histórico é atualizado.

**Cartão na entrega:** `debito_entrega` ou `credito_entrega`; o pedido inicia `a_receber_na_entrega/em_separacao` e avança para embalado, saiu para entrega e entregue. A confirmação financeira no painel só deve ocorrer após cobrança efetiva.

**Cancelamento:** pedido pago antes da expedição pode registrar estorno **já executado externamente**, exigindo justificativa, com cancelamento e devolução de estoque transacional; o sistema não transfere dinheiro. Pós-despacho exige fluxo de devolução separado.

**Rastreio:** código não sequencial; consulta exibe apenas situação financeira, logística e histórico, sem nome/endereço/itens; limitação de consultas por IP.

**Financeiro:** relatório de receita considera pedidos com pagamento confirmado, custo histórico congelado de itens e despesas lançadas. Os filtros de receita utilizam `pago_em` para recebimentos confirmados; pedidos legados sem essa data exigem conciliação. Estornos retiram o pedido confirmado do saldo agregado, sem livro-razão histórico. O resultado é estimado e não equivale a demonstrações contábeis.

## 5. APIs e serviços externos
| Integração | Local | Dado JSON | Comportamento de falha |
|---|---|---|---|
| ViaCEP | `public/checkout.php` | `logradouro`, `bairro`, `localidade`, `uf` | endereço pode ser preenchido manualmente |
| Nominatim OSM | `public/admin/configuracoes.php` | latitude e longitude | administrar manualmente a coordenada |
| Leaflet + tiles OSM | `public/index.php`, `assets/js/mapa.js` | renderização cartográfica, não conta como terceira API JSON | endereço textual visível |

Geocodificação é manual, uma consulta ao configurar a loja; coordenadas são armazenadas, evitando chamadas repetidas no rodapé. Usar atribuição e respeitar política dos provedores, incluindo limite global Nominatim.

## 6. Segurança
- Senha nunca em texto claro no MySQL; `password_hash` na criação e `password_verify` na autenticação.
- Login limitado por tentativas persistentes; sessão validada contra conta ativa, tempo de inatividade; logout somente POST+CSRF.
- PDO com parâmetros; saída HTML escapada por `e()` e autorização administrativa em cada rota sensível.
- Rastreio por token aleatório e contador de tentativas; jamais retornar dados pessoais do comprador ao público.
- Dados de autenticação em `config/local.php` local, não no Git. Para produção, conferir `htdocs/.htaccess` e arquivos privados com HTTP 403/404.
- Dados do cliente para finalidade do pedido, sem uso para campanha de marketing sem consentimento.

## 7. Provisionamento da operação (sem dados fictícios)
1. Importar as nove migrations (001–009) em MySQL isolado.
2. Configurar `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` via variáveis locais ou `config/local.php` não versionado.
3. Criar login administrativo por CLI: `ADMIN_EMAIL`, `ADMIN_NAME`, `ADMIN_PASSWORD` (mínimo 12 caracteres) + `php scripts/create_admin.php`. Não publicar senha em comando gravado no histórico do terminal; preferir prompt interativo/variável de ambiente de sessão.
4. Login pelo e-mail informado no provisionamento. Não existe e-mail administrador padrão publicado.
5. No admin `configuracoes.php`, preencher WhatsApp comercial (somente números `55+DDD+telefone`), endereço e coordenadas. Não colocar número pessoal sem autorização nem inventar endereço.
6. Testar Fluxo Pix, cartão na entrega e confirmação manual. O link WhatsApp **não envia mensagem sozinho**.

## 8. Contratos HTTP principais
| Método | Rota | Acesso | Efeito |
|---|---|---|---|
| GET | `/index.php` | público | catálogo |
| GET / POST | `/carrinho.php` | sessão visitante | listar/alterar carrinho |
| GET / POST | `/checkout.php` | sessão visitante | mostrar checkout/registrar pedido |
| GET | `/rastrear.php?token=...` | token | estado limitado do pedido |
| GET / POST | `/login.php` | público | login administrativo |
| POST | `/logout.php` | admin | finalizar sessão |
| GET / POST | `/admin/produtos.php` | admin | CRUD |
| GET / POST | `/admin/pedidos.php` | admin | pagamento/status |
| GET | `/admin/dashboard.php` | admin | métricas |
| GET / POST | `/admin/financeiro.php` | admin | despesas |
| GET / POST | `/admin/configuracoes.php` | admin | localização/WhatsApp |

## 9. Testes e critérios de liberação
`php -l` em todos arquivos PHP, migrations 001–009 em MySQL 8 isolado, 78 asserções HTTP/MySQL, concorrência real entre duas conexões e 15 cenários Chromium emulados passaram no CI da dev.5. APIs externas reais, dispositivos físicos e segurança manual permanecem necessários. Referência: [Matriz de QA](MATRIZ_TESTES_V1.md), [Checklist de homologação](HOMOLOGACAO_V1.md).

**Critério de bloqueio:** sem aprovação dos cenários críticos (login, CRUD, carrinho, checkout, status financeiro, estoque, privacidade) não há selo de qualidade interno nem release final, mesmo com CI verde.

## 10. Implantação — etapa final
Somente após homologação: `deploy/INFINITYFREE.md`. O hosting gratuito tem `htdocs` como diretório web; não enviar testes, migrations, backups, docs ou segredos desnecessários e validar HTTP 403/404 para arquivos privados. A publicação exige evidências reais, não apenas organização documental.

## 11. Futuro
Separar [plano posterior à entrega](PLANO_POS_ENTREGA.md) e o [backlog v2](VERSAO_2_0_BACKLOG.md). Nenhuma função veterinária entra na entrega de DW2.

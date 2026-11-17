# VittaPet Store — Especificação funcional e técnica v1.0

**Documento:** 1.0.0 | **Data:** 17/11/2026 | **Estado:** implementação incremental, homologação em andamento e entrega DW2 em 23/11/2026. A documentação não prova que os requisitos foram testados ou publicados.

## 1. Identificação e objetivo
Projeto acadêmico de Desenvolvimento Web II: uma **loja virtual de produtos pet**, com catálogo, CRUD em MySQL, carrinho reativo, checkout, controle de pedidos, expedição, dashboard financeiro, duas APIs e hospedagem InfinityFree. A v1.0 constitui a base reaproveitável para o ecossistema de serviços da v2.0.

## 2. Delimitação do escopo
**Incluído:** comercialização de produtos, categorias, clientes de pedidos, estoque, carrinho, endereço, pedidos, pagamento manual controlado, separação/embalagem/entrega, indicadores, relatórios e localização da loja.
**Excluído:** clínica, profissionais veterinários, prontuários, receitas, vacinação, banho/tosa, agendamento de serviços, portal clínico, galeria de cães, mural social, fidelidade, concursos, chatbot e notificações automáticas; esses itens ficam documentados para v2.0.
**Sem gateway na v1.0:** aceitar pedido não equivale a receber pagamento. Se houver Pix antecipado ou débito/crédito na entrega, a confirmação será manual por operador autorizado, e o site não deverá afirmar pagamento automático.

## 3. Atores
- **Visitante/cliente:** navega sem login, monta carrinho, informa dados de checkout, recebe token e consulta andamento com dados limitados.
- **Administrador:** autentica-se e gerencia produtos, categorias, estoque, pedidos, status, despesas, configurações e indicadores.
A v1.0 utiliza checkout como convidado; contas de tutores serão adicionadas na v2.0 sem quebrar o vínculo histórico dos pedidos.

## 4. Requisitos funcionais
| ID | Requisito | Critério de aceite |
|---|---|---|
| RF01 | Página inicial original | Produtos e categorias reais, navegação e rodapé com mapa |
| RF02 | Catálogo | Filtrar por categoria e pesquisar; apresentar preço e disponibilidade |
| RF03 | CRUD de produtos | Criar, listar, atualizar e desativar/excluir logicamente no MySQL |
| RF04 | Categorias e imagens | Associar produto a categoria válida; validar imagens e exibi-las |
| RF05 | Carrinho reativo | Inclusão, remoção e alteração de quantidade recalculam totais imediatamente |
| RF06 | Checkout | Coletar identificação mínima, contato, endereço e método de pagamento; gerar pedido |
| RF07 | ViaCEP | Consultar CEP por Fetch, tratar JSON, erros e preenchimento manual |
| RF08 | Geolocalização comercial | Geocodificar endereço da loja para lat/lon com API JSON, salvar resultado |
| RF09 | Mapa da loja | Leaflet/OSM, marcador, endereço e rota externa, com fallback |
| RF10 | Pedidos | Salvar cabeçalho e itens, preço congelado, total e token forte |
| RF11 | Rastreio | Consultar por token sem expor endereço/dados pessoais ou itens |
| RF12 | Fluxo logístico | Separação, embalagem, expedição e entrega, com histórico de transições |
| RF13 | Pagamento | Estado independente de logística; confirmação manual auditável |
| RF14 | Administração | Login/logout, permissão, lista e atualização autorizada |
| RF15 | Estoque | Limitar quantidades; registrar saídas e reversões consistentes |
| RF16 | Dashboard | Pedidos por estado, receita confirmada, estoque baixo, ticket médio e produtos mais vendidos |
| RF17 | Financeiro | Despesas, CMV, receita confirmada, resultado estimado e filtros por período |
| RF18 | Configuração | Endereço da loja, coordenadas e contatos editáveis |
| RF19 | Responsividade | Telas utilizáveis em mobile e desktop; componentes slim |
| RF20 | Relatórios | Visões por período, pedidos e totais consistentes com banco |

## 5. Fluxos e regras de negócio
### 5.1 Carrinho
- Quantidades inteiras positivas; remoção ao zerar; item eliminado deixa de contar no total.
- UI recalcula `subtotal_item = preço_atual * quantidade`, `subtotal = soma(itens)` e `total = subtotal - desconto_valido + frete` (se houver).
- Não há botão de atualizar carrinho; eventos reativos atualizam totais e quantidade no ícone do carrinho.
- Backend recalcula preços, promoção, frete, disponibilidade e total antes da gravação. Nunca aceitar total enviado pelo navegador como fonte da verdade.
- Prevenir cliques concorrentes/duplicados e pedidos duplicados; checkout utiliza chave de idempotência quando implementado.
- Preço de pedido fechado é imutável em `pedido_itens.preco_unitario`.
### 5.2 Pedido e pagamento
- Separar `status_pagamento`: `pendente | a_receber_na_entrega | confirmado | estornado` de `status_logistico`: `aguardando_pagamento | em_separacao | embalado | saiu_para_entrega | entregue | cancelado`.
- Sequência normal: aguardando pagamento → em separação → embalado → saiu para entrega → entregue.
- Pagamento confirmado permite iniciar a separação, salvo modalidade explícita de pagamento na entrega; nessa modalidade, status logístico pode avançar com pagamento pendente, sem contabilizar receita confirmada.
- Cancelamento exige regra conforme fase, reversão consistente de estoque e eventual pagamento; transições inválidas são rejeitadas.
- Cada mudança gera histórico com status anterior/novo, data/hora e responsável. Rastreio público exibe somente estado e horários mínimos.
### 5.3 Estoque
- Gravar pedido, itens e movimentações com transações MySQL/InnoDB e `SELECT ... FOR UPDATE` quando aplicável; evitar venda simultânea do último item.
- Modelo de estoque escolhido deve deixar explícito quando ocorre reserva/baixa; cancelar reverte apenas operações efetivamente realizadas.
### 5.4 Financeiro
- **Receita confirmada:** soma atual dos pedidos ainda confirmados com `pago_em` no período; estornos integrais deixam de compor esse saldo agregado. Não se trata de fluxo de caixa histórico.
- **CMV:** soma do custo congelado dos itens vendidos; manter custo unitário histórico.
- **Resultado estimado:** receita confirmada menos CMV menos despesas e custos de entrega assumidos pela loja, sem rotular como lucro contábil definitivo.
- **Ticket médio:** receita confirmada / quantidade de pedidos pagos (evitar divisão por zero).
- Nunca somar pedidos ainda não pagos à receita efetivamente recebida.
- Registrar despesas separadamente, com valor, categoria e data.

## 6. Telas públicas
1. **Home:** hero da marca, categorias, produtos em destaque, vantagens comerciais, seção 'Onde estamos' e rodapé.
2. **Loja:** grid de cards, filtros, busca e botão de adicionar ao carrinho.
3. **Produto:** foto, descrição, categoria, preço, estoque e adicionar.
4. **Carrinho:** lista, foto, valor unitário, controle slim, remover, resumo dinâmico.
5. **Checkout:** identificação, contato, CEP/ViaCEP, endereço, entrega, meio de pagamento e revisão final.
6. **Confirmação/rastreio:** token, andamento, histórico reduzido.
7. **Contato/localização:** endereço, informações da loja, mapa/rotas.

## 7. Telas administrativas
1. Login/logout com sessão protegida.
2. Dashboard com cards de receita confirmada, resultado estimado, pedidos em aberto e estoque baixo; gráficos por período e últimos pedidos.
3. Produtos: tabela pesquisável, criação, edição, exclusão lógica.
4. Categorias: listagem e cadastro simplificado.
5. Pedidos: detalhes, status de pagamento e logística, histórico e transições autorizadas.
6. Estoque: saldo e movimentações.
7. Financeiro: despesas, receitas e relatórios por período.
8. Configurações: informações públicas da loja, endereço e coordenadas.

## 8. APIs obrigatórias do exercício
**API A — ViaCEP** (`https://viacep.com.br/ws/{cep}/json/`): CEP com oito dígitos; consumir JSON no formulário de checkout; sinalizar `erro`, falha de rede e edição manual.
**API B — geocodificação:** Nominatim/OpenStreetMap ou serviço equivalente com resposta JSON de coordenadas da loja; consulta pela administração, cache em banco, política de uso/identificação e limite de solicitações observados. Geocodificar endereço *não* é obter GPS do visitante.
**Mapa:** Leaflet.js sobre tiles licenciados/permitidos e atribuição obrigatória; biblioteca de apresentação não conta como terceira API requerida. Verificar a política de uso dos tiles e fallback textual.
**Evidências:** registrar captura ou log de resposta JSON das duas integrações e resultado visível no site.

## 9. Banco relacional proposto
InnoDB, UTF8MB4, `DECIMAL(10,2)` para moeda, índices e FKs:
- `usuarios(id, nome, email UNIQUE, senha_hash, perfil, ativo, created_at)`
- `categorias(id, nome, descricao, ativo)`
- `produtos(id, categoria_id FK, nome, descricao, preco_venda, custo_unitario, estoque_atual, estoque_minimo, imagem_path, ativo, created_at, updated_at)`
- `clientes(id, nome, email, telefone, created_at)` para pedidos; futuramente ligar a credenciais
- `pedidos(id, cliente_id FK, token_rastreio UNIQUE, endereco_snapshot, subtotal, desconto, frete, total, status_pagamento, status_logistico, metodo_pagamento, created_at)`
- `pedido_itens(id, pedido_id FK, produto_id FK, quantidade, preco_unitario, custo_unitario)`
- `pedido_historico(id, pedido_id FK, status_anterior, status_novo, usuario_id NULL, criado_em)`
- `movimentacoes_estoque(id, produto_id FK, pedido_id NULL, tipo, quantidade, criado_em)`
- `transacoes_financeiras(id, pedido_id NULL, tipo, categoria, valor, descricao, confirmado_em)`
- `despesas(id, categoria, valor, data_competencia, descricao)` caso não se centralize tudo no livro-caixa
- `configuracoes_loja(id, nome, endereco, cep, latitude, longitude, link_google_maps, whatsapp)`
A lista é modelo-alvo da v1.0, sujeito à validação no momento de criar migrations. Não criar duplicação contábil entre `despesas` e `transacoes_financeiras`: a implementação deve eleger uma única fonte canônica para relatórios.

## 10. Arquitetura e organização
PHP 8.x, MySQL 8.x, PDO, Bootstrap 5, HTML5, CSS3, JavaScript ES6+. Monólito modular: `public/` (webroot), `src/Controllers`, `src/Services`, `src/Repositories`, `config/`, `includes/`, `database/migrations`, `database/seeds`, `storage/`, `tests/`, `docs/`. Definir `BASE_URL` e caminhos que funcionem no InfinityFree; jamais publicar credenciais e diretórios privados.
SRP e Service Layer/Repository para separação concreta de responsabilidades; outros padrões somente se justificáveis.

## 11. Identidade visual
- Verde principal `#0F766E`; turquesa `#2DD4BF`; laranja de ação `#F97316`; creme `#FFF7ED`; texto `#1F2937`.
- Poppins 600/700 títulos; Inter 400/500 corpo. Ícones SVG/Lucide finos e consistentes, sem símbolos improvisados.
- Tokens centralizados em `public/assets/css/tokens.css`; CSS de base/layout/componentes/páginas/admin separados.
- Botões e controles compactos, radius 12–16px, grid consistente, estados de foco, feedback e contraste WCAG 2.1 AA.
- Carrinho sem botão de atualização; somatórios reativos.

## 12. Segurança e privacidade
`password_hash`/`password_verify`, regeneração de sessão, RBAC no servidor, CSRF em escrita, PDO preparado, validação de uploads e saída HTML, tokens fortes de rastreio, limite de requisições, mensagens genéricas para erros, logs sem segredos, HTTPS em produção. Apenas dados mínimos do comprador; política de privacidade e consentimento específico para marketing quando houver. Dados sensíveis e configurações fora do webroot.

## 13. Requisitos não funcionais
Responsivo/mobile-first; mensagens acessíveis; integridade do checkout; desempenho adequado; tratamento de indisponibilidade de API; documentação reproduzível; versionamento e migrações sem destruição de dados; backups e restauração planejados.

## 14. Testes e critérios de aceite
- [ ] CRUD: criar/ler/editar/desativar com persistência MySQL e painel protegido
- [ ] Adicionar/remover produto, editar quantidades, carrinho vazio, limites de estoque e totais sem refresh
- [ ] Preços e frete recalculados no servidor na finalização
- [ ] Pedido com itens, histórico e token; transições logísticas e financeiras consistentes
- [ ] Pagamento pendente não aparece como recebido; estorno não infla receita
- [ ] Financeiro com dados do banco, filtros e valores coerentes
- [ ] ViaCEP trata JSON, CEP inválido e falhas; mapa consome coordenadas JSON e possui atribuição
- [ ] Mobile/desktop, CSS original, links e rotas funcionando
- [ ] HTTPS, controle de acesso e formulários protegidos
- [ ] URL InfinityFree acessível publicamente e documentação entregue no Teams
- [ ] Evidência de cada requisito (print ou teste documentado)
**Estado:** nenhum item deve ser assinalado antes do teste real.

## 15. Prioridade acadêmica e riscos
Pontuação do exercício: CRUD 2,5; originalidade/interface 2,0; documentação 2,0; hospedagem 2,0; duas APIs 1,5. A loja completa é um escopo ampliado para o prazo de 23/11; o gestor deve proteger os cinco critérios avaliativos antes de otimizações do dashboard. APIs, frete e meios de pagamento não devem ser declarados funcionais sem execução e validação. A v1.0 não estará lançada apenas porque o documento tem número de versão.

## Complementos normativos atualizados
- [Documentação técnica da implementação atual](ARQUITETURA_TECNICA_V1.md)
- [Matriz de testes e evidências](MATRIZ_TESTES_V1.md)
- [Condições objetivas para qualidade](HOMOLOGACAO_V1.md)
- [Planejamento da versão futura com datas após a entrega](PLANO_POS_ENTREGA.md)

**Atenção:** o texto acima especifica o produto-alvo; a implementação atual inclui checkout Pix via WhatsApp e débito/crédito na entrega, com validação manual, e permanece sujeita a testes. O escopo da versão 2 não entra na entrega acadêmica.

## 12. Estado de implementação e exceções (17/11/2026)
Esta especificação descreve também **objetivos ainda não entregues**; não tratá-la como prova de que todas as telas e funcionalidades existem. O código em `main` da `dev.5` passou 78 asserções PHP/MySQL, concorrência isolada e 15 testes Chromium emulados. Há frete fixo configurável, cobertura por prefixos de CEP, estorno manual pré-expedição e relatório financeiro por `pago_em`. Não há gateway Pix nem transferência automática de estorno. A home possui filtro de categoria; busca textual e telas dedicadas de produto/categoria requerem verificação de implementação antes de declaração de aceite. O filtro de CEP não comprova a existência do endereço. Testes das APIs externas reais, aparelhos físicos, acessibilidade e InfinityFree continuam pendentes. Consulte [Checklist de entrega](CHECKLIST_ENTREGA_V1.md) e [Relatório de liberação](RELATORIO_LIBERACAO_V1.md).

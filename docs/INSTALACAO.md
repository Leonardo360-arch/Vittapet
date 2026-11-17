# Instalação inicial (desenvolvimento)

**Windows + Docker Desktop:** prefira o passo a passo completo em [LOCALHOST_WINDOWS.md](LOCALHOST_WINDOWS.md). O comando assistido prepara PHP 8.2, MySQL 8, phpMyAdmin, migrations e administração sem configurar PHP ou MySQL diretamente no Windows. Este documento também serve para instalação manual/XAMPP.


1. Configure PHP 8 com extensões PDO MySQL e mbstring, MySQL e servidor web.
2. Crie o banco `vittapet_db` com UTF8MB4 e importe, **uma vez e na ordem**, todos os arquivos `database/migrations/001_*.sql` até `009_*.sql`. Não reexecutar indiscriminadamente migrations com `ALTER TABLE` em banco já migrado.
3. Copie `config/local.example.php` para `config/local.php` e configure credenciais **localmente**. Não commitar `local.php`.
4. Defina variáveis de ambiente locais `ADMIN_EMAIL`, `ADMIN_NAME` e `ADMIN_PASSWORD` (senha com no mínimo 12 caracteres, sem expor no Git ou histórico do terminal) e execute `php scripts/create_admin.php` para provisionar o login. Em ambiente de hospedagem sem CLI, faça o provisionamento em banco de teste antes de exportar o hash e nunca suba o script para `htdocs`.
5. Em desenvolvimento, execute `php -S localhost:8000 -t public` a partir da raiz do projeto.
6. Acesse `/login.php` para administrar produtos e `/index.php` para visualizar o catálogo.

**Hospedagem InfinityFree:** consulte [`deploy/INFINITYFREE.md`](../deploy/INFINITYFREE.md) antes de fazer upload. Apenas arquivos necessários devem entrar em `htdocs` e os bloqueios do `.htaccess` devem ser testados.

**Estado verificado:** código em `main` foi testado no CI com PHP 8.2/MySQL 8 (78 asserções), duas conexões concorrentes e Chromium emulado (15 testes). Há proteção persistente contra tentativas de login e rastreio. ViaCEP e Nominatim foram simulados nos testes de navegador, **não validados em rede real**. Frete e prefixos CEP devem ser configurados; não aceitar vendas reais antes de homologar região e operação. O InfinityFree permanece sem implantação.

## Configuração do mapa
No painel administrativo acesse `admin/configuracoes.php`, preencha endereço, clique em **Localizar endereço no mapa**, confira as coordenadas retornadas pela API JSON e salve. Na homepage, o mapa Leaflet aparecerá depois que latitude e longitude forem salvas; sem coordenadas, o endereço informativo permanece e nenhum ponto fictício é exibido.

## WhatsApp Pix
Na administração abra Configurações e informe o WhatsApp comercial no formato `55 + DDD + número`. O checkout Pix gera o pedido e o botão da tela de rastreio abre conversa com mensagem pré-preenchida; o cliente precisa enviar a mensagem. Conferência de pagamento é manual. Consulte [Pagamentos v1](PAGAMENTOS_V1.md).

## Segurança e controle de acesso
A migration 006 é obrigatória para login e rastreio: cria tabelas de tentativas e controle de consultas. Após instalar, teste login com hash gerado localmente, bloqueio de tentativas e logout por POST. Execute os testes da [Revisão QA 05](QA_REVISAO_05.md). Sem a migration 006, login e rastreio não funcionarão corretamente.

## Integração automatizada
A suíte `tests/integration_http.php` utiliza MySQL 8 isolado e servidor `php -S` acionados por `.github/workflows/php-quality.yml` em Pull Requests. Usa exclusivamente credenciais e produtos fictícios no CI; consulte [matriz de testes](MATRIZ_TESTES_V1.md). **Os cenários Playwright simulam APIs externas, sem comprovar disponibilidade/CORS em rede real. Não testa InfinityFree.**

## Frete da loja
A partir da migration 007, o checkout exige taxa de frete configurada pelo administrador em `admin/configuracoes.php`. Vazio bloqueia a compra; `0.00` é frete grátis explícito. O backend também verifica prefixos de CEP autorizados pela migration 008; não confirma se o endereço informado existe. Consulte [Frete v1](FRETE_V1.md).

## Cobertura de entrega
Após importar 008, configure no painel a taxa fixa e os prefixos de CEP com cinco dígitos autorizados, como documentado em [Cobertura](COBERTURA_ENTREGA_V1.md). Não há lista padrão de bairros e não é permitido aceitar pedidos enquanto a configuração estiver vazia.

## Estorno de pagamento
Migration 009 registra data de recebimento e de estorno. O botão administrativo só **registra estorno já feito externamente**, limitado a pedidos não despachados. Consulte [Estornos financeiros](ESTORNOS_FINANCEIRO_V1.md).

# Instalação inicial (desenvolvimento)

1. Configure PHP 8 com extensões PDO MySQL e mbstring, MySQL e servidor web.
2. Crie o banco `vittapet_db` com UTF8MB4 e importe `database/migrations/001_store_base.sql` e, em seguida, `database/migrations/002_pedidos.sql` e `database/migrations/003_financeiro.sql`, depois `database/migrations/004_localizacao_loja.sql`.
3. Copie `config/local.example.php` para `config/local.php` e configure credenciais **localmente**. Não commitar `local.php`.
4. Crie o usuário administrador **por procedimento local seguro**, gerando hash com `php -r "echo password_hash('SENHA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"` e inserindo o hash no campo `senha_hash` com SQL parametrizado ou ferramenta local. Não versionar senha.
5. Em desenvolvimento, execute `php -S localhost:8000 -t public` a partir da raiz do projeto.
6. Acesse `/login.php` para administrar produtos e `/index.php` para visualizar o catálogo.

**Hospedagem InfinityFree:** consulte [`deploy/INFINITYFREE.md`](../deploy/INFINITYFREE.md) antes de fazer upload. Apenas arquivos necessários devem entrar em `htdocs` e os bloqueios do `.htaccess` devem ser testados.

**Observações:** CRUD, carrinho, checkout inicial, tabelas de pedidos e painel de acompanhamento estão no código, ainda **sem validação em ambiente executável**. O checkout usa frete demonstrativo zero e pagamento manual; não utilizá-lo para vendas reais. Dashboard financeiro, geocodificação/mapa, testes de implantação e proteção contra abuso continuam pendentes. ViaCEP está implementado no formulário, mas ainda sem testes no navegador. O login ainda não tem proteção completa contra força bruta; configurar rate-limit antes de implantação pública. A URL e o webroot do InfinityFree exigem teste específico para manter `config/` e `src/` não acessíveis.

## Configuração do mapa
No painel administrativo acesse `admin/configuracoes.php`, preencha endereço, clique em **Localizar endereço no mapa**, confira as coordenadas retornadas pela API JSON e salve. Na homepage, o mapa Leaflet aparecerá depois que latitude e longitude forem salvas; sem coordenadas, o endereço informativo permanece e nenhum ponto fictício é exibido.

# Instalação inicial (desenvolvimento)

1. Configure PHP 8 com extensões PDO MySQL e mbstring, MySQL e servidor web.
2. Crie o banco `vittapet_db` com UTF8MB4 e importe `database/migrations/001_store_base.sql`.
3. Copie `config/local.example.php` para `config/local.php` e configure credenciais **localmente**. Não commitar `local.php`.
4. Crie o usuário administrador **por procedimento local seguro**, gerando hash com `php -r "echo password_hash('SENHA_FORTE', PASSWORD_DEFAULT), PHP_EOL;"` e inserindo o hash no campo `senha_hash` com SQL parametrizado ou ferramenta local. Não versionar senha.
5. Em desenvolvimento, execute `php -S localhost:8000 -t public` a partir da raiz do projeto.
6. Acesse `/login.php` para administrar produtos e `/index.php` para visualizar o catálogo.

**Observações:** CRUD e carrinho de sessão foram adicionados ao código, ainda sem validação em ambiente executável; checkout, pedidos, dashboard financeiro, duas APIs e mapa continuam pendentes. O login ainda não tem proteção completa contra força bruta; configurar rate-limit antes de implantação pública. A URL e o webroot do InfinityFree exigem teste específico para manter `config/` e `src/` não acessíveis.

# Auditoria de organização Git e pacote de produção — VittaPet 1.0.0-dev.4

**Data da auditoria:** 17/11/2026. **Escopo:** árvore da branch `main` antes da dev.4, configuração de Git e pacote de hospedagem.

## Resultado do inventário
Estrutura `config/`, `includes/`, `public/`, `database/migrations/`, `docs/`, `scripts/`, `tests/`, `deploy/`, `.github/` e arquivos raiz. **Não foram encontrados arquivos temporários obviamente descartáveis** (.zip, .log, .env, .sql.gz, IDE, dumps ou node_modules) na árvore rastreada. Não houve exclusão destrutiva.

## Política de retenção no Git
- **MANTER:** `database/migrations/` (reconstrução do esquema), `tests/` (regressão), `docs/` (evidências acadêmicas), `scripts/` (operações locais), `.github/workflows/` (quality gate), `deploy/INFINITYFREE.md` (manual), `README.md`, `CHANGELOG.md`, `VERSION`, `.editorconfig`.
- **MANTER no código de execução:** `public/`, `includes/`, `config/database.php`, `config/.htaccess`, `.htaccess`.
- **NÃO VERSIONAR:** `config/local.php`, `.env`, credenciais reais, dump de banco, arquivos de log, ZIP gerado, caches, arquivos de IDE, `build/`, `dist/`, `coverage/`.
- **NÃO ENVIAR A `htdocs`:** diretórios `.git/`, `.github/`, `docs/`, `database/`, `scripts/`, `tests/`, `deploy/`, `config/local.example.php`, segredos e backups.

## Empacotamento seguro
`php scripts/build_deploy.php` gera **somente** `build/htdocs` com arquivos explicitamente permitidos. A pasta `build/` está ignorada no Git e jamais deve ser comitada. A saída é uma base para upload posterior; o administrador cria `config/local.php` **diretamente no servidor**, sem inserir senha em ZIP ou repositório.

Se já existir `build/htdocs`, o empacotador falha e exige revisão/remoção manual para evitar apagar dados inadvertidamente.

CI precisa confirmar a presença de `public/checkout.php`, `includes/order_inventory.php`, `.htaccess` e ausência de documentos/migrations/testes/segredos no pacote.

## Limitações
A auditoria verificou o estado atual rastreado, **não um scan do histórico completo em todos os commits**, nem dependências que não estão no Git. O empacotamento não publica no InfinityFree e não valida rewrite/HTTPS nesse provedor. Só liberar o upload depois de QA funcional e teste de acesso privado.

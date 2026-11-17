# Implantação no InfinityFree — VittaPet Store

## Topologia

A versão gratuita tem `htdocs` como raiz web fixa e restringe o uso de arquivos fora dela. Por isso, a aplicação inteira pode ser copiada para `htdocs`, **mas o acesso HTTP aos diretórios internos precisa estar bloqueado** com o `.htaccess` da raiz e arquivos `.htaccess` internos. Esta é uma solução de compatibilidade do hosting compartilhado, menos isolada que um servidor dedicado cujo DocumentRoot seja `public/`.

```text
/htdocs/
  .htaccess             # reescrita das URLs e bloqueios
  public/               # PHP das telas, assets, admin
  config/               # database.php e local.php privado
  includes/             # bootstrap, utilitários
  database/             # migrations; preferencialmente NAO subir em producao
  docs/                 # documentação; preferencialmente NAO subir em producao
  deploy/               # orientações; preferencialmente NAO subir em producao
```

**Recomendação:** copiar para `htdocs` somente `.htaccess`, `public/`, `config/`, `includes/` e dependências PHP realmente utilizadas. Não enviar `docs/`, `database/`, `.git/`, testes, dumps, logs, backups ou documentos locais.

## Passo a passo
1. Criar site/conta InfinityFree e habilitar HTTPS/SSL.
2. No painel MySQL Databases, criar o banco e anotar **hostname, usuário e nome do banco exatamente como fornecidos**. Não assumir `localhost`.
3. Importar migrations `001_store_base.sql`, `002_pedidos.sql` e `003_financeiro.sql` nessa ordem, via phpMyAdmin do painel.
4. Criar `config/local.php` a partir de `config/local.example.php` **somente no servidor/local** e preencher credenciais reais. Nunca realizar commit desse arquivo.
5. Criar usuário administrador com senha forte e hash (`password_hash`), sem publicar credenciais no Git.
6. Transferir arquivos permitidos para dentro de `htdocs`, preservando a estrutura. O `.htaccess` oculto da raiz deve ser enviado.
7. Confirmar com URL do navegador: `/` abre loja; `/login.php` abre login; `/admin/produtos.php` redireciona para login sem sessão; `/assets/css/tokens.css` responde CSS.
8. Confirmar que `/config/database.php`, `/config/local.php`, `/includes/bootstrap.php` e `/database/migrations/001_store_base.sql` **não são acessíveis via HTTP** (403/404 esperado). Se retornarem conteúdo, suspender publicação.
9. Testar banco, CRUD, carrinho, checkout, rastreio, relatórios e APIs em navegador real, incluindo celular.
10. Testar SSL e não divulgar link acadêmico antes de passar no checklist de QA.

## Limitações e cuidados
- InfinityFree não oferece cron jobs na hospedagem gratuita; fila de e-mail e agendamento automático ficam para arquitetura futura.
- Serviços externos consultados no frontend (ViaCEP e geocodificação conforme implementação) precisam respeitar CORS e políticas de uso. Nunca pressupor que chamadas externas via PHP estarão livres de restrições.
- Não declarar frete grátis real: checkout atual tem R$ 0,00 **demonstrativo**. Não vender para clientes reais sem política de frete implementada.
- Admin precisa de rate limit de login e o rastreio requer limite de tentativas antes de publicação real.
- Configurar upload de arquivos, permissões, logs, backup e restauração conforme recursos disponíveis; não deixar dumps nem segredos sob webroot público.
- A instalação e funcionamento no InfinityFree ainda exigem teste com dados da conta. Este documento prepara a implantação, **não significa que ela tenha sido executada**.

## Testes específicos de roteamento
`/index.php`, `/login.php`, `/carrinho.php`, `/checkout.php`, `/rastrear.php`, `/admin/pedidos.php`, `/assets/css/tokens.css`. Não deixar diretórios sensíveis responderem com listagem ou fontes.

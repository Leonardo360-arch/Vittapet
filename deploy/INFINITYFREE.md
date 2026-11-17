# Implantação no InfinityFree — VittaPet Store

## Topologia

A versão gratuita tem `htdocs` como raiz web fixa e restringe o uso de arquivos fora dela. Por isso, a aplicação inteira pode ser copiada para `htdocs`, **mas o acesso HTTP aos diretórios internos precisa estar bloqueado** com o `.htaccess` da raiz e arquivos `.htaccess` internos. Esta é uma solução de compatibilidade do hosting compartilhado, menos isolada que um servidor dedicado cujo DocumentRoot seja `public/`.

```text
/htdocs/
  .htaccess             # reescrita das URLs e bloqueios
  public/               # PHP das telas, assets, admin
  config/               # database.php e local.php privado
  includes/             # bootstrap, utilitários
```

**Empacotamento recomendado (somente após homologação):** executar `php scripts/build_deploy.php` na raiz do projeto e usar a estrutura gerada em `build/htdocs/`. Ela contém apenas `.htaccess`, `public/`, `includes/`, `config/database.php` e `config/.htaccess`; **não contém** `config/local.php` nem documentos/migrations/testes/scripts. Configurar credenciais apenas no servidor, fora do Git e do pacote. Não executar FTP antes dos testes de segurança.

## Passo a passo
1. Criar site/conta InfinityFree e habilitar HTTPS/SSL.
2. No painel MySQL Databases, criar o banco e anotar **hostname, usuário e nome do banco exatamente como fornecidos**. Não assumir `localhost`.
3. Importar migrations **001 a 009**, em ordem numérica e sem repetir etapas, pelo phpMyAdmin do painel. Arquivos SQL permanecem fora de `htdocs`.
4. Criar `config/local.php` a partir de `config/local.example.php` **somente no servidor/local** e preencher credenciais reais. Nunca realizar commit desse arquivo.
5. Provisionar usuário administrador com e-mail e hash de senha forte antes do upload, por procedimento privado/CLI; não enviar `scripts/create_admin.php` para `htdocs` nem publicar credenciais no Git.
6. Transferir arquivos permitidos para dentro de `htdocs`, preservando a estrutura. O `.htaccess` oculto da raiz deve ser enviado.
7. Confirmar com URL do navegador: `/` abre loja; `/login.php` abre login; `/admin/produtos.php` redireciona para login sem sessão; `/assets/css/tokens.css` responde CSS.
8. Confirmar que `/config/database.php`, `/config/local.php`, `/includes/bootstrap.php` **não são acessíveis via HTTP** (403/404 esperado). O caminho `/database/` nem deve existir no pacote. Se qualquer URL privada revelar conteúdo, suspender a publicação.
9. Testar banco, CRUD, carrinho, checkout, rastreio, relatórios e APIs em navegador real, incluindo celular.
10. Testar SSL e não divulgar link acadêmico antes de passar no checklist de QA.

## Limitações e cuidados
- InfinityFree não oferece cron jobs na hospedagem gratuita; fila de e-mail e agendamento automático ficam para arquitetura futura.
- Serviços externos consultados no frontend (ViaCEP e geocodificação conforme implementação) precisam respeitar CORS e políticas de uso. Nunca pressupor que chamadas externas via PHP estarão livres de restrições.
- O checkout exige frete fixo explícito e cobertura por CEP; definir área real de atendimento e validar casos excepcionais antes da venda.
- Login e rastreio têm limitação persistente desde a migration 006; testar os limites no host e definir retenção de logs de tentativa.
- Configurar upload de arquivos, permissões, logs, backup e restauração conforme recursos disponíveis; não deixar dumps nem segredos sob webroot público.
- A instalação e funcionamento no InfinityFree ainda exigem teste com dados da conta. Este documento prepara a implantação, **não significa que ela tenha sido executada**.

## Testes específicos de roteamento
`/index.php`, `/login.php`, `/carrinho.php`, `/checkout.php`, `/rastrear.php`, `/admin/pedidos.php`, `/assets/css/tokens.css`. Não deixar diretórios sensíveis responderem com listagem ou fontes.

## Proteção de login e rastreio
A migration 006 cria contadores persistentes para diminuir tentativas automatizadas. Verifique respostas 429 e evite apagar indiscriminadamente os registros ativos. Planeje limpeza de registros com mais de 7 dias via manutenção manual ou rotina oportunística compatível com hospedagem gratuita.

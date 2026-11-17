# VittaPet Store — Teste em localhost (Windows + Docker Desktop)

**Versão:** 1.0.0-dev.7 — ambiente de desenvolvimento. **Não é hospedagem pública nem versão comercial homologada.**

## 1. Pré-requisitos
- Windows 10/11, Docker Desktop **iniciado** (engine Linux/WSL2) e Git.
- PowerShell 5.1 ou superior. Não é necessário instalar PHP ou MySQL no Windows: ambos estão nos containers.
- Portas locais **8080 e 8081 livres**. Se estiverem ocupadas, altere apenas a parte esquerda do mapeamento em `compose.local.yaml`.
- Conexão com a internet na primeira execução para baixar as imagens do Docker e, no navegador, para CDN Bootstrap/Leaflet e APIs externas.

## 2. Clonar ou atualizar
Abra o terminal no Windows:
```powershell
git clone https://github.com/Leonardo360-arch/Vittapet.git
cd Vittapet
```
Se já tem a pasta clonada, em vez do clone:
```powershell
git switch main
git pull --ff-only origin main
```
**Não apague arquivos locais nem rode `git reset --hard`.** Confira `git status` antes de atualizar; se tiver alterações suas, preserve-as.

## 3. Primeira execução: script assistido
No terminal PowerShell, dentro da raiz do repositório:
```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\setup_local.ps1
```
O assistente:
1. Confere Docker Desktop e existência de `config/local.php` (configuração tradicional que pode sobrescrever a do Docker).
2. Executa `docker compose -f compose.local.yaml up -d --build`.
3. MySQL cria **banco, categorias e migrations 001–009 automaticamente, apenas no volume vazio**.
4. Solicita **e-mail e senha do administrador** no terminal. A senha digitada não é armazenada no repositório e é enviada ao PHP por variável temporária de ambiente.
5. Mostra URLs de acesso.

Se o Windows bloquear o script, use o comando acima com `-ExecutionPolicy Bypass` **somente para esse processo**. Não altere a política global do Windows.

## 4. URLs locais
| Serviço | URL | Observação |
|---|---|---|
| Loja | http://localhost:8080/ | Produtos, carrinho, checkout |
| Login | http://localhost:8080/login.php | E-mail e senha cadastrados pelo assistente |
| Configurações | http://localhost:8080/admin/configuracoes.php | Requer login |
| Pedidos | http://localhost:8080/admin/pedidos.php | Requer login |
| phpMyAdmin | http://localhost:8081/ | Usuário `vittapet_local`, senha **somente de teste** `vittapet_dev_only`; banco `vittapet_db` |

**Cuidado:** os valores `vittapet_dev_only` / `root_dev_only` são credenciais **deliberadamente públicas para desenvolvimento local**, não use essas senhas no InfinityFree, serviços reais nem máquinas compartilhadas. As portas ficam ligadas a `127.0.0.1`.

## 5. Configurar a loja para permitir checkout
Ao iniciar, as categorias já existirão, **mas você precisará cadastrar produtos manualmente**, com preço e estoque. No painel:
1. Em Produtos, cadastre pelo menos um item ativo.
2. Em Configurações, informe **WhatsApp comercial autorizado** no formato `55 + DDD + número`. O teste de Pix prepara o link para o WhatsApp, mas **não envia mensagens sozinho**.
3. Configure **frete fixo** (exemplo: `8.50`; `0.00` é frete grátis proposital).
4. Configure **prefixos de CEP atendidos** com 5 dígitos separados por vírgula. Exemplo somente para teste: `01001`, que inclui `01001-000`. A configuração **não valida se o endereço existe**.
5. Cadastre endereço da loja; clique em localizar com Nominatim e salve coordenadas para exibir mapa.
6. Para visualizar um catálogo inicial, pode executar **opcionalmente** `docker compose -f compose.local.yaml exec -T app php scripts/seed_local.php`. Ele cria quatro produtos fictícios marcados DEMO somente se nenhum produto existir; não altera cadastros existentes.
7. Teste compra sem usar dados pessoais reais, rastreio, Pix via WhatsApp, pagamento na entrega e atualização de status.

Se frete ou cobertura estiverem vazios, o servidor impede novos pedidos — isso é esperado.

## 6. Diagnóstico sem modificar dados
```powershell
docker compose -f compose.local.yaml exec -T app php scripts/check_local.php
```
O diagnóstico verifica conexão MySQL, principais tabelas e colunas das migrations, quantidade de admins e produtos ativos e situação de frete/CEP/WhatsApp, sem imprimir senha ou dados dos clientes.

Para ver logs:
```powershell
docker compose -f compose.local.yaml logs --tail=100 app db
```

## 7. Iniciar, parar e preservar dados
```powershell
docker compose -f compose.local.yaml up -d
docker compose -f compose.local.yaml stop
docker compose -f compose.local.yaml start
```
O volume `db_local` mantém produtos, contas e pedidos quando os containers são parados/recriados. **Importação automática das migrations ocorre somente na criação inicial do volume.** Se atualizar o Git e surgirem migrations novas, faça backup do banco e aplique apenas as novas migrations conforme a documentação; não reinicialize o banco para tentar atualizá-lo.

**Atenção: `docker compose down -v` APAGA IRREVERSIVELMENTE o banco local do projeto.** Não execute para atualizar código ou corrigir erro sem backup.

## 8. Alternativa sem Docker (PHP/MySQL já instalados)
É possível usar XAMPP/servidor PHP existente, sem alterar os arquivos de produção:
- Criar banco `vittapet_db` no MySQL local e importar 001–009 **em ordem, uma vez**.
- Copiar `config/local.example.php` para `config/local.php` e ajustar `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, **somente no PC**.
- Provisionar administrador com `php scripts/create_admin.php`, fornecendo `ADMIN_EMAIL`, `ADMIN_NAME`, `ADMIN_PASSWORD` no ambiente local.
- Executar `php -S 127.0.0.1:8080 -t public` e abrir http://localhost:8080/.

Não rode simultaneamente PHP/XAMPP e Docker na mesma porta; escolha um método. `config/local.php` tem prioridade sobre variáveis `DB_*` do Docker: não misturar os dois métodos.

## 9. Escopo de segurança e hospedagem
Este ambiente é **somente local** e usa senhas de banco de teste intencionais. Não publicar as portas nem enviá-lo como se fosse ambiente de produção. O pacote de InfinityFree continuará sendo gerado separadamente por `scripts/build_deploy.php`, sem Docker, MySQL local, dumps ou scripts de teste. A implantação pública permanece a última etapa após homologação.

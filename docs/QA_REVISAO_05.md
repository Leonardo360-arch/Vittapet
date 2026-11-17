# QA — Revisão 05: endurecimento de segurança da VittaPet Store

Data: 17/11/2026. Branch de revisão: `fix/qa-seguranca-v1`.

## Implementação
- `database/migrations/006_login_tentativas.sql`: tabela de tentativas administrativas e consultas de rastreio, com identificadores e endereços IP representados por SHA-256, sem valores em texto claro.
- `public/login.php`: limite de 8 falhas por par IP/e-mail e 30 falhas por IP em 15 minutos, bloqueio HTTP 429, consulta da senha usando `password_verify`, sessão regenerada no sucesso.
- `includes/bootstrap.php`: autorização administrativa checada no banco em cada requisição e expiração após 60 minutos sem atividade.
- `public/logout.php`: somente POST com token CSRF, encerramento da sessão e expiração do cookie.
- `public/rastrear.php`: limite de 40 consultas em 10 minutos por IP usando tabela persistente; erros e indisponibilidade não expõem detalhes SQL.
- `.github/workflows/php-quality.yml`: lint automático `php -l` em arquivos PHP, conferência da estrutura mínima e ausência de segredos de configuração no repositório.
- Navegação de saída alterada para formulários POST em telas administrativas.

## Resultado da revisão
Inspeção estática do código feita. **Testes de integração ainda não executados.** A disponibilidade do workflow depende dos GitHub Actions do repositório. Sem credenciais do InfinityFree e sem MySQL de teste configurado, não foi possível validar os cenários HTTP reais.

## Checklist obrigatório para aprovação final
- [ ] Migration 006 importada após migrations 001 a 005.
- [ ] PHP CLI (versão compatível) executa lint sem erros em todos os arquivos PHP.
- [ ] Login válido com senha hash redireciona ao dashboard; inválido mantém acesso negado.
- [ ] Oitava falha de login bloqueia novas tentativas; resposta 429 após atingir o limite.
- [ ] Usuário desativado ou perfil alterado perde o acesso administrativo imediatamente.
- [ ] Sessão expira após período de inatividade e novo acesso exige login.
- [ ] Logout com GET retorna 405; logout com POST+CSRF encerra sessão.
- [ ] Rastreio inválido retorna resposta genérica; após limite retorna 429.
- [ ] Checkout e pedidos continuam funcionando após alteração de sessões.
- [ ] URLs de `config/`, `includes/` e migrations não são servidas pelo InfinityFree.
- [ ] Nenhum arquivo real `config/local.php` nem `.env` aparece versionado.
- [ ] ViaCEP e geolocalização retornam JSON real no domínio publicado.
- [ ] Testes no celular e em desktop, incluindo rota do WhatsApp Pix.

## Riscos ainda abertos
1. **Alto:** nenhum teste end-to-end PHP/MySQL/InfinityFree; não considerar a versão pronta para clientes reais.
2. **Alto:** frete está em R$ 0,00 demonstrativo; não há precificação de entrega final.
3. **Alto:** reembolso/estorno de pedido pago ainda não implementado.
4. **Médio:** IP com SHA-256 é um identificador pseudonimizado, ainda sujeito a regras de privacidade e retenção. Definir retenção de até 7 dias e rotina de limpeza compatível com a hospedagem; sem cron gratuito, implementar manutenção manual ou limpeza oportunística.
5. **Médio:** contadores por IP podem afetar visitantes atrás de NAT ou internet escolar; ajustar limites com métricas de testes.
6. **Médio:** ausência de limites globais distribuídos e proteção adicional de infraestrutura (WAF, CDN) permanece risco operacional.
7. **Médio:** execução de GitHub Actions e dependências de CDN precisam ser observadas; não marcar como aprovadas sem logs de execução.

## Decisão
**Incremento elegível para revisão de integração como código em desenvolvimento**, não como release. Somente promover à versão 1.0 estável após executar o checklist e corrigir bloqueadores.

# VittaPet

Sistema web para Pet Shop, com evolução planejada para clínica veterinária, loja virtual e Portal do Tutor.

## Entrega acadêmica — Desenvolvimento Web II

**Prazo:** 23/11/2026.

**Escopo do MVP:**
- Interface pública responsiva com catálogo de produtos;
- CRUD de produtos (Create, Read, Update, Delete) com PHP 8 e MySQL;
- Acesso administrativo protegido por sessão;
- Integração de **duas APIs públicas**: ViaCEP e Dog CEO;
- Publicação no InfinityFree;
- Relatório técnico e evidências de teste.

> **Situação:** repositório inicializado. Funcionalidades acima são planejadas, ainda não implementadas. Não considerar a entrega concluída antes de validar CRUD, APIs e hospedagem.

## Arquitetura-alvo

Monólito modular em PHP 8, MySQL 8, PDO, HTML5, CSS3, Bootstrap 5 e JavaScript ES6+. Separação de apresentação, controllers, services e repositories. Apenas `public/` deve ser exposto pelo servidor; ajustes específicos da hospedagem InfinityFree precisam preservar proteção dos arquivos internos.

## Evolução (VittaPet v2.1)

1. Tutores, pets, agenda, estoque e caixa;
2. Consultas, prontuários, vacinas, exames e Portal do Tutor;
3. Carrinho, checkout e rastreio de pedidos;
4. VittaBot, e-mails, VittaPontos, mural e concursos;
5. Testes, privacidade, auditoria e endurecimento da segurança.

O **MVP é a fundação do produto**, não um projeto descartável. Módulos futuros serão incorporados por migrations versionadas e componentes isolados, preservando dados e funcionalidades existentes.

## Estrutura prevista

```text
public/             # Única pasta pública
src/                # Controllers, Services, Repositories
config/             # Configuração privada
includes/           # Componentes compartilhados e segurança
database/migrations # Esquema versionado
database/seeds/     # Dados de demonstração
storage/logs/       # Arquivos privados e logs
tests/              # Testes
docs/               # Documentação do MVP e roadmap
```

## Critérios de aceite (DW2)

- [ ] CRUD completo com persistência MySQL, sem erros de PHP
- [ ] Layout e regras de negócio originais do tema Pet Shop
- [ ] Documentação técnica organizada
- [ ] Integração de duas APIs com tratamento JSON
- [ ] Hospedagem InfinityFree validada por URL pública

## Segurança e colaboração

Não versionar senhas, credenciais, arquivos `.env` ou dados sensíveis. Commits devem ser descritivos; preferir branches de funcionalidade e revisão antes da integração na `main`.

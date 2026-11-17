# Padrão de qualidade e código limpo — VittaPet

**Versão:** 1.0, 17/11/2026. Aplicação gradual: arquivos legados ainda precisam de refatoração e formatação antes de receber aprovação final.

## 1. Padrões aplicáveis
- PHP 8.2: PSR-12 como alvo de formatação; PHPStan/PHP_CodeSniffer opcionais quando o ambiente permitir, sem instalar dependências de runtime incompatíveis com InfinityFree.
- HTML: semântica, foco visível, formulários com label e mensagens de erro; WCAG 2.1 AA como referência.
- JavaScript: ES6, módulos específicos por página, nomes descritivos, operações de rede com tratamento de erro; evitar código minificado/manual em linhas únicas.
- SQL: migrations imutáveis e numeradas; InnoDB, FKs, prepared statements; DECIMAL para valores monetários.
- `.editorconfig` padroniza UTF-8, LF, indentação e newline final; revisar no editor/PR.

## 2. SOLID: aplicar apenas quando resolver problema real
- **SRP:** extrair do PHP misturado a regra de cálculo do pedido, atualização logística e autorização.
- **OCP:** adicionar novos meios de pagamento por estratégia só se o número de provedores e variações justificar; não criar interface para uma única ramificação do MVP.
- **LSP/ISP:** não criar interfaces ou hierarquia de classes sem comportamento substituível real.
- **DIP:** injetar dependência de repositório/DB em serviços ao separar lógica; PDO parametrizado continua fonte de persistência.
- **Repository/Service:** recomendados para checkout/estoque/pagamento e relatórios quando houver mais regras, testes ou reuso; refatorar com testes verdes, sem reescrever todo o sistema às vésperas de entregar.

## 3. Convenções
- Funções e variáveis descritivas em `camelCase`, classes em `PascalCase`, tabelas em `snake_case`.
- Comentários explicam *por que* há regra ou risco, não repetem código autoexplicativo.
- Erro para usuário sem stacktrace, detalhes completos apenas em log privado (sem senha/CPF/dados de pagamento).
- Formatação antes do PR, revisão por pares e nenhum código morto/comentado.
- Comparar `main` com branch e integrar por PR sem `force push`.
- Nunca commitar senha, telefone de cliente real, chave Pix, backup ou credenciais de teste externas.

## 4. Dívidas técnicas verificadas
A versão atualmente implementada contém rotas PHP monolíticas, SQL embutido nas views e trechos de JavaScript em templates. Isso não impede testar o MVP, mas impede afirmar que todo código já atende integralmente a separação de responsabilidades. Priorizar:
1. Proteção de estoque/pagamento e testes P0.
2. Extrair camada de cálculo monetário com valores em centavos ou decimal com precisão estável, e reduzir duplicações.
3. Centralizar estados e transições em serviço com testes.
4. Separar templates de ações HTTP, aplicar PSR-12, depois adicionar análise estática ao gate.
5. Remover ajustes CSS inline e melhorar componentes de carrinho/checkout responsivos.

## 5. Critérios mínimos de revisão
- [ ] `php -l` em todos os arquivos PHP
- [ ] CI MySQL/HTTP verde, demonstrando operações transacionais
- [ ] Código novo formatado e indentado conforme `.editorconfig`
- [ ] Autorização e CSRF em toda ação de escrita
- [ ] Nenhum pagamento pendente reconhecido como receita
- [ ] Nenhum segredo no commit e nenhuma rota privada acessível
- [ ] Documentação atualizada com comportamento real
- [ ] Todo P0 da matriz resolvido antes de emitir o selo metafórico

**Esta política é critério técnico a cumprir, não evidência de conformidade já concluída.**

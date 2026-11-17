# VittaPet Ecosystem — Backlog v2.0 (não implementado)

**A v2.0 será evolução da mesma base da VittaPet Store v1.0**, e não novo projeto. Esta lista organiza as ideias futuras; a referência detalhada permanece no documento original VittaPet v2.1, que inclui as regras RN01–RN20, RF01–RF18, segurança e modelagem originalmente planejadas.

## Domínios novos
1. **Tutores e pets:** perfis, múltiplos pets, consentimentos e históricos.
2. **Banho e tosa:** serviços, portes, profissionais, recursos e agenda sem conflito.
3. **Clínica veterinária:** prontuário, consultas, adendos, receitas, exames, vacinas, QR de verificação e responsabilidades profissionais.
4. **Portal do Tutor:** acompanhamento clínico restrito a pets do titular, documentos e comunicação.
5. **VittaBot:** atendimento, triagem não diagnóstica, encaminhamento humano e fluxos de segurança.
6. **Relacionamento:** lembretes e e-mails com consentimento e descadastro.
7. **VittaPontos:** créditos, validade, estornos, extrato e campanhas.
8. **Comunidade:** mural moderado, concursos e regras antifraude.
9. **Novas APIs:** somente após justificar utilidade, custo, privacidade e manutenção.

## Pareceres setoriais simulados
Gestão de pet shop: operação eficiente; veterinário: prontuário legível e auditável; banho e tosa: agendas por duração/recurso; varejo pet: categorias/estoque; experiência do tutor: navegação simples; mercado: diferenciação; atendimento: contato acessível; conformidade: privacidade e controles legais. Trata-se de papéis virtuais de consultoria, não profissionais contratados.

## Migração e critérios
Preservar `usuarios`, `produtos`, `pedidos` e saldos históricos; adicionar tabelas por migrations; autenticação de novos perfis com autorização por titular; testar regressão de carrinho/checkout/financeiro; avaliar 2FA, auditoria e retenção clínica conforme regras aplicáveis. Diferenciar escopo planejado de implementação e só declarar versão concluída com testes e implantação.

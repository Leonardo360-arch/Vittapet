-- VittaPet 1.0.0-dev.2 — cobertura regional controlada por prefixos de CEP.
-- Executar após migration 007. NULL / vazio não autoriza qualquer CEP.
ALTER TABLE configuracoes_loja ADD COLUMN cep_prefixos_entrega VARCHAR(500) NULL DEFAULT NULL;

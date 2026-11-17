-- Migration 007: configuração de frete fixo controlada pelo administrador.
-- NULL indica frete NÃO configurado; zero é frete grátis deliberadamente cadastrado.
ALTER TABLE configuracoes_loja ADD COLUMN frete_fixo DECIMAL(10,2) NULL DEFAULT NULL;

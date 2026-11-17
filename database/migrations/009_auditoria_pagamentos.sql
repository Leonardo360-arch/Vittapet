-- Migration 009 — auditoria temporal de pagamentos e estornos manuais
-- Não atribuir data fictícia a pagamentos antigos: confirmados legados terão pago_em NULL
-- até reconciliação administrativa. Dashboard sinaliza esse limite.
ALTER TABLE pedidos
  ADD COLUMN pago_em DATETIME NULL,
  ADD COLUMN estornado_em DATETIME NULL,
  ADD COLUMN observacao_estorno VARCHAR(500) NULL;
CREATE INDEX idx_pedidos_pago_em ON pedidos (status_pagamento, pago_em);

-- Migration incremental 005 — fluxo de pagamento Pix/WhatsApp e cartao na entrega.
-- Executar somente após as migrations 001 a 004.
-- Preserva cartao_entrega para pedidos antigos; novas compras usam debito_entrega ou credito_entrega.
ALTER TABLE pedidos MODIFY COLUMN metodo_pagamento
 ENUM('pix_manual','dinheiro_entrega','cartao_entrega','debito_entrega','credito_entrega') NOT NULL;
ALTER TABLE pedidos MODIFY COLUMN status_pagamento
 ENUM('pendente','confirmado','estornado','a_receber_na_entrega') NOT NULL DEFAULT 'pendente';
ALTER TABLE configuracoes_loja ADD COLUMN whatsapp VARCHAR(16) NULL;

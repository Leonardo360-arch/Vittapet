-- VittaPet Store v1 — configuração de localização pública da loja.
-- Aplicar após 001, 002 e 003. Não inserir endereço fictício como endereço real.
CREATE TABLE IF NOT EXISTS configuracoes_loja (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  nome VARCHAR(120) NOT NULL DEFAULT 'VittaPet Store',
  cep CHAR(8) NULL,
  logradouro VARCHAR(190) NULL,
  numero VARCHAR(20) NULL,
  complemento VARCHAR(100) NULL,
  bairro VARCHAR(120) NULL,
  cidade VARCHAR(120) NULL,
  uf CHAR(2) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  geocodificado_em DATETIME NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_config_id CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracoes_loja(id,nome) VALUES(1,'VittaPet Store');

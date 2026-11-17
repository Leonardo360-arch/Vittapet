-- Importar apos 001_store_base.sql e 002_pedidos.sql.
CREATE TABLE IF NOT EXISTS despesas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 descricao VARCHAR(190) NOT NULL,
 categoria VARCHAR(80) NOT NULL,
 valor DECIMAL(10,2) NOT NULL,
 data_despesa DATE NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT chk_despesas_valor CHECK (valor > 0),
 INDEX idx_despesas_data (data_despesa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

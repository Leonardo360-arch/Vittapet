-- Migration 006: tentativas de autenticação administrativas.
-- Executar após 001 a 005. Não armazenar email/IP em texto claro.
CREATE TABLE IF NOT EXISTS login_tentativas (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 identificador_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_login_ip_tempo (ip_hash, criado_em),
 INDEX idx_login_par_tempo (ip_hash, identificador_hash, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

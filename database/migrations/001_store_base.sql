CREATE TABLE IF NOT EXISTS usuarios (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 senha_hash VARCHAR(255) NOT NULL,
 perfil ENUM('admin','tutor','veterinario') NOT NULL DEFAULT 'admin',
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL UNIQUE,
 descricao VARCHAR(255) NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS produtos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 categoria_id BIGINT UNSIGNED NOT NULL,
 nome VARCHAR(180) NOT NULL,
 descricao TEXT NULL,
 preco_venda DECIMAL(10,2) NOT NULL,
 custo_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
 estoque_atual INT UNSIGNED NOT NULL DEFAULT 0,
 estoque_minimo INT UNSIGNED NOT NULL DEFAULT 5,
 imagem_path VARCHAR(500) NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_produtos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
 INDEX idx_produtos_catalogo (ativo, categoria_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (nome, descricao) VALUES
('Rações','Alimentação para cães e gatos'),
('Brinquedos','Diversão e enriquecimento ambiental'),
('Acessórios','Coleiras, guias e utilidades'),
('Higiene','Cuidados e limpeza');

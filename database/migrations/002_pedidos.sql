-- Executar depois de 001_store_base.sql.
-- Valores monetários DECIMAL; status financeiro separado de logística.
CREATE TABLE IF NOT EXISTS clientes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL,
 telefone VARCHAR(30) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_clientes_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedidos (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 cliente_id BIGINT UNSIGNED NOT NULL,
 token_rastreio VARCHAR(32) NOT NULL UNIQUE,
 endereco_cep CHAR(8) NOT NULL,
 endereco_logradouro VARCHAR(190) NOT NULL,
 endereco_numero VARCHAR(20) NOT NULL,
 endereco_complemento VARCHAR(100) NULL,
 endereco_bairro VARCHAR(120) NOT NULL,
 endereco_cidade VARCHAR(120) NOT NULL,
 endereco_uf CHAR(2) NOT NULL,
 subtotal DECIMAL(10,2) NOT NULL,
 frete DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 desconto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 total DECIMAL(10,2) NOT NULL,
 metodo_pagamento ENUM('pix_manual','dinheiro_entrega','cartao_entrega') NOT NULL,
 status_pagamento ENUM('pendente','confirmado','estornado') NOT NULL DEFAULT 'pendente',
 status_logistico ENUM('aguardando_pagamento','em_separacao','embalado','saiu_para_entrega','entregue','cancelado') NOT NULL DEFAULT 'aguardando_pagamento',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_pedidos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
 INDEX idx_pedidos_data (created_at),
 INDEX idx_pedidos_status (status_logistico,status_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_itens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pedido_id BIGINT UNSIGNED NOT NULL,
 produto_id BIGINT UNSIGNED NOT NULL,
 quantidade INT UNSIGNED NOT NULL,
 preco_unitario DECIMAL(10,2) NOT NULL,
 custo_unitario DECIMAL(10,2) NOT NULL,
 CONSTRAINT fk_itens_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
 CONSTRAINT fk_itens_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
 INDEX idx_itens_pedido (pedido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pedido_historico (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 pedido_id BIGINT UNSIGNED NOT NULL,
 status_anterior VARCHAR(32) NULL,
 status_novo VARCHAR(32) NOT NULL,
 usuario_id BIGINT UNSIGNED NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_historico_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
 CONSTRAINT fk_historico_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacoes_estoque (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 produto_id BIGINT UNSIGNED NOT NULL,
 pedido_id BIGINT UNSIGNED NULL,
 tipo ENUM('saida_venda','estorno_venda','ajuste') NOT NULL,
 quantidade INT NOT NULL,
 criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_mov_produto FOREIGN KEY (produto_id) REFERENCES produtos(id),
 CONSTRAINT fk_mov_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

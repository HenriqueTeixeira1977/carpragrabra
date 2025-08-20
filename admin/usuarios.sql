-- ========================================
-- BANCO DE DADOS PARA USUÁRIOS DO SISTEMA
-- ========================================

-- Criar banco (se ainda não existir)
CREATE DATABASE IF NOT EXISTS sistema_usuarios
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE sistema_usuarios;

-- ========================================
-- TABELA DE USUÁRIOS
-- ========================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    usuario VARCHAR(150) NOT NULL UNIQUE, -- e-mail usado para login
    senha VARCHAR(255) NOT NULL, -- armazenar com password_hash()
    funcao ENUM('Administrador', 'Gerente', 'Vendedor') NOT NULL DEFAULT 'Vendedor',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- USUÁRIO PADRÃO (senha: admin123)
-- ========================================
INSERT INTO usuarios (nome, usuario, senha, funcao)
VALUES (
    'Administrador Padrão',
    'admin@sistema.com',
    '$2y$10$9mIPV5Hpi1zX6YjCVw4axOd6UPlZ7N6cCB9XZxUu8QheZfW72QKry', -- hash de 'admin123'
    'Administrador'
);

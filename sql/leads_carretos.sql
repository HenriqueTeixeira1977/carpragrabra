-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS carretos_db
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados
USE carretos_db;

-- Criação da tabela leads_carretos
CREATE TABLE IF NOT EXISTS leads_carretos (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(120) NOT NULL,
  whatsapp   VARCHAR(30)  NOT NULL,
  email      VARCHAR(120) NOT NULL,
  origem     VARCHAR(120) NOT NULL,
  destino    VARCHAR(120) NOT NULL,
  mensagem   TEXT,
  data_envio DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

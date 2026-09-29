CREATE DATABASE IF NOT EXISTS syntax_labs
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE syntax_labs;

-- Usuários que acessam a área administrativa
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    senha_hash    VARCHAR(255) NOT NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

select * from usuarios; 

-- Tokens de recuperação de senha
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED NOT NULL,
    token_hash  CHAR(64)     NOT NULL,
    expira_em   DATETIME     NOT NULL,
    usado       TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_token_hash (token_hash),
    INDEX idx_usuario (usuario_id),

    CONSTRAINT fk_reset_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

select * from password_resets; 

CREATE TABLE IF NOT EXISTS tentativas (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo      VARCHAR(20) NOT NULL,
    chave     CHAR(64)    NOT NULL,
    criado_em DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_busca (tipo, chave, criado_em)
) ENGINE=InnoDB;

select * from tentativas;

DELETE FROM tentativas WHERE id > 0;

SELECT id, usuario_id, LEFT(token_hash, 10) AS hash, expira_em, usado FROM password_resets;
SELECT id, LEFT(token_hash, 10) AS hash, expira_em, usado, NOW() AS agora
  FROM password_resets ORDER BY id DESC;
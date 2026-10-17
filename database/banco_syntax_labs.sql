-- =====================================================
-- Banco de Dados: Syntax Labs
-- =====================================================

CREATE DATABASE IF NOT EXISTS syntax_labs
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE syntax_labs;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    senha_hash    VARCHAR(255) NOT NULL,
    ativo         TINYINT(1)   NOT NULL DEFAULT 1,
    criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Tokens de recuperação de senha
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Controle de tentativas (limite de login, recuperação, contato etc.)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tentativas (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo      VARCHAR(40) NOT NULL,
    chave     CHAR(64)    NOT NULL,
    criado_em DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_busca (tipo, chave, criado_em)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Mensagens do formulário "Fale Conosco"
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS mensagens (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome          VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL,
    empresa       VARCHAR(100)  NULL,
    telefone      VARCHAR(20)   NULL,
    assunto       VARCHAR(150)  NOT NULL,
    mensagem      TEXT          NOT NULL,
    lida          TINYINT(1)    NOT NULL DEFAULT 0,
    arquivada     TINYINT(1)    NOT NULL DEFAULT 0,
    arquivada_em  DATETIME      NULL,
    lida_em       DATETIME      NULL,
    criado_em     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lida_criado (lida, criado_em)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Respostas enviadas pelos administradores
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS mensagens_respostas (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mensagem_id   INT UNSIGNED  NOT NULL,
    usuario_id    INT UNSIGNED  NULL,
    resposta      TEXT          NOT NULL,
    enviada_em    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mensagem (mensagem_id),
    CONSTRAINT fk_resp_mensagem FOREIGN KEY (mensagem_id)
        REFERENCES mensagens(id) ON DELETE CASCADE,
    CONSTRAINT fk_resp_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Orçamentos
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS orcamentos (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    status             ENUM('novo','editando','enviado','aceito','rejeitado') NOT NULL DEFAULT 'novo',

    -- Pedido do cliente
    nome               VARCHAR(100)  NOT NULL,
    empresa            VARCHAR(100)  NOT NULL,
    email              VARCHAR(150)  NOT NULL,
    telefone           VARCHAR(20)   NOT NULL,
    tipos              VARCHAR(500)  NOT NULL,
    objetivos          VARCHAR(500)  NOT NULL,
    descricao          TEXT          NOT NULL,
    possui             VARCHAR(100)  NULL,
    referencias        VARCHAR(1000) NULL,
    faixa              VARCHAR(50)   NULL,
    inicio             VARCHAR(50)   NULL,
    criado_em          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Proposta (preenchida pelo admin)
    prop_titulo        VARCHAR(150)  NULL,
    prop_escopo        TEXT          NULL,
    prop_prazo         VARCHAR(100)  NULL,
    prop_valor_min     DECIMAL(10,2) NULL,
    prop_valor_max     DECIMAL(10,2) NULL,
    prop_pagamento     TEXT          NULL,
    prop_validade      DATE          NULL,
    prop_obs           TEXT          NULL,

    -- Envio e resposta do cliente
    token_hash         CHAR(64)      NULL,
    enviada_em         DATETIME      NULL,
    respondida_em      DATETIME      NULL,
    atualizado_por     INT UNSIGNED  NULL,

    INDEX idx_status_criado (status, criado_em),
    INDEX idx_token (token_hash),
    CONSTRAINT fk_orc_usuario FOREIGN KEY (atualizado_por)
        REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orcamentos_anexos (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    orcamento_id   INT UNSIGNED  NOT NULL,
    nome_original  VARCHAR(255)  NOT NULL,
    nome_arquivo   CHAR(40)      NOT NULL,
    mime           VARCHAR(100)  NOT NULL,
    tamanho        INT UNSIGNED  NOT NULL,
    criado_em      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orc (orcamento_id),
    CONSTRAINT fk_anexo_orc FOREIGN KEY (orcamento_id)
        REFERENCES orcamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB;
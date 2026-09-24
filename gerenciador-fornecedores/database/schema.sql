-- ============================================================
-- Gerenciador de Fornecedores — schema MySQL
-- Importe este arquivo no phpMyAdmin ou via terminal:
--   mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS gerenciador_fornecedores
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gerenciador_fornecedores;

-- Remove as tabelas na ordem inversa das dependências (facilita reimportar)
DROP TABLE IF EXISTS contatos;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS fornecedores;

-- ------------------------------------------------------------
-- Fornecedores (empresas)
-- ------------------------------------------------------------
CREATE TABLE fornecedores (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    razao_social  VARCHAR(150) NOT NULL,
    nome_fantasia VARCHAR(150) NULL,
    cnpj          CHAR(14)     NOT NULL,            -- somente dígitos
    email         VARCHAR(120) NULL,
    telefone      VARCHAR(11)  NULL,                -- somente dígitos (DDD + número)
    cidade        VARCHAR(80)  NULL,
    uf            CHAR(2)      NULL,
    status        ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fornecedores_cnpj (cnpj)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Produtos fornecidos (1 fornecedor : N produtos)
-- ------------------------------------------------------------
CREATE TABLE produtos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT UNSIGNED  NOT NULL,
    nome          VARCHAR(120)  NOT NULL,
    descricao     VARCHAR(255)  NULL,
    unidade       VARCHAR(10)   NOT NULL DEFAULT 'un',
    preco         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_produtos_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Contatos responsáveis (1 fornecedor : N contatos)
-- ------------------------------------------------------------
CREATE TABLE contatos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fornecedor_id INT UNSIGNED NOT NULL,
    nome          VARCHAR(100) NOT NULL,
    cargo         VARCHAR(80)  NULL,
    email         VARCHAR(120) NULL,
    telefone      VARCHAR(11)  NULL,
    principal     TINYINT(1)   NOT NULL DEFAULT 0,
    CONSTRAINT fk_contatos_fornecedor
        FOREIGN KEY (fornecedor_id) REFERENCES fornecedores (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados de exemplo (CNPJs válidos apenas para testes)
-- ------------------------------------------------------------
INSERT INTO fornecedores (razao_social, nome_fantasia, cnpj, email, telefone, cidade, uf, status) VALUES
    ('Distribuidora Boa Safra Ltda', 'Boa Safra',       '11222333000181', 'vendas@boasafra.com.br',    '1133445566', 'São Paulo',      'SP', 'ativo'),
    ('Embalagens Rio Claro S.A.',    'Rio Claro',       '11444777000161', 'contato@rioclaro.com.br',   '1932107788', 'Campinas',       'SP', 'ativo'),
    ('Metalúrgica Horizonte ME',     'Horizonte Metais','12345678000195', 'comercial@horizonte.ind.br','3134567890', 'Belo Horizonte', 'MG', 'inativo');

INSERT INTO produtos (fornecedor_id, nome, descricao, unidade, preco) VALUES
    (1, 'Arroz tipo 1',        'Pacote de 5 kg',                 'pct', 24.90),
    (1, 'Feijão carioca',      'Pacote de 1 kg',                 'pct',  7.85),
    (1, 'Óleo de soja',        'Garrafa de 900 ml',              'un',   6.40),
    (2, 'Caixa de papelão M',  '30 x 20 x 15 cm, onda simples',  'un',   2.35),
    (2, 'Fita adesiva',        'Rolo de 48 mm x 100 m',          'rl',   4.20),
    (3, 'Suporte de aço',      'Suporte reforçado para prateleira', 'un', 18.00);

INSERT INTO contatos (fornecedor_id, nome, cargo, email, telefone, principal) VALUES
    (1, 'Marina Duarte',  'Gerente comercial', 'marina@boasafra.com.br',  '11987654321', 1),
    (1, 'Carlos Menezes', 'Financeiro',        'carlos@boasafra.com.br',  '1133445567',  0),
    (2, 'Paulo Andrade',  'Vendas',            'paulo@rioclaro.com.br',   '19998877665', 1),
    (3, 'Helena Souza',   'Atendimento',       'helena@horizonte.ind.br', '31991234567', 1);

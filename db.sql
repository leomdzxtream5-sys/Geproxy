-- Criação do banco
CREATE DATABASE delta_gerador;

CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    discord_id VARCHAR(255) UNIQUE,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    senha VARCHAR(255),
    avatar VARCHAR(255),
    saldo DECIMAL(10,2) DEFAULT 0.00,
    trial_used BOOLEAN DEFAULT FALSE,
    trial_expira TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    afiliado_codigo VARCHAR(20) UNIQUE,
    afiliado_por INT REFERENCES usuarios(id),
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE proxies (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id),
    proxy_host VARCHAR(255) NOT NULL,
    proxy_port INT NOT NULL,
    proxy_user VARCHAR(100),
    proxy_pass VARCHAR(255),
    tipo VARCHAR(20) DEFAULT 'http',
    pais VARCHAR(5),
    status VARCHAR(20) DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE historico_proxies (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id),
    quantidade INT NOT NULL,
    tipo VARCHAR(20),
    custo DECIMAL(10,2),
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE tickets (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id),
    assunto VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'aberto',
    criado_em TIMESTAMP DEFAULT NOW()
);

CREATE TABLE afiliados (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id),
    indicado_id INT NOT NULL REFERENCES usuarios(id),
    comissao DECIMAL(10,2) DEFAULT 0.00,
    criado_em TIMESTAMP DEFAULT NOW()
);

-- Admin padrão
INSERT INTO usuarios (username, email, senha, is_admin) 
VALUES ('admin', 'admin@delta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);
-- Senha: password

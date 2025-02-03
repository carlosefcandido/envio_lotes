-- Criação das tabelas
CREATE TABLE usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel_usuario ENUM('operador', 'supervisor') NOT NULL
);

CREATE TABLE tipo_pagamento (
    id_tipo INT PRIMARY KEY AUTO_INCREMENT,
    nome_tipo VARCHAR(50) NOT NULL
);

CREATE TABLE movimento (
    id_movimento INT PRIMARY KEY AUTO_INCREMENT,
    id_tipo INT,
    lote VARCHAR(50) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    id_usuario INT,
    data_salvo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_tipo) REFERENCES tipo_pagamento(id_tipo),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);
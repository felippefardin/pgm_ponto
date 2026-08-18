CREATE DATABASE IF NOT EXISTS pgm_ponto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pgm_ponto;

-- 1. Tabela de Instituições (Administradores)
CREATE TABLE instituicoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_instituicao VARCHAR(255) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    codigo_verificacao VARCHAR(10), -- Código enviado por e-mail
    verificado BOOLEAN DEFAULT FALSE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabela de Servidores
CREATE TABLE servidores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituicao_id INT,
    nome_completo VARCHAR(255) NOT NULL,
    matricula VARCHAR(50) UNIQUE NOT NULL,
    pin CHAR(6) NOT NULL, -- PIN de 6 dígitos
    face_token LONGTEXT, -- Hash ou Base64 da face cadastrada
    status ENUM('ativo', 'bloqueado', 'arquivado') DEFAULT 'ativo',
    email_recuperacao VARCHAR(150), -- Onde o ADM recebe o PIN
    FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id) ON DELETE CASCADE
);

-- 3. Tabela de Configuração de Geofencing (Mapa)
CREATE TABLE cercas_geograficas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instituicao_id INT,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    raio_metros INT DEFAULT 100, -- Raio permitido para bater ponto
    UNIQUE KEY uk_cerca_instituicao (instituicao_id),
    FOREIGN KEY (instituicao_id) REFERENCES instituicoes(id)
);

-- 4. Tabela de Registros de Ponto
CREATE TABLE pontos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servidor_id INT,
    tipo ENUM('entrada', 'pausa', 'saida') NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    latitude_registro DECIMAL(10, 8),
    longitude_registro DECIMAL(11, 8),
    registro_manual BOOLEAN DEFAULT FALSE, -- Identifica se foi feito pelo ADM na auditoria
    justificativa TEXT, -- Caso seja manual
    FOREIGN KEY (servidor_id) REFERENCES servidores(id) ON DELETE CASCADE
);

-- 5. Tabela de Auditoria e Logs (O Coração da Segurança)
CREATE TABLE logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    adm_id INT, -- Quem fez a alteração
    servidor_afetado_id INT,
    ponto_id INT,
    acao ENUM('inserir', 'editar', 'excluir') NOT NULL,
    valor_antigo JSON, -- Armazena o estado anterior do ponto
    valor_novo JSON,   -- Armazena o novo estado
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (adm_id) REFERENCES instituicoes(id),
    FOREIGN KEY (servidor_afetado_id) REFERENCES servidores(id)
);

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220820015243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agendamento (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, cliente_id VARCHAR(25) NOT NULL, empresa_id VARCHAR(14) NOT NULL, funcionario_id VARCHAR(25) NOT NULL, horario DATETIME NOT NULL, compareceu TINYINT(1) NOT NULL, atrasado TINYINT(1) NOT NULL, cancelado DATETIME DEFAULT NULL, forma_pagto VARCHAR(10) DEFAULT \'Dinheiro\' NOT NULL, conclusao_esperada DATETIME DEFAULT NULL, concluido TINYINT(1) NOT NULL, pagamento_pendente TINYINT(1) DEFAULT \'1\' NOT NULL, pagamento_presencial TINYINT(1) NOT NULL, INDEX IDX_1F6FB7AADE734E51 (cliente_id), INDEX IDX_1F6FB7AA521E1991 (empresa_id), INDEX IDX_1F6FB7AA642FEB76 (funcionario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agendamento_cancelamento (agendamento_id BIGINT UNSIGNED NOT NULL, requested_by_id VARCHAR(25) NOT NULL, cancelled_ts DATETIME NOT NULL, reason LONGTEXT NOT NULL, INDEX IDX_D7941C3D4DA1E751 (requested_by_id), PRIMARY KEY(agendamento_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agendamento_pagamento (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', agendamento_id BIGINT UNSIGNED NOT NULL, data DATETIME NOT NULL, forma_pagto VARCHAR(10) NOT NULL, valor NUMERIC(8, 2) NOT NULL, status_atual VARCHAR(15) NOT NULL, ultima_modificacao DATETIME NOT NULL, capturado TINYINT(1) NOT NULL, processador VARCHAR(15) DEFAULT NULL, log JSON DEFAULT NULL, INDEX IDX_79A18377C427592F (agendamento_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agendamento_pagamento_request (token VARCHAR(20) NOT NULL, agendamento_id BIGINT UNSIGNED NOT NULL, validade DATETIME NOT NULL, UNIQUE INDEX UNIQ_763646F1C427592F (agendamento_id), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agendamento_servicos (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, agendamento_id BIGINT UNSIGNED DEFAULT NULL, servico_id BIGINT UNSIGNED DEFAULT NULL, avaliacao_cliente INT NOT NULL, INDEX IDX_85DA170DC427592F (agendamento_id), INDEX IDX_85DA170D82E14982 (servico_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cliente_avaliacao (cnpj_id VARCHAR(14) NOT NULL, cpf_id VARCHAR(25) NOT NULL, atrasos INT DEFAULT 0 NOT NULL, cancelamentos INT DEFAULT 0 NOT NULL, bloqueios INT DEFAULT 0 NOT NULL, bloqueado TINYINT(1) DEFAULT \'0\' NOT NULL, inicio_bloqueio DATETIME DEFAULT NULL, concluido INT DEFAULT 0 NOT NULL, INDEX IDX_7E16C8E621850CA6 (cnpj_id), INDEX IDX_7E16C8E6413D8865 (cpf_id), PRIMARY KEY(cnpj_id, cpf_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE empresa (cnpj VARCHAR(14) NOT NULL, nome_empresa VARCHAR(45) NOT NULL, cor_fundo CHAR(7) DEFAULT \'#FFFFFF\' NOT NULL, logo VARCHAR(256) DEFAULT NULL, intervalo_bloqueio VARCHAR(10) DEFAULT \'Nunca\' NOT NULL, qtde_bloqueio INT UNSIGNED DEFAULT 0 NOT NULL, intervalo_analise VARCHAR(10) DEFAULT \'Meses\' NOT NULL, qtde_analise INT UNSIGNED DEFAULT 1 NOT NULL, atrasos_tolerados INT UNSIGNED DEFAULT 1000 NOT NULL, cancelamentos_tolerados INT UNSIGNED DEFAULT 1000 NOT NULL, endereco VARCHAR(100) NOT NULL, cidade VARCHAR(50) NOT NULL, uf CHAR(2) NOT NULL, cep VARCHAR(8) DEFAULT NULL, qtde_licencas INT UNSIGNED DEFAULT 1 NOT NULL, cor_texto VARCHAR(255) DEFAULT NULL, cor_label VARCHAR(255) DEFAULT NULL, cor_boxes VARCHAR(255) DEFAULT NULL, cor_input VARCHAR(255) DEFAULT NULL, PRIMARY KEY(cnpj)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE empresa_processador_pagamento (id INT AUTO_INCREMENT NOT NULL, empresa_id VARCHAR(14) NOT NULL, processador VARCHAR(15) NOT NULL, pix VARCHAR(36) DEFAULT NULL, max_parcelas_cartao INT DEFAULT 1 NOT NULL, politica_parcelamento JSON NOT NULL, INDEX IDX_2E3F9D5D521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE empresa_turno_trabalho (dia_semana INT NOT NULL, empresa_id VARCHAR(14) NOT NULL, hora_inicio TIME NOT NULL, hora_fim TIME NOT NULL, INDEX IDX_213F360521E1991 (empresa_id), PRIMARY KEY(empresa_id, dia_semana)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE funcionario_local_trabalho (cnpj_id VARCHAR(14) NOT NULL, cpf_funcionario_id VARCHAR(25) NOT NULL, ativo TINYINT(1) NOT NULL, salario NUMERIC(10, 0) DEFAULT NULL, comissao NUMERIC(10, 0) DEFAULT NULL, privilegios JSON NOT NULL, INDEX IDX_16A9B0421850CA6 (cnpj_id), INDEX IDX_16A9B04BB74571 (cpf_funcionario_id), PRIMARY KEY(cnpj_id, cpf_funcionario_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE funcionario_turno_trabalho (dia_semana INT NOT NULL, cnpj_id VARCHAR(14) NOT NULL, cpf_funcionario_id VARCHAR(25) NOT NULL, hora_inicio TIME NOT NULL, hora_fim TIME NOT NULL, INDEX IDX_35AB97F421850CA6 (cnpj_id), INDEX IDX_35AB97F4BB74571 (cpf_funcionario_id), PRIMARY KEY(cnpj_id, cpf_funcionario_id, dia_semana)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE perfil (nome_usuario VARCHAR(25) NOT NULL, pessoa_id VARCHAR(11) NOT NULL, uid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, password VARCHAR(180) NOT NULL, roles JSON NOT NULL, confirmado TINYINT(1) NOT NULL, atualizado_em DATETIME DEFAULT NULL, discriminator VARCHAR(255) NOT NULL, INDEX IDX_96657647DF6FA0A5 (pessoa_id), INDEX email_profile_index (email), PRIMARY KEY(nome_usuario)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE perfil_cliente_payment_id (id INT AUTO_INCREMENT NOT NULL, perfil_cliente_id VARCHAR(25) NOT NULL, processador VARCHAR(15) NOT NULL, cards JSON NOT NULL, INDEX IDX_9639A1F146073E07 (perfil_cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pessoa (cpf VARCHAR(11) NOT NULL, nome VARCHAR(50) NOT NULL, telefone VARCHAR(11) NOT NULL, endereco VARCHAR(180) DEFAULT NULL, uid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_1CDFAB82539B0606 (uid), PRIMARY KEY(cpf)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registro_acesso (reg BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', usuario_id VARCHAR(25) NOT NULL, origem VARCHAR(255) NOT NULL, dh DATETIME NOT NULL, INDEX IDX_B30784ADDB38439E (usuario_id), PRIMARY KEY(reg)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id VARCHAR(25) NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE servico (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, empresa_id VARCHAR(14) NOT NULL, servico VARCHAR(45) NOT NULL, descricao TINYTEXT DEFAULT NULL, valor NUMERIC(8, 2) NOT NULL, foto VARCHAR(256) DEFAULT NULL, ativo TINYINT(1) NOT NULL, duracao TIME DEFAULT NULL, INDEX IDX_14873CC521E1991 (empresa_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE agendamento ADD CONSTRAINT FK_1F6FB7AADE734E51 FOREIGN KEY (cliente_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE agendamento ADD CONSTRAINT FK_1F6FB7AA521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE agendamento ADD CONSTRAINT FK_1F6FB7AA642FEB76 FOREIGN KEY (funcionario_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE agendamento_cancelamento ADD CONSTRAINT FK_D7941C3DC427592F FOREIGN KEY (agendamento_id) REFERENCES agendamento (id)');
        $this->addSql('ALTER TABLE agendamento_cancelamento ADD CONSTRAINT FK_D7941C3D4DA1E751 FOREIGN KEY (requested_by_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE agendamento_pagamento ADD CONSTRAINT FK_79A18377C427592F FOREIGN KEY (agendamento_id) REFERENCES agendamento (id)');
        $this->addSql('ALTER TABLE agendamento_pagamento_request ADD CONSTRAINT FK_763646F1C427592F FOREIGN KEY (agendamento_id) REFERENCES agendamento (id)');
        $this->addSql('ALTER TABLE agendamento_servicos ADD CONSTRAINT FK_85DA170DC427592F FOREIGN KEY (agendamento_id) REFERENCES agendamento (id)');
        $this->addSql('ALTER TABLE agendamento_servicos ADD CONSTRAINT FK_85DA170D82E14982 FOREIGN KEY (servico_id) REFERENCES servico (id)');
        $this->addSql('ALTER TABLE cliente_avaliacao ADD CONSTRAINT FK_7E16C8E621850CA6 FOREIGN KEY (cnpj_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE cliente_avaliacao ADD CONSTRAINT FK_7E16C8E6413D8865 FOREIGN KEY (cpf_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE empresa_processador_pagamento ADD CONSTRAINT FK_2E3F9D5D521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE empresa_turno_trabalho ADD CONSTRAINT FK_213F360521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE funcionario_local_trabalho ADD CONSTRAINT FK_16A9B0421850CA6 FOREIGN KEY (cnpj_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE funcionario_local_trabalho ADD CONSTRAINT FK_16A9B04BB74571 FOREIGN KEY (cpf_funcionario_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE funcionario_turno_trabalho ADD CONSTRAINT FK_35AB97F421850CA6 FOREIGN KEY (cnpj_id) REFERENCES empresa (cnpj)');
        $this->addSql('ALTER TABLE funcionario_turno_trabalho ADD CONSTRAINT FK_35AB97F4BB74571 FOREIGN KEY (cpf_funcionario_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE perfil ADD CONSTRAINT FK_96657647DF6FA0A5 FOREIGN KEY (pessoa_id) REFERENCES pessoa (cpf)');
        $this->addSql('ALTER TABLE perfil_cliente_payment_id ADD CONSTRAINT FK_9639A1F146073E07 FOREIGN KEY (perfil_cliente_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE registro_acesso ADD CONSTRAINT FK_B30784ADDB38439E FOREIGN KEY (usuario_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES perfil (nome_usuario)');
        $this->addSql('ALTER TABLE servico ADD CONSTRAINT FK_14873CC521E1991 FOREIGN KEY (empresa_id) REFERENCES empresa (cnpj)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agendamento_cancelamento DROP FOREIGN KEY FK_D7941C3DC427592F');
        $this->addSql('ALTER TABLE agendamento_pagamento DROP FOREIGN KEY FK_79A18377C427592F');
        $this->addSql('ALTER TABLE agendamento_pagamento_request DROP FOREIGN KEY FK_763646F1C427592F');
        $this->addSql('ALTER TABLE agendamento_servicos DROP FOREIGN KEY FK_85DA170DC427592F');
        $this->addSql('ALTER TABLE agendamento DROP FOREIGN KEY FK_1F6FB7AA521E1991');
        $this->addSql('ALTER TABLE cliente_avaliacao DROP FOREIGN KEY FK_7E16C8E621850CA6');
        $this->addSql('ALTER TABLE empresa_processador_pagamento DROP FOREIGN KEY FK_2E3F9D5D521E1991');
        $this->addSql('ALTER TABLE empresa_turno_trabalho DROP FOREIGN KEY FK_213F360521E1991');
        $this->addSql('ALTER TABLE funcionario_local_trabalho DROP FOREIGN KEY FK_16A9B0421850CA6');
        $this->addSql('ALTER TABLE funcionario_turno_trabalho DROP FOREIGN KEY FK_35AB97F421850CA6');
        $this->addSql('ALTER TABLE servico DROP FOREIGN KEY FK_14873CC521E1991');
        $this->addSql('ALTER TABLE agendamento DROP FOREIGN KEY FK_1F6FB7AADE734E51');
        $this->addSql('ALTER TABLE agendamento DROP FOREIGN KEY FK_1F6FB7AA642FEB76');
        $this->addSql('ALTER TABLE agendamento_cancelamento DROP FOREIGN KEY FK_D7941C3D4DA1E751');
        $this->addSql('ALTER TABLE cliente_avaliacao DROP FOREIGN KEY FK_7E16C8E6413D8865');
        $this->addSql('ALTER TABLE funcionario_local_trabalho DROP FOREIGN KEY FK_16A9B04BB74571');
        $this->addSql('ALTER TABLE funcionario_turno_trabalho DROP FOREIGN KEY FK_35AB97F4BB74571');
        $this->addSql('ALTER TABLE perfil_cliente_payment_id DROP FOREIGN KEY FK_9639A1F146073E07');
        $this->addSql('ALTER TABLE registro_acesso DROP FOREIGN KEY FK_B30784ADDB38439E');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE perfil DROP FOREIGN KEY FK_96657647DF6FA0A5');
        $this->addSql('ALTER TABLE agendamento_servicos DROP FOREIGN KEY FK_85DA170D82E14982');
        $this->addSql('DROP TABLE agendamento');
        $this->addSql('DROP TABLE agendamento_cancelamento');
        $this->addSql('DROP TABLE agendamento_pagamento');
        $this->addSql('DROP TABLE agendamento_pagamento_request');
        $this->addSql('DROP TABLE agendamento_servicos');
        $this->addSql('DROP TABLE cliente_avaliacao');
        $this->addSql('DROP TABLE empresa');
        $this->addSql('DROP TABLE empresa_processador_pagamento');
        $this->addSql('DROP TABLE empresa_turno_trabalho');
        $this->addSql('DROP TABLE funcionario_local_trabalho');
        $this->addSql('DROP TABLE funcionario_turno_trabalho');
        $this->addSql('DROP TABLE perfil');
        $this->addSql('DROP TABLE perfil_cliente_payment_id');
        $this->addSql('DROP TABLE pessoa');
        $this->addSql('DROP TABLE registro_acesso');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE servico');
    }
}

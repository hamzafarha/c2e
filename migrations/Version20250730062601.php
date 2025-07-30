<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250730062601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE equipement (ideq INT AUTO_INCREMENT NOT NULL, typeeq VARCHAR(255) NOT NULL, nomeq VARCHAR(255) NOT NULL, referenceeq VARCHAR(255) NOT NULL, localisationeq VARCHAR(255) NOT NULL, modeleeq VARCHAR(255) NOT NULL, numserieeq VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, code_qr VARCHAR(255) DEFAULT NULL, createdBy INT DEFAULT NULL, INDEX IDX_B8B4C6F3D3564642 (createdBy), PRIMARY KEY(ideq)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE intervention (idint INT AUTO_INCREMENT NOT NULL, ideq INT NOT NULL, dateint DATE NOT NULL, typeint VARCHAR(255) NOT NULL, technicien VARCHAR(255) NOT NULL, etatapres VARCHAR(255) NOT NULL, prochainedate DATE NOT NULL, INDEX IDX_D11814AB173CDA21 (ideq), PRIMARY KEY(idint)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD CONSTRAINT FK_B8B4C6F3D3564642 FOREIGN KEY (createdBy) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intervention ADD CONSTRAINT FK_D11814AB173CDA21 FOREIGN KEY (ideq) REFERENCES equipement (ideq)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP FOREIGN KEY FK_B8B4C6F3D3564642
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intervention DROP FOREIGN KEY FK_D11814AB173CDA21
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE equipement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE intervention
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624091944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article CHANGE id id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entreestock DROP FOREIGN KEY FK_CAB3E7B7E89848B6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entreestock ADD CONSTRAINT FK_CAB3E7B7E89848B6 FOREIGN KEY (idart_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement ADD code_qr VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intervention CHANGE prochainedate prochainedate DATE NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article CHANGE id id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entreestock DROP FOREIGN KEY FK_CAB3E7B7E89848B6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE entreestock ADD CONSTRAINT FK_CAB3E7B7E89848B6 FOREIGN KEY (idart_id) REFERENCES article (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE equipement DROP code_qr
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE intervention CHANGE prochainedate prochainedate VARCHAR(255) NOT NULL
        SQL);
    }
}

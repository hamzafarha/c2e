<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250721071647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE backup_log ADD backup_name VARCHAR(255) NOT NULL, ADD status VARCHAR(50) NOT NULL, ADD details LONGTEXT DEFAULT NULL, ADD total_size_gb DOUBLE PRECISION DEFAULT NULL, ADD files_processed INT DEFAULT NULL, ADD errors_count INT DEFAULT NULL, ADD backup_type VARCHAR(255) NOT NULL, DROP result, DROP size, CHANGE source_path source_path VARCHAR(255) DEFAULT NULL, CHANGE destination_path destination_path VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE backup_log ADD result VARCHAR(10) NOT NULL, ADD size VARCHAR(20) NOT NULL, DROP backup_name, DROP status, DROP details, DROP total_size_gb, DROP files_processed, DROP errors_count, DROP backup_type, CHANGE source_path source_path LONGTEXT NOT NULL, CHANGE destination_path destination_path LONGTEXT NOT NULL
        SQL);
    }
}

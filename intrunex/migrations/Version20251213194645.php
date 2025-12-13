<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251213194645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__activity_log AS SELECT id, message, status, created_at FROM activity_log');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('CREATE TABLE activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, message VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO activity_log (id, message, status, created_at) SELECT id, message, status, created_at FROM __temp__activity_log');
        $this->addSql('DROP TABLE __temp__activity_log');
        $this->addSql('CREATE INDEX IDX_FD06F647A76ED395 ON activity_log (user_id)');
        $this->addSql('ALTER TABLE user ADD COLUMN created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__activity_log AS SELECT id, message, status, created_at FROM activity_log');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('CREATE TABLE activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, message VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO activity_log (id, message, status, created_at) SELECT id, message, status, created_at FROM __temp__activity_log');
        $this->addSql('DROP TABLE __temp__activity_log');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, name, country_code, mobile_number, organization_name, job_role, job_description FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles CLOB DEFAULT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, country_code VARCHAR(10) DEFAULT NULL, mobile_number VARCHAR(20) DEFAULT NULL, organization_name VARCHAR(255) DEFAULT NULL, job_role VARCHAR(100) DEFAULT NULL, job_description VARCHAR(500) DEFAULT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, name, country_code, mobile_number, organization_name, job_role, job_description) SELECT id, email, roles, password, name, country_code, mobile_number, organization_name, job_role, job_description FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}

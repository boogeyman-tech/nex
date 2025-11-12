<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112053629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_vulnerability_vulnerability AS SELECT id, asset_id, cve_id, description, severity, discovered_at, status FROM asset_vulnerability_vulnerability');
        $this->addSql('DROP TABLE asset_vulnerability_vulnerability');
        $this->addSql('CREATE TABLE asset_vulnerability_vulnerability (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, scan_job_id INTEGER DEFAULT NULL, cve_id VARCHAR(100) NOT NULL, description CLOB NOT NULL, severity VARCHAR(50) NOT NULL, discovered_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, FOREIGN KEY (asset_id) REFERENCES asset_discovery_asset (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_627E66A6D7516B57 FOREIGN KEY (scan_job_id) REFERENCES scan_job (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO asset_vulnerability_vulnerability (id, asset_id, cve_id, description, severity, discovered_at, status) SELECT id, asset_id, cve_id, description, severity, discovered_at, status FROM __temp__asset_vulnerability_vulnerability');
        $this->addSql('DROP TABLE __temp__asset_vulnerability_vulnerability');
        $this->addSql('CREATE INDEX IDX_627E66A65DA1941 ON asset_vulnerability_vulnerability (asset_id)');
        $this->addSql('CREATE INDEX IDX_627E66A6D7516B57 ON asset_vulnerability_vulnerability (scan_job_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_vulnerability_vulnerability AS SELECT id, asset_id, cve_id, description, severity, discovered_at, status FROM asset_vulnerability_vulnerability');
        $this->addSql('DROP TABLE asset_vulnerability_vulnerability');
        $this->addSql('CREATE TABLE asset_vulnerability_vulnerability (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, cve_id VARCHAR(100) NOT NULL, description CLOB NOT NULL, severity VARCHAR(50) NOT NULL, discovered_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, CONSTRAINT FK_627E66A65DA1941 FOREIGN KEY (asset_id) REFERENCES asset_discovery_asset (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO asset_vulnerability_vulnerability (id, asset_id, cve_id, description, severity, discovered_at, status) SELECT id, asset_id, cve_id, description, severity, discovered_at, status FROM __temp__asset_vulnerability_vulnerability');
        $this->addSql('DROP TABLE __temp__asset_vulnerability_vulnerability');
        $this->addSql('CREATE INDEX IDX_627E66A65DA1941 ON asset_vulnerability_vulnerability (asset_id)');
    }
}

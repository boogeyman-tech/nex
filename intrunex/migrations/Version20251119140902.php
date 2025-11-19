<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251119140902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_discovery_asset AS SELECT id, user_id, name, ip_address, type, status, description, url, domain, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at, user_asset_number FROM asset_discovery_asset');
        $this->addSql('DROP TABLE asset_discovery_asset');
        $this->addSql('CREATE TABLE asset_discovery_asset (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, description CLOB DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, domain VARCHAR(255) DEFAULT NULL, operating_system VARCHAR(255) DEFAULT NULL, open_ports CLOB DEFAULT NULL, last_profiled_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , last_vulnerability_scan_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , user_asset_number INTEGER DEFAULT NULL, CONSTRAINT FK_665E49E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO asset_discovery_asset (id, user_id, name, ip_address, type, status, description, url, domain, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at, user_asset_number) SELECT id, user_id, name, ip_address, type, status, description, url, domain, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at, user_asset_number FROM __temp__asset_discovery_asset');
        $this->addSql('DROP TABLE __temp__asset_discovery_asset');
        $this->addSql('CREATE INDEX IDX_665E49E3A76ED395 ON asset_discovery_asset (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__scan_job AS SELECT id, asset_id, status, started_at, finished_at, scanner, error_message FROM scan_job');
        $this->addSql('DROP TABLE scan_job');
        $this->addSql('CREATE TABLE scan_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, status VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , finished_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , scanner VARCHAR(255) DEFAULT NULL, details CLOB DEFAULT NULL, CONSTRAINT FK_8FFE2CAF5DA1941 FOREIGN KEY (asset_id) REFERENCES asset_discovery_asset (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scan_job (id, asset_id, status, started_at, finished_at, scanner, details) SELECT id, asset_id, status, started_at, finished_at, scanner, error_message FROM __temp__scan_job');
        $this->addSql('DROP TABLE __temp__scan_job');
        $this->addSql('CREATE INDEX IDX_8FFE2CAF5DA1941 ON scan_job (asset_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles CLOB DEFAULT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__asset_discovery_asset AS SELECT id, user_id, user_asset_number, name, ip_address, url, domain, type, status, description, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at FROM asset_discovery_asset');
        $this->addSql('DROP TABLE asset_discovery_asset');
        $this->addSql('CREATE TABLE asset_discovery_asset (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, user_asset_number INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, domain VARCHAR(255) DEFAULT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, description CLOB DEFAULT NULL, operating_system VARCHAR(255) DEFAULT NULL, open_ports CLOB DEFAULT NULL --(DC2Type:json)
        , last_profiled_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , last_vulnerability_scan_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_665E49E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO asset_discovery_asset (id, user_id, user_asset_number, name, ip_address, url, domain, type, status, description, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at) SELECT id, user_id, user_asset_number, name, ip_address, url, domain, type, status, description, operating_system, open_ports, last_profiled_at, last_vulnerability_scan_at FROM __temp__asset_discovery_asset');
        $this->addSql('DROP TABLE __temp__asset_discovery_asset');
        $this->addSql('CREATE INDEX IDX_665E49E3A76ED395 ON asset_discovery_asset (user_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__scan_job AS SELECT id, asset_id, status, started_at, finished_at, scanner, details FROM scan_job');
        $this->addSql('DROP TABLE scan_job');
        $this->addSql('CREATE TABLE scan_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, status VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , finished_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , scanner VARCHAR(255) DEFAULT NULL, error_message CLOB DEFAULT NULL, CONSTRAINT FK_8FFE2CAF5DA1941 FOREIGN KEY (asset_id) REFERENCES asset_discovery_asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO scan_job (id, asset_id, status, started_at, finished_at, scanner, error_message) SELECT id, asset_id, status, started_at, finished_at, scanner, details FROM __temp__scan_job');
        $this->addSql('DROP TABLE __temp__scan_job');
        $this->addSql('CREATE INDEX IDX_8FFE2CAF5DA1941 ON scan_job (asset_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }
}

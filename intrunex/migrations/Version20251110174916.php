<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251110174916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$schema->hasTable('vulnerability_scan_job')) {
            $this->addSql('CREATE TABLE vulnerability_scan_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, asset_id INTEGER NOT NULL, status VARCHAR(50) NOT NULL, started_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
            , completed_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
            , CONSTRAINT FK_595204E95DA1941 FOREIGN KEY (asset_id) REFERENCES asset_discovery_asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
            $this->addSql('CREATE INDEX IDX_595204E95DA1941 ON vulnerability_scan_job (asset_id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE vulnerability_scan_job');
    }
}

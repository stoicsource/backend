<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221029160531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE edition ADD sources LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\' AFTER source');

        $this->addSql("UPDATE edition SET sources = CONCAT('[{\"url\":\"', source, '\",\"type\":\"text\"}]');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE edition DROP sources');
    }
}

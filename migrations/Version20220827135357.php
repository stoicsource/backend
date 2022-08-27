<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220827135357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edition ADD has_content TINYINT(1) NOT NULL AFTER author_id, ADD copyright LONGTEXT NOT NULL AFTER source, ADD internal_comment LONGTEXT DEFAULT NULL');
        $this->addSql('UPDATE edition SET has_content = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE edition DROP has_content, DROP copyright, DROP internal_comment');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210725195747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM content WHERE content LIKE '%[none%'");

        $this->addSql("
            DELETE FROM author WHERE url_slug = 'poq';
            DELETE FROM edition WHERE id NOT IN (SELECT edition_id FROM author_edition);
        ");

        $this->addSql("DELETE
            FROM edition
            WHERE id NOT IN (SELECT DISTINCT edition_id FROM content);
            
            DELETE
            FROM work
            WHERE id NOT IN (SELECT DISTINCT work_id FROM edition);
            
            DELETE
            FROM toc_entry
            WHERE id NOT IN (SELECT DISTINCT toc_entry_id FROM content);
            
            DELETE
            FROM author
            WHERE id NOT IN (SELECT author_id FROM work_author)
            AND id NOT IN (SELECT author_id FROM author_edition);
            ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

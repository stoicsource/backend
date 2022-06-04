<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220604213215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // duplicate 50 into 51 - and then let the manual labour begin
        $this->addSql("
            INSERT INTO content (toc_entry_id, edition_id, content, notes, title, content_type)
            SELECT 2851 as toc_entry_id, edition_id, content, notes, title, content_type
            FROM content
            WHERE toc_entry_id = (SELECT id FROM toc_entry WHERE work_id = 1 AND label = '50');
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

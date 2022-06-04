<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220604205820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // fix toc enchiridion (work id = 1)
        // 51 is actually 52, 52 is actually 53
        $this->addSql("
            UPDATE toc_entry SET label = '53', sort_order = 1133 WHERE work_id = 1 AND label = '52';
            UPDATE toc_entry SET label = '52', sort_order = 1132 WHERE work_id = 1 AND label = '51';
            ");

        // 50 contains 50 and 51 combined
        // create new 51
        $this->addSql("INSERT INTO toc_entry (work_id, label, sort_order) VALUES (1, '51', 1131)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

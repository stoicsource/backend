<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210628144505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A974281A5E');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9D79AB9E5');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A974281A5E FOREIGN KEY (edition_id) REFERENCES edition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9D79AB9E5 FOREIGN KEY (toc_entry_id) REFERENCES toc_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE edition DROP FOREIGN KEY FK_A891181FBB3453DB');
        $this->addSql('ALTER TABLE edition ADD CONSTRAINT FK_A891181FBB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE toc_entry DROP FOREIGN KEY FK_E808B257BB3453DB');
        $this->addSql('ALTER TABLE toc_entry ADD CONSTRAINT FK_E808B257BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9D79AB9E5');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A974281A5E');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9D79AB9E5 FOREIGN KEY (toc_entry_id) REFERENCES toc_entry (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A974281A5E FOREIGN KEY (edition_id) REFERENCES edition (id)');
        $this->addSql('ALTER TABLE edition DROP FOREIGN KEY FK_A891181FBB3453DB');
        $this->addSql('ALTER TABLE edition ADD CONSTRAINT FK_A891181FBB3453DB FOREIGN KEY (work_id) REFERENCES work (id)');
        $this->addSql('ALTER TABLE toc_entry DROP FOREIGN KEY FK_E808B257BB3453DB');
        $this->addSql('ALTER TABLE toc_entry ADD CONSTRAINT FK_E808B257BB3453DB FOREIGN KEY (work_id) REFERENCES work (id)');
    }
}

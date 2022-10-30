<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221030141411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_FEC530A974281A5E');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_FEC530A9D79AB9E5');
        $this->addSql('ALTER TABLE chapter CHANGE content_format content_format SMALLINT NOT NULL, CHANGE notes_format notes_format SMALLINT NOT NULL');
        $this->addSql('DROP INDEX idx_fec530a9d79ab9e5 ON chapter');
        $this->addSql('CREATE INDEX IDX_F981B52ED79AB9E5 ON chapter (toc_entry_id)');
        $this->addSql('DROP INDEX idx_fec530a974281a5e ON chapter');
        $this->addSql('CREATE INDEX IDX_F981B52E74281A5E ON chapter (edition_id)');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_FEC530A974281A5E FOREIGN KEY (edition_id) REFERENCES edition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_FEC530A9D79AB9E5 FOREIGN KEY (toc_entry_id) REFERENCES toc_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE edition CHANGE quality quality SMALLINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52ED79AB9E5');
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E74281A5E');
        $this->addSql('ALTER TABLE chapter CHANGE content_format content_format SMALLINT DEFAULT 1 NOT NULL, CHANGE notes_format notes_format SMALLINT DEFAULT 1');
        $this->addSql('DROP INDEX idx_f981b52ed79ab9e5 ON chapter');
        $this->addSql('CREATE INDEX IDX_FEC530A9D79AB9E5 ON chapter (toc_entry_id)');
        $this->addSql('DROP INDEX idx_f981b52e74281a5e ON chapter');
        $this->addSql('CREATE INDEX IDX_FEC530A974281A5E ON chapter (edition_id)');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52ED79AB9E5 FOREIGN KEY (toc_entry_id) REFERENCES toc_entry (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E74281A5E FOREIGN KEY (edition_id) REFERENCES edition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE edition CHANGE quality quality SMALLINT DEFAULT 6 NOT NULL');
    }
}

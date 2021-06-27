<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210627201232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE author_edition (author_id INT NOT NULL, edition_id INT NOT NULL, INDEX IDX_EDAB875CF675F31B (author_id), INDEX IDX_EDAB875C74281A5E (edition_id), PRIMARY KEY(author_id, edition_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, toc_entry_id INT NOT NULL, edition_id INT NOT NULL, INDEX IDX_FEC530A9D79AB9E5 (toc_entry_id), INDEX IDX_FEC530A974281A5E (edition_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE edition (id INT AUTO_INCREMENT NOT NULL, work_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_A891181FBB3453DB (work_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE toc_entry (id INT AUTO_INCREMENT NOT NULL, work_id INT NOT NULL, INDEX IDX_E808B257BB3453DB (work_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE work (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE author_edition ADD CONSTRAINT FK_EDAB875CF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE author_edition ADD CONSTRAINT FK_EDAB875C74281A5E FOREIGN KEY (edition_id) REFERENCES edition (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9D79AB9E5 FOREIGN KEY (toc_entry_id) REFERENCES toc_entry (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A974281A5E FOREIGN KEY (edition_id) REFERENCES edition (id)');
        $this->addSql('ALTER TABLE edition ADD CONSTRAINT FK_A891181FBB3453DB FOREIGN KEY (work_id) REFERENCES work (id)');
        $this->addSql('ALTER TABLE toc_entry ADD CONSTRAINT FK_E808B257BB3453DB FOREIGN KEY (work_id) REFERENCES work (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author_edition DROP FOREIGN KEY FK_EDAB875CF675F31B');
        $this->addSql('ALTER TABLE author_edition DROP FOREIGN KEY FK_EDAB875C74281A5E');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A974281A5E');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9D79AB9E5');
        $this->addSql('ALTER TABLE edition DROP FOREIGN KEY FK_A891181FBB3453DB');
        $this->addSql('ALTER TABLE toc_entry DROP FOREIGN KEY FK_E808B257BB3453DB');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE author_edition');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE edition');
        $this->addSql('DROP TABLE toc_entry');
        $this->addSql('DROP TABLE work');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220604183611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE edition ADD author_id INT NOT NULL AFTER work_id');
        $this->addSql("UPDATE edition, author_edition SET edition.author_id = author_edition.author_id WHERE edition.id = author_edition.edition_id;");
        $this->addSql('ALTER TABLE edition ADD CONSTRAINT FK_A891181FF675F31B FOREIGN KEY (author_id) REFERENCES author (id)');
        $this->addSql('CREATE INDEX IDX_A891181FF675F31B ON edition (author_id)');

        $this->addSql('ALTER TABLE work ADD author_id INT NOT NULL AFTER id');
        $this->addSql("UPDATE work, work_author SET work.author_id = work_author.author_id WHERE work.id = work_author.work_id;");
        $this->addSql('ALTER TABLE work ADD CONSTRAINT FK_534E6880F675F31B FOREIGN KEY (author_id) REFERENCES author (id)');
        $this->addSql('CREATE INDEX IDX_534E6880F675F31B ON work (author_id)');

        $this->addSql('DROP TABLE author_edition');
        $this->addSql('DROP TABLE work_author');
    }

    public function down(Schema $schema): void
    {

    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Romans\Filter\IntToRoman;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210725200620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $filter = new IntToRoman();

        for ($num = 1; $num < 50; $num++) {
            $roman = $filter->filter($num);

            $this->addSql("UPDATE content SET content = replace(content, '$roman.', '') WHERE content LIKE '$roman.%'");
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

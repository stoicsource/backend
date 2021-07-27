<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Romans\Filter\IntToRoman;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210727153522 extends AbstractMigration
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

            $romanFirstUpper = $roman[0] . mb_strtolower(substr($roman, 1));

            $this->addSql("UPDATE content SET content = replace(content, '$romanFirstUpper.', '') WHERE content LIKE '$romanFirstUpper.%'");
        }

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

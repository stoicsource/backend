<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210824200451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $workId = 2;
        $sets = [
            ['1.1611', '1.1618', '1.1623', '1.1628'],
            ['1.1711', '1.1715', '1.1719', '1.1722'],
            ['3.0401', '3.0405', '3.0407', '3.0409'],
            ['4.0301', '4.0304', '4.0307', '4.0311'],
            ['5.0801', '5.0806', '5.0811'],
            ['9.011', '9.014', '9.017'],
            ['9.091', '9.094', '9.097'],
            ['9.421', '9.425', '9.429'],
            ['10.071', '10.075'],
            ['10.0801', '10.0806'],
            ['10.331', '10.336'],
            ['11.1801', '11.1806', '11.1811', '11.1815', '11.1818']
        ];

        foreach ($sets as $set) {
            // 92 - 105, less 95
            for ($editionId = 92; $editionId <= 105; $editionId++) {
                if ($editionId === 95) {
                    continue;
                }

                $combinedContent = '';
                for ($setEntryId = 0; $setEntryId < sizeof($set); $setEntryId++) {
                    $contentLabel = $set[$setEntryId];

                    $sql = "SELECT content FROM content WHERE edition_id = $editionId AND toc_entry_id = (SELECT id FROM toc_entry WHERE work_id = $workId AND label = '$contentLabel')";
                    $partialContent = $this->connection->executeQuery($sql)->fetchOne();
                    $combinedContent .= ($combinedContent > '' ? " " : '') . $partialContent;
                }
                $combinedContent = str_replace("'", "''", $combinedContent);
                $firstEntryLabel = $set[0];
                $sql = "UPDATE content SET content = '$combinedContent' WHERE edition_id = $editionId AND toc_entry_id = (SELECT id FROM toc_entry WHERE work_id = $workId AND label = '$firstEntryLabel')";
                $this->connection->executeQuery($sql);
                // $this->addSql($sql);
            }

            for ($setEntryId = 1; $setEntryId < sizeof($set); $setEntryId++) {
                $labelToDelete = $set[$setEntryId];
                $sql = "DELETE FROM toc_entry WHERE work_id = $workId AND label = '$labelToDelete'";
                $this->connection->executeQuery($sql);
                // $this->addSql($sql);
            }

            $firstEntryLabel = $set[0];
            $shortenedLabel = substr($firstEntryLabel, 0, 4);
            $sql = "UPDATE toc_entry SET label = '$shortenedLabel' WHERE work_id = $workId AND label = '$firstEntryLabel'";
            $this->connection->executeQuery($sql);
            // $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

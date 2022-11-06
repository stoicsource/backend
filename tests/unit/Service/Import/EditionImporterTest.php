<?php

namespace App\Tests\unit\Service\Import;

use App\Adapter\EditionWebSourceInterface;
use App\Dto\ChapterDto;
use App\Entity\Chapter;
use App\Repository\AuthorRepository;
use App\Repository\ChapterRepository;
use App\Repository\EditionRepository;
use App\Repository\TocEntryRepository;
use App\Repository\WorkRepository;
use App\Service\Import\ChapterImporter;
use App\Service\Import\EditionImporter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EditionImporterTest extends TestCase
{
    /*
     * tests to write
     * - creates edition
     * o uses existing edition
     * + creates all chapters
     * o creates chapters with footnotes
     * o sets content format
     * o sets notes format
     * - validates html content, i.e. makes sure there are no invalid tags in title and content
     */

    public function testSavesAllChapters(): void
    {
        $sourceMock = $this->createMock(EditionWebSourceInterface::class);
        $sourceMock->method('getChapters')->willReturn([new ChapterDto()]);

        $chapterRepoMock = $this->createMock(ChapterRepository::class);
        $chapterRepoMock->expects(self::once())->method('save')->with($this->callback(function ($saveArgument) {
            return $saveArgument instanceof Chapter;
        }));

        $importer = new EditionImporter(
            new ChapterImporter(
                $chapterRepoMock,
                $this->createMock(TocEntryRepository::class),
            ),
            $this->createMock(WorkRepository::class),
            $this->createMock(EditionRepository::class),
            $this->createMock(AuthorRepository::class),
            $this->createMock(EntityManagerInterface::class),
        );

        $importer->import($sourceMock, 'InvalidUrl');
    }
}

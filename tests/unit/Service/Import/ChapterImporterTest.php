<?php

namespace App\Tests\unit\Service\Import;

use App\Dto\ChapterDto;
use App\Entity\Chapter;
use App\Entity\Edition;
use App\Entity\TocEntry;
use App\Repository\ChapterRepository;
use App\Repository\TocEntryRepository;
use App\Service\Import\ChapterImporter;
use PHPUnit\Framework\TestCase;

class ChapterImporterTest extends TestCase
{
    public function testWritesToChapterRepo(): void
    {
        $chapterDto = new ChapterDto("The Chapter's title",
            "The content to be found in this chapter",
            "4.12");

        $editionMock = $this->createMock(Edition::class);

        $chapterRepoMock = $this->createMock(ChapterRepository::class);
        $chapterRepoMock->expects(self::once())->method('save')->with($this->callback(
            function ($savedChapter) {
                return
                    $savedChapter instanceof Chapter;
            }
        ));

        $importer = new ChapterImporter(
            $chapterRepoMock,
            $this->createMock(TocEntryRepository::class)
        );

        $importer->import($chapterDto, $editionMock);
    }

    public function testSetsCorrectTocEntry()
    {
        $chapterDto = new ChapterDto("irrelevant",
            "irrelevant",
            "4.12");

        $chapterValidator = function ($savedChapter) use ($chapterDto) {
            assert($savedChapter instanceof Chapter);
            return $savedChapter->getTocEntry()->getLabel() === $chapterDto->getTocLabel();
        };

        $editionMock = $this->createMock(Edition::class);

        $chapterRepoMock = $this->createMock(ChapterRepository::class);
        $chapterRepoMock->expects(self::once())->method('save')->with($this->callback(
            $chapterValidator
        ));

        $tocEntryRepositoryMock = $this->createMock(TocEntryRepository::class);
        $tocEntryRepositoryMock->method('findOrCreate')
            ->with($this->anything(), $chapterDto->getTocLabel())
            ->willReturn((new TocEntry())->setLabel($chapterDto->getTocLabel()));

        $importer = new ChapterImporter(
            $chapterRepoMock,
            $tocEntryRepositoryMock
        );

        $importer->import($chapterDto, $editionMock);
    }
}

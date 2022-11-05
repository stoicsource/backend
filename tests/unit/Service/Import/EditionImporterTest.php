<?php

namespace App\Tests\unit\Service\Import;

use App\Adapter\EditionWebSourceInterface;
use App\Dto\ChapterDto;
use App\Entity\Chapter;
use App\Repository\ChapterRepository;
use App\Service\Import\EditionImporter;
use PHPUnit\Framework\TestCase;

class EditionImporterTest extends TestCase
{
    /*
     * tests to write
     * - creates edition
     * - uses existing edition
     * + creates all chapters
     * - creates chapters with footnotes
     * - sets content format
     * - sets notes format
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

        $importer = new EditionImporter($chapterRepoMock);
        $importer->import($sourceMock, 'InvalidUrl');
    }
}

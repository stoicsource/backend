<?php

namespace App\Service\Import;

use App\Dto\ChapterDto;
use App\Entity\Chapter;
use App\Entity\Edition;
use App\Repository\ChapterRepository;
use App\Repository\TocEntryRepository;

class ChapterImporter
{
    public function __construct(
        private readonly ChapterRepository $chapterRepository,
        private readonly TocEntryRepository $tocEntryRepository
    )
    {
    }

    public function import(ChapterDto $chapterDto, Edition $edition): void
    {
        $tocEntry = $this->tocEntryRepository->findOrCreate(
            $edition->getWork(),
            $chapterDto->getTocLabel(),
            $chapterDto->getSortOrder(),
            false
        );

        $chapter = Chapter::fromDto($chapterDto);
        $chapter->setEdition($edition);
        $chapter->setTocEntry($tocEntry);
        $chapter->setContentFormat(Chapter::CONTENT_TYPE_HTML);
        $this->chapterRepository->save($chapter, false);
    }
}
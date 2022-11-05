<?php

namespace App\Service\Import;

use App\Adapter\EditionWebSourceInterface;
use App\Dto\ChapterDto;
use App\Entity\Chapter;
use App\Repository\AuthorRepository;
use App\Repository\ChapterRepository;
use App\Repository\EditionRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;

class EditionImporter
{
    public function __construct(
        private readonly ChapterImporter $chapterImporter,
        private readonly WorkRepository $workRepository,
        private readonly EditionRepository $editionRepository,
        private readonly AuthorRepository $authorRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function import(EditionWebSourceInterface $sourceMaterialAdapter, string $sourceUrl): void
    {
        $discoursesWork = $this->workRepository->findByNameOrCreate(
            'Discourses',
            'discourses',
            $this->authorRepository->findOneBy(['name' => 'Epictetus']),
            false
        );

        $edition = $this->editionRepository->create(
            'The Discourses of Epictetus',
            1877,
            $discoursesWork,
            $this->authorRepository->findOneBy(['name' => 'George Long']),
            [],
            false
        );

        foreach ($sourceMaterialAdapter->getChapters($sourceUrl) as $chapter) {
            assert($chapter instanceof ChapterDto);

            $this->chapterImporter->import($chapter, $edition);
        }

        $this->entityManager->flush();
    }
}
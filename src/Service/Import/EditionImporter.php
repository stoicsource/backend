<?php

namespace App\Service\Import;

use App\Adapter\EditionWebSourceInterface;
use App\Dto\ChapterDto;
use App\Entity\Author;
use App\Entity\Chapter;
use App\Entity\Work;
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
        $sourceEdition = $sourceMaterialAdapter->getEdition();
//        $work = $this->workRepository->findByNameOrCreate(
//            'Discourses',
//            'discourses',
//            $this->authorRepository->findOneBy(['name' => 'Epictetus']),
//            false
//        );
        $work = $this->workRepository->findOneBy(['name' => $sourceEdition->getWorkName()]);
        assert($work instanceof Work);

        $author = $this->authorRepository->findOneBy(['name' => $sourceEdition->getAuthorName()]);
        assert($author instanceof Author);

        $edition = $this->editionRepository->create(
            $sourceEdition->getName(),
            $sourceEdition->getYear(),
            $work,
            $author,
            $sourceEdition->getSources(),
            false
        );

        foreach ($sourceMaterialAdapter->getChapters($sourceUrl) as $chapter) {
            assert($chapter instanceof ChapterDto);

            $this->chapterImporter->import($chapter, $edition);
        }

        $this->entityManager->flush();
    }
}
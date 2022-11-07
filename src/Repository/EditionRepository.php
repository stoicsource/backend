<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Edition;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Edition::class);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function create(string $name, string $year, Work $work, Author $author, array $sources, bool $flush = true): Edition
    {
        $edition = new Edition();
        $edition->setName($name);
        $edition->setWork($work);
        $edition->setYear($year);
        $edition->setLanguage('eng');
        $edition->setSources($sources);
        $edition->setAuthor($author);
        $edition->setQuality(Edition::QUALITY_SOLID);
        $edition->setHasContent(true);
        $edition->setCopyright('Public Domain');
        $edition->setContributor(null);
        $this->getEntityManager()->persist($edition);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $edition;
    }

    public function remove(Edition $existingEdition, bool $flush = true): void
    {
        $this->getEntityManager()->remove($existingEdition);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}

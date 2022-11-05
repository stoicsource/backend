<?php

namespace App\Repository;

use App\Entity\Author;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Work|null find($id, $lockMode = null, $lockVersion = null)
 * @method Work|null findOneBy(array $criteria, array $orderBy = null)
 * @method Work[]    findAll()
 * @method Work[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Work::class);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function findByNameOrCreate(string $workName, string $urlSlug, Author $author, bool $flush = true): Work
    {
        $work = $this->findOneBy(['name' => $workName]);
        if (!$work) {
            $work = new Work();
            $work->setAuthor($author);
            $work->setName($workName);
            $work->setUrlSlug($urlSlug);
            $this->getEntityManager()->persist($work);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }

        return $work;
    }
}

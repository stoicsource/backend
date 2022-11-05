<?php

namespace App\Repository;

use App\Entity\TocEntry;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TocEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method TocEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method TocEntry[]    findAll()
 * @method TocEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TocEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TocEntry::class);
    }

    public function findOrCreate(Work $work, string $label, int $sortOrder, bool $flush = true): TocEntry
    {
        $tocEntry = $this->findOneBy(['work' => $work, 'label' => $label]);
        if (!$tocEntry) {
            $tocEntry = new TocEntry();
            $tocEntry->setWork($work);
            $tocEntry->setLabel($label);
            $tocEntry->setSortOrder($sortOrder);
            $this->getEntityManager()->persist($tocEntry);
            if ($flush) {
                $this->getEntityManager()->flush();
            }
        }
        return $tocEntry;
    }
}

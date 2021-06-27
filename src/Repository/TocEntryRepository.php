<?php

namespace App\Repository;

use App\Entity\TocEntry;
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

    // /**
    //  * @return TocEntry[] Returns an array of TocEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TocEntry
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\ArtisticWork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisticWork|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisticWork|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisticWork[]    findAll()
 * @method ArtisticWork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisticWorkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisticWork::class);
    }

    // /**
    //  * @return ArtisticWork[] Returns an array of ArtisticWork objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ArtisticWork
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

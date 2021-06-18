<?php

namespace App\Repository;

use App\Entity\ArtisticWorkVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArtisticWorkVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtisticWorkVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArtisticWorkVente[]    findAll()
 * @method ArtisticWorkVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtisticWorkVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArtisticWorkVente::class);
    }

    // /**
    //  * @return ArtisticWorkVente[] Returns an array of ArtisticWorkVente objects
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
    public function findOneBySomeField($value): ?ArtisticWorkVente
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

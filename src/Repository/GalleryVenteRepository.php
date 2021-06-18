<?php

namespace App\Repository;

use App\Entity\GalleryVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GalleryVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryVente[]    findAll()
 * @method GalleryVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GalleryVente::class);
    }

    // /**
    //  * @return GalleryVente[] Returns an array of GalleryVente objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GalleryVente
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

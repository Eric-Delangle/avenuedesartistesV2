<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }
    /*

    public function __toString()
    {
        return (string) $this->find($category); 
    }
    */

     /**
  
      */
    /*
    public function findAllVisibleQuery(): Query
    {
        return $this->findAllVisibleQuery()
          ->getQuery();
    }
   

    /*
    public function findOneBySomeField($value): ?Gallery
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

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

    public function findForMarketplace(array $filters = []): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.category', 'c')
            ->addSelect('c')
            ->where("a.listingType != 'none'")
            ->andWhere("a.status = 'available'")
            ->orderBy('a.createdAt', 'DESC');

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'sale') {
                $qb->andWhere("a.listingType IN ('sale', 'both')");
            } elseif ($filters['type'] === 'exchange') {
                $qb->andWhere("a.listingType IN ('exchange', 'both')");
            }
        }

        if (!empty($filters['category']) && is_numeric($filters['category'])) {
            $qb->andWhere('c.id = :cat')->setParameter('cat', (int) $filters['category']);
        }

        if (!empty($filters['priceMin']) && is_numeric($filters['priceMin'])) {
            $qb->andWhere('(a.price >= :pmin OR a.price IS NULL)')->setParameter('pmin', (float) $filters['priceMin']);
        }

        if (!empty($filters['priceMax']) && is_numeric($filters['priceMax'])) {
            $qb->andWhere('(a.price <= :pmax OR a.price IS NULL)')->setParameter('pmax', (float) $filters['priceMax']);
        }

        return $qb->getQuery();
    }
}
<?php

namespace App\Repository;

use App\DTO\SearchSiteDTO;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 *
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function searchByCriteria(SearchSiteDTO $searchDTO): array
    {
        $qb = $this->createQueryBuilder('s');

        $this->addSearchFilters($qb, $searchDTO);

        $qb->orderBy('s.name', 'ASC');

        if ($searchDTO->limit) {
            $qb->setMaxResults($searchDTO->limit);
        }

        if ($searchDTO->page && $searchDTO->limit) {
            $offset = ($searchDTO->page - 1) * $searchDTO->limit;
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByCriteria(SearchSiteDTO $searchDTO): int
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');

        $this->addSearchFilters($qb, $searchDTO);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function addSearchFilters($qb, SearchSiteDTO $searchDTO): void
    {
        if ($searchDTO->q) {
            $qb->andWhere('s.name LIKE :q OR s.states LIKE :q')
               ->setParameter('q', '%' . $searchDTO->q . '%');
        }

        if ($searchDTO->name) {
            $qb->andWhere('s.name LIKE :name')
               ->setParameter('name', '%' . $searchDTO->name . '%');
        }

        if ($searchDTO->country) {
            $qb->andWhere('s.states LIKE :country')
               ->setParameter('country', '%' . $searchDTO->country . '%');
        }

        if ($searchDTO->category) {
            $qb->andWhere('s.category = :category')
               ->setParameter('category', $searchDTO->category);
        }

        if ($searchDTO->region) {
            $qb->andWhere('s.region LIKE :region')
               ->setParameter('region', '%' . $searchDTO->region . '%');
        }

        if ($searchDTO->minLat !== null) {
            $qb->andWhere('s.latitude >= :minLat')
               ->setParameter('minLat', $searchDTO->minLat);
        }

        if ($searchDTO->maxLat !== null) {
            $qb->andWhere('s.latitude <= :maxLat')
               ->setParameter('maxLat', $searchDTO->maxLat);
        }

        if ($searchDTO->minLon !== null) {
            $qb->andWhere('s.longitude >= :minLon')
               ->setParameter('minLon', $searchDTO->minLon);
        }

        if ($searchDTO->maxLon !== null) {
            $qb->andWhere('s.longitude <= :maxLon')
               ->setParameter('maxLon', $searchDTO->maxLon);
        }
    }

    //    /**
    //     * @return Site[] Returns an array of Site objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Site
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}

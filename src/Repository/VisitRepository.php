<?php

namespace App\Repository;

use App\Entity\Visit;
use App\Entity\User;
use App\Entity\Site;
use App\Enum\VisitType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 */
class VisitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->setParameter('user', $user)
            ->orderBy('v.visitedAt', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWishlistByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->andWhere('v.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', VisitType::WISHLIST)
            ->orderBy('v.visitedAt', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findVisitedByUser(User $user): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->andWhere('v.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', VisitType::VISITED)
            ->orderBy('v.visitedAt', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUserVisitForSite(User $user, Site $site): ?Visit
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->andWhere('v.site = :site')
            ->setParameter('user', $user)
            ->setParameter('site', $site)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserVisitForSiteAndType(User $user, Site $site, VisitType $type): ?Visit
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->andWhere('v.site = :site')
            ->andWhere('v.type = :type')
            ->setParameter('user', $user)
            ->setParameter('site', $site)
            ->setParameter('type', $type)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

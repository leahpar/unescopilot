<?php

namespace App\Repository;

use App\DTO\SearchVisitDTO;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Visit;
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

    public function findByUser(User $user, SearchVisitDTO $searchVisitDTO): array
    {
        $qb = $this->createQueryBuilder('v');

        if ($searchVisitDTO->userId) {
            $qb->andWhere('v.user = :userId')
                ->setParameter('userId', $searchVisitDTO->userId);
        } else {
            $qb->andWhere('v.user = :user')
                ->setParameter('user', $user);
        }

        $qb->orderBy('v.visitedAt', 'DESC')
            ->addOrderBy('v.id', 'DESC');

        if ($searchVisitDTO->type) {
            $qb->andWhere('v.type = :type')
                ->setParameter('type', $searchVisitDTO->type);
        }

        return $qb->getQuery()->getResult();
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

<?php

namespace App\Repository;

use App\Entity\UserToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserToken>
 */
class UserTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserToken::class);
    }

    public function findValidToken(string $token): ?UserToken
    {
        return $this->createQueryBuilder('ut')
            ->where('ut.token = :token')
            ->andWhere('ut.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('ut')
            ->delete()
            ->where('ut.expiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function deleteUserTokens(int $userId): int
    {
        return $this->createQueryBuilder('ut')
            ->delete()
            ->where('ut.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
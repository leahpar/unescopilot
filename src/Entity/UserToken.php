<?php

namespace App\Entity;

use App\Repository\UserTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    public string $token;

    #[ORM\Column]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column]
    public \DateTimeImmutable $expiresAt;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $deviceInfo = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    public User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }
}
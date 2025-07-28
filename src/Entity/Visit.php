<?php

namespace App\Entity;

use App\Enum\VisitType;
use App\Repository\VisitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitRepository::class)]
#[ORM\Table]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Site::class)]
    #[ORM\JoinColumn(nullable: false)]
    public ?Site $site = null;

    // Year of visit
    #[ORM\Column(nullable: true)]
    public ?int $visitedAt = null;

    #[ORM\Column(type: 'string', enumType: VisitType::class)]
    public VisitType $type = VisitType::WISHLIST;

}

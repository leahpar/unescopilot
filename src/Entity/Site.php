<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\Table]
class Site
{
    /**
     * The unique ID from the UNESCO source data (id_number).
     * We use this as our primary key but do not auto-generate it,
     * as it's provided during the data import.
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 255)]
    public ?string $category = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $shortDescription = null;

    #[ORM\Column(length: 255)]
    public ?string $httpUrl = null;

    #[ORM\Column(length: 255)]
    public ?string $imageUrl = null;

    #[ORM\Column(type: 'float')]
    public ?float $latitude = null;

    #[ORM\Column(type: 'float')]
    public ?float $longitude = null;

    #[ORM\Column(type: 'integer')]
    public ?int $dateInscribed = null;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $states = null;

    #[ORM\Column(type: 'boolean')]
    public ?bool $transboundary = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $criteriaTxt = null;

    #[ORM\Column(length: 10, nullable: true)]
    public ?string $isoCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $region = null;
}

<?php

namespace App\DTO;

use App\Enum\VisitType;
use Symfony\Component\Validator\Constraints as Assert;

class CreateVisitDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public ?int $siteId = null;

    #[Assert\NotBlank]
    public ?VisitType $type = null;

    #[Assert\Type('integer')]
    #[Assert\Range(min: 1900, max: 2100)]
    public ?int $visitedAt = null;
}
<?php

namespace App\DTO;

use App\Enum\VisitType;

class SearchVisitDTO
{
    public ?VisitType $type = null;
    public ?int $userId = null;
}

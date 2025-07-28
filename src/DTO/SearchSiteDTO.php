<?php

namespace App\DTO;

class SearchSiteDTO
{
    public ?string $q = null;
    public ?string $name = null;
    public ?string $country = null;
    public ?string $category = null;
    public int $page = 1;
    public int $limit = 20;
    
    public ?float $minLat = null;
    public ?float $maxLat = null;
    public ?float $minLon = null;
    public ?float $maxLon = null;
}

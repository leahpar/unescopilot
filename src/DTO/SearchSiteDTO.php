<?php

namespace App\DTO;

class SearchSiteDTO
{
    public ?string $name = null;
    public ?string $country = null;
    public ?string $category = null;
    public int $page = 1;
    public int $limit = 20;
}
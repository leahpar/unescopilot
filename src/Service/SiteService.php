<?php

namespace App\Service;

use App\Entity\Site;
use App\Repository\SiteRepository;

class SiteService
{
    public function __construct(
        private readonly SiteRepository $siteRepository
    ) {
    }

    public function getAllSites(): array
    {
        return $this->siteRepository->findAll();
    }

    public function getSiteById(int $id): ?Site
    {
        return $this->siteRepository->find($id);
    }
}
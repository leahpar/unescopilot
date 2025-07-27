<?php

namespace App\Service;

use App\DTO\SearchSiteDTO;
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

    public function searchSites(SearchSiteDTO $searchDTO): array
    {
        return $this->siteRepository->searchByCriteria($searchDTO);
    }
}
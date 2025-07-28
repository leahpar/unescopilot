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
        $sites = $this->siteRepository->searchByCriteria($searchDTO);
        $total = $this->siteRepository->countByCriteria($searchDTO);
        
        $totalPages = $searchDTO->limit > 0 ? (int) ceil($total / $searchDTO->limit) : 1;
        
        return [
            'data' => $sites,
            'total' => $total,
            'page' => $searchDTO->page,
            'limit' => $searchDTO->limit,
            'totalPages' => $totalPages
        ];
    }
}
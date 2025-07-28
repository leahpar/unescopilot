<?php

namespace App\Controller;

use App\DTO\SearchSiteDTO;
use App\Entity\Site;
use App\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sites')]
class SiteController extends AbstractController
{
    public function __construct(
        private readonly SiteService $siteService
    ) {
    }

    #[Route('', name: 'app_api_site_list', methods: ['GET'])]
    public function list(#[MapQueryString] SearchSiteDTO $searchDTO): JsonResponse
    {
        $sites = $this->siteService->searchSites($searchDTO);

        return $this->json($sites);
    }

    #[Route('/{id}', name: 'app_api_site_show', methods: ['GET'])]
    public function show(Site $site): JsonResponse
    {
        return $this->json($site);
    }
}

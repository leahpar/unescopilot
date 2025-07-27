<?php

namespace App\Controller;

use App\DTO\SearchSiteDTO;
use App\Entity\Site;
use App\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sites')]
class SiteController extends AbstractController
{
    public function __construct(
        private readonly SiteService $siteService
    ) {
    }

    #[Route('', name: 'app_api_site_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $searchDTO = new SearchSiteDTO();
        $searchDTO->name = $request->query->get('name');
        $searchDTO->country = $request->query->get('country');
        $searchDTO->category = $request->query->get('category');
        $searchDTO->page = (int) $request->query->get('page', 1);
        $searchDTO->limit = (int) $request->query->get('limit', 20);

        $sites = $this->siteService->searchSites($searchDTO);

        return $this->json($sites);
    }

    #[Route('/{id}', name: 'app_api_site_show', methods: ['GET'])]
    public function show(Site $site): JsonResponse
    {
        return $this->json($site);
    }
}

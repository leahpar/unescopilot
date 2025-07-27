<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sites')]
class SiteController extends AbstractController
{
    public function __construct(
        private readonly SiteRepository $siteRepository
    ) {
    }

    #[Route('', name: 'app_api_site_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $sites = $this->siteRepository->findAll();

        return $this->json($sites);
    }

    #[Route('/{id}', name: 'app_api_site_show', methods: ['GET'])]
    public function show(Site $site): JsonResponse
    {
        return $this->json($site);
    }
}

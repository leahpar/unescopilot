<?php

namespace App\Controller;

use App\Service\RankingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class RankingController extends AbstractController
{
    public function __construct(private readonly RankingService $rankingService)
    {
    }

    #[Route('/ranking', name: 'app_api_ranking', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getRanking(): JsonResponse
    {
        $ranking = $this->rankingService->getRanking();
        return $this->json($ranking);
    }
}

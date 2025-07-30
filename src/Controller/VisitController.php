<?php

namespace App\Controller;

use App\DTO\CreateVisitDTO;
use App\DTO\SearchVisitDTO;
use App\Entity\Site;
use App\Entity\Visit;
use App\Repository\SiteRepository;
use App\Repository\VisitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/visits')]
#[IsGranted('ROLE_USER')]
class VisitController extends AbstractController
{
    public function __construct(
        private readonly VisitRepository $visitRepository,
        private readonly SiteRepository $siteRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'app_api_visit_list', methods: ['GET'])]
    public function list(#[MapQueryString] ?SearchVisitDTO $dto): JsonResponse
    {
        $dto ??= new SearchVisitDTO();
        if (null === $dto->userId) {
            $dto->userId = $this->getUser()->getId();
        }

        $visits = $this->visitRepository->findByUser($this->getUser(), $dto);

        return $this->json($visits);
    }

    

    #[Route('', name: 'app_api_visit_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateVisitDTO $dto): JsonResponse
    {
        $site = $this->siteRepository->find($dto->siteId);
        if (!$site) {
            return $this->json(['error' => 'Site not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if user already has a visit with the same type for this site
        $existingVisit = $this->visitRepository->findUserVisitForSiteAndType($this->getUser(), $site, $dto->type);
        if ($existingVisit) {
            return $this->json($existingVisit, Response::HTTP_OK);
        }

        $visit = new Visit();
        $visit->user = $this->getUser();
        $visit->site = $site;
        $visit->type = $dto->type;
        $visit->visitedAt = $dto->visitedAt;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        return $this->json($visit, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_api_visit_show', methods: ['GET'])]
    public function show(Visit $visit): JsonResponse
    {
        if ($visit->user !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($visit);
    }

    #[Route('/{id}', name: 'app_api_visit_delete', methods: ['DELETE'])]
    public function delete(Visit $visit): JsonResponse
    {
        if ($visit->user !== $this->getUser()) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($visit);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/site/{id}', name: 'app_api_visit_by_site', methods: ['GET'])]
    public function getVisitBySite(Site $site): JsonResponse
    {
        $visit = $this->visitRepository->findUserVisitForSite($this->getUser(), $site);

        if (!$visit) {
            return $this->json(null, Response::HTTP_NOT_FOUND);
        }

        return $this->json($visit);
    }
}

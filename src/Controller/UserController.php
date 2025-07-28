<?php

namespace App\Controller;

use App\DTO\UpdateProfileDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }
    #[Route('', name: 'app_api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser()
        ]);
    }

    #[Route('/profile', name: 'app_api_me_profile', methods: ['PUT'])]
    public function updateProfile(#[MapRequestPayload] UpdateProfileDTO $updateDTO): JsonResponse
    {
        $user = $this->getUser();
        
        // Seul le pseudo peut être modifié, pas l'email
        $user->setPseudo($updateDTO->pseudo);
        
        $this->entityManager->flush();
        
        return $this->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    // Future endpoint for password change:
    // #[Route('/password', methods: ['PUT'])] - Change password
}

<?php

namespace App\Controller;

use App\DTO\UpdateProfileDTO;
use App\DTO\UserProfileDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/me', name: 'app_api_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        return $this->json(UserProfileDTO::fromUser($this->getUser()));
    }

    #[Route('/me/profile', name: 'app_api_me_profile', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(#[MapRequestPayload] UpdateProfileDTO $updateDTO): JsonResponse
    {
        $user = $this->getUser();

        // Seul le pseudo peut être modifié, pas l'email
        $user->setPseudo($updateDTO->pseudo);

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Profile updated successfully',
            'user' => UserProfileDTO::fromUser($user)
        ]);
    }

    #[Route('/users', name: 'app_api_users', methods: ['GET'])]
    public function users(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $dtos = array_map(fn (User $user) => UserProfileDTO::fromUser($user), $users);

        return $this->json($dtos);
    }

    #[Route('/users/{id}', name: 'app_api_user_profile', methods: ['GET'])]
    public function userProfile(User $user): JsonResponse
    {
        return $this->json(UserProfileDTO::fromUser($user));
    }

    // Future endpoint for password change:
    // #[Route('/password', methods: ['PUT'])] - Change password
}

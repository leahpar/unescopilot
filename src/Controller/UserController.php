<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/me')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('', name: 'app_api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser()
        ]);
    }

    // Future endpoints for account management:
    // #[Route('/profile', methods: ['PUT'])] - Update profile
    // #[Route('/password', methods: ['PUT'])] - Change password
}

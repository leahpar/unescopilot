<?php

namespace App\Controller;

use App\DTO\LoginDTO;
use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/security')]
class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/register', name: 'app_api_security_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterUserDTO $registerDTO): JsonResponse
    {
        if ($this->userRepository->findOneBy(['email' => $registerDTO->email])) {
            return $this->json(['error' => 'Email already exists'], 409);
        }

        $user = new User();
        $user->setEmail($registerDTO->email);
        $user->setPseudo($registerDTO->pseudo);
        $user->setPassword($this->passwordHasher->hashPassword($user, $registerDTO->password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    #[Route('/login', name: 'app_api_security_login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginDTO $loginDTO): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $loginDTO->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginDTO->password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $tokenString = bin2hex(random_bytes(32));
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $createdAt->add(new \DateInterval('P30D')); // 30 days

        $userToken = new UserToken();
        $userToken->token = $tokenString;
        $userToken->createdAt = $createdAt;
        $userToken->expiresAt = $expiresAt;
        $userToken->user = $user;

        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        return $this->json([
            'token' => $tokenString,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'user' => $user,
        ]);
    }

    #[Route('/logout', name: 'app_api_security_logout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function logout(): JsonResponse
    {
        // Logic to invalidate current token could be added here
        // For now, client-side token removal is sufficient

        return $this->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

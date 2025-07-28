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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository,
        private readonly UserTokenRepository $userTokenRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('/register', name: 'app_api_register', methods: ['POST'])]
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

    #[Route('/login', name: 'app_api_login', methods: ['POST'])]
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

    #[Route('/me', name: 'app_api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json([
            'user' => $this->getUser()
        ]);
    }
}

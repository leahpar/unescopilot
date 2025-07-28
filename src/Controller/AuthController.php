<?php

namespace App\Controller;

use App\DTO\LoginDTO;
use App\DTO\RegisterUserDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository
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
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo()
            ]
        ], 201);
    }

    #[Route('/login', name: 'app_api_login', methods: ['POST'])]
    public function login(#[MapRequestPayload] LoginDTO $loginDTO): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $loginDTO->email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $loginDTO->password)) {
            return $this->json(['error' => 'Invalid credentials'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $createdAt = new \DateTimeImmutable();

        $user->token = $token;
        $user->tokenCreatedAt = $createdAt;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'token' => $token,
            'created_at' => $createdAt->format(\DateTimeInterface::ATOM),
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'pseudo' => $user->getPseudo()
            ]
        ]);
    }
}
<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerMultipleTokensTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserTokenRepository $userTokenRepository;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->userTokenRepository = $container->get(UserTokenRepository::class);
    }

    public function testMultipleTokensForSameUser(): void
    {

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPseudo('testuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // First login
        $this->client->request('POST', '/api/security/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        $firstResponse = json_decode($this->client->getResponse()->getContent(), true);
        $firstToken = $firstResponse['token'];

        // Second login (different device/session)
        $this->client->request('POST', '/api/security/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        $secondResponse = json_decode($this->client->getResponse()->getContent(), true);
        $secondToken = $secondResponse['token'];

        // Verify tokens are different
        $this->assertNotEquals($firstToken, $secondToken);

        // Verify both tokens exist in database
        $firstUserToken = $this->userTokenRepository->findOneBy(['token' => $firstToken]);
        $secondUserToken = $this->userTokenRepository->findOneBy(['token' => $secondToken]);

        $this->assertNotNull($firstUserToken);
        $this->assertNotNull($secondUserToken);
        $this->assertEquals($user->getId(), $firstUserToken->user->getId());
        $this->assertEquals($user->getId(), $secondUserToken->user->getId());

        // Verify user has 2 active tokens
        $userTokens = $this->userTokenRepository->findBy(['user' => $user]);
        $this->assertCount(2, $userTokens);
    }
}
<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthTokenTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testProtectedEndpointWithoutToken(): void
    {

        $this->client->request('GET', '/api/me');

        $this->assertResponseStatusCodeSame(401);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Authentication failed', $responseData['error']);
    }

    public function testProtectedEndpointWithInvalidToken(): void
    {

        $this->client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid-token-here'
        ]);

        $this->assertResponseStatusCodeSame(401);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Authentication failed', $responseData['error']);
    }

    public function testProtectedEndpointWithValidToken(): void
    {

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPseudo('testuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userToken = new UserToken();
        $userToken->token = bin2hex(random_bytes(32));
        $userToken->createdAt = new \DateTimeImmutable();
        $userToken->expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));
        $userToken->user = $user;

        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $userToken->token
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals('test@example.com', $responseData['user']['email']);
        $this->assertEquals('testuser', $responseData['user']['pseudo']);
        $this->assertEquals($user->getId(), $responseData['user']['id']);
    }

    public function testProtectedEndpointWithExpiredToken(): void
    {

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPseudo('testuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userToken = new UserToken();
        $userToken->token = bin2hex(random_bytes(32));
        $userToken->createdAt = new \DateTimeImmutable('-2 days');
        $userToken->expiresAt = new \DateTimeImmutable('-1 day'); // Expired
        $userToken->user = $user;

        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $userToken->token
        ]);

        $this->assertResponseStatusCodeSame(401);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Authentication failed', $responseData['error']);
    }

    public function testPublicEndpointsRemainAccessible(): void
    {

        // Test register endpoint
        $this->client->request('POST', '/api/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@example.com',
            'pseudo' => 'newuser',
            'password' => 'password123'
        ]));

        $this->assertResponseStatusCodeSame(201);

        // Test login endpoint
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newuser@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
    }
}

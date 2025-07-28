<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerLoginTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
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

    public function testLoginSuccess(): void
    {
        $client = static::createClient();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPseudo('testuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('created_at', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        
        $this->assertNotEmpty($responseData['token']);
        $this->assertNotEmpty($responseData['created_at']);
        
        $this->assertEquals('test@example.com', $responseData['user']['email']);
        $this->assertEquals('testuser', $responseData['user']['pseudo']);
        
        $this->assertTrue(\DateTime::createFromFormat(\DateTimeInterface::ATOM, $responseData['created_at']) !== false);
        
        $this->entityManager->refresh($user);
        $this->assertEquals($responseData['token'], $user->token);
        $this->assertEquals($responseData['created_at'], $user->tokenCreatedAt->format(\DateTimeInterface::ATOM));
    }

    public function testLoginInvalidCredentials(): void
    {
        $client = static::createClient();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPseudo('testuser');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLoginNonExistentUser(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]));

        $this->assertResponseStatusCodeSame(401);
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLoginValidationErrors(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'invalid-email',
            'password' => ''
        ]));

        $this->assertResponseStatusCodeSame(422);
    }
}
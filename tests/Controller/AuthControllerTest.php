<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $host = $_ENV['TEST_SERVER_HOST'] ?? '127.0.0.1';
        $port = $_ENV['TEST_SERVER_PORT'] ?? '8016';
        
        $this->client = static::createClient([], [
            'HTTP_HOST' => $host . ':' . $port
        ]);
    }

    public function testRegisterSuccess(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'pseudo' => 'testuser',
            'password' => 'password123'
        ];

        $this->client->request(
            'POST',
            '/api/security/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);
        $this->assertEquals($userData['email'], $responseData['user']['email']);
        $this->assertEquals($userData['pseudo'], $responseData['user']['pseudo']);
        $this->assertArrayHasKey('id', $responseData['user']);
    }

    public function testRegisterDuplicateEmail(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();
        $passwordHasher = $container->get('security.password_hasher');

        $existingUser = new User();
        $existingUser->setEmail('existing@example.com');
        $existingUser->setPseudo('existing');
        $existingUser->setPassword($passwordHasher->hashPassword($existingUser, 'password'));

        $entityManager->persist($existingUser);
        $entityManager->flush();

        $userData = [
            'email' => 'existing@example.com',
            'pseudo' => 'newuser',
            'password' => 'password123'
        ];

        $this->client->request(
            'POST',
            '/api/security/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Email already exists', $responseData['error']);
    }

    public function testRegisterValidationErrors(): void
    {
        $userData = [
            'email' => 'invalid-email',
            'pseudo' => 'ab',
            'password' => '123'
        ];

        $this->client->request(
            'POST',
            '/api/security/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testRegisterMissingFields(): void
    {
        $userData = [
            'email' => 'test@example.com'
        ];

        $this->client->request(
            'POST',
            '/api/security/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }
}
<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
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
    }

    private function createAndLoginUser(string $email, string $pseudo, string $password): string
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userToken = new UserToken();
        $userToken->token = bin2hex(random_bytes(32));
        $userToken->createdAt = new \DateTimeImmutable();
        $userToken->expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));
        $userToken->user = $user;

        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        return $userToken->token;
    }

    public function testGetUsers(): void
    {
        $this->createAndLoginUser('user1@example.com', 'user1', 'password');
        $this->createAndLoginUser('user2@example.com', 'user2', 'password');

        $this->client->request('GET', '/api/users');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $responseData);
        $this->assertEquals('user1', $responseData[0]['pseudo']);
        $this->assertEquals('user2', $responseData[1]['pseudo']);
    }

    public function testGetUserProfile(): void
    {
        $token = $this->createAndLoginUser('user1@example.com', 'user1', 'password');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);

        $this->client->request('GET', '/api/users/' . $user->getId(), [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token
        ]);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('user1', $responseData['pseudo']);
        $this->assertEquals($user->getId(), $responseData['id']);
    }
}

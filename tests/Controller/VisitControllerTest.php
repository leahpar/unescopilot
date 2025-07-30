<?php

namespace App\Tests\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserToken;
use App\Entity\Visit;
use App\Enum\VisitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VisitControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private $client;
    private User $testUser;
    private string $authToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->createTestUserAndToken();
    }

    private function createTestUserAndToken(): void
    {
        $this->testUser = new User();
        $this->testUser->setEmail('test@example.com');
        $this->testUser->setPseudo('testuser');
        $this->testUser->setPassword($this->passwordHasher->hashPassword($this->testUser, 'password123'));

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();

        $userToken = new UserToken();
        $userToken->token = bin2hex(random_bytes(32));
        $userToken->createdAt = new \DateTimeImmutable();
        $userToken->expiresAt = (new \DateTimeImmutable())->add(new \DateInterval('P30D'));
        $userToken->user = $this->testUser;

        $this->entityManager->persist($userToken);
        $this->entityManager->flush();

        $this->authToken = $userToken->token;
    }

    private function createTestSite(int $id = 1234): Site
    {
        $site = new Site();
        $site->id = $id;
        $site->name = 'Test UNESCO Site';
        $site->category = 'Cultural';
        $site->httpUrl = 'https://example.com';
        $site->imageUrl = 'https://example.com/image.jpg';
        $site->latitude = 48.8566;
        $site->longitude = 2.3522;
        $site->dateInscribed = 2023;
        $site->states = 'France';
        $site->transboundary = false;

        $this->entityManager->persist($site);
        $this->entityManager->flush();

        return $site;
    }

    private function getAuthHeaders(): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->authToken];
    }

    public function testListVisitsWithoutAuth(): void
    {
        $this->client->request('GET', '/api/visits');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testListVisitsEmpty(): void
    {
        $this->client->request('GET', '/api/visits', [], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertEmpty($responseData);
    }

    public function testCreateVisit(): void
    {
        $site = $this->createTestSite();

        $visitData = [
            'siteId' => $site->id,
            'type' => VisitType::WISHLIST->value,
            'visitedAt' => null
        ];

        $this->client->request(
            'POST',
            '/api/visits',
            [],
            [],
            array_merge($this->getAuthHeaders(), ['CONTENT_TYPE' => 'application/json']),
            json_encode($visitData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($site->id, $responseData['site']['id']);
        $this->assertEquals(VisitType::WISHLIST->value, $responseData['type']);
    }

    public function testCreateVisitForNonExistentSite(): void
    {
        $visitData = [
            'siteId' => 99999,
            'type' => VisitType::WISHLIST->value
        ];

        $this->client->request(
            'POST',
            '/api/visits',
            [],
            [],
            array_merge($this->getAuthHeaders(), ['CONTENT_TYPE' => 'application/json']),
            json_encode($visitData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Site not found', $responseData['error']);
    }

    public function testCreateDuplicateVisit(): void
    {
        $site = $this->createTestSite();

        $visitData = [
            'siteId' => $site->id,
            'type' => VisitType::WISHLIST->value
        ];

        // Create first visit
        $this->client->request(
            'POST',
            '/api/visits',
            [],
            [],
            array_merge($this->getAuthHeaders(), ['CONTENT_TYPE' => 'application/json']),
            json_encode($visitData)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        // Try to create duplicate
        $this->client->request(
            'POST',
            '/api/visits',
            [],
            [],
            array_merge($this->getAuthHeaders(), ['CONTENT_TYPE' => 'application/json']),
            json_encode($visitData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $responseData);
    }

    public function testListVisitsWithData(): void
    {
        $site = $this->createTestSite();

        $visit = new Visit();
        $visit->user = $this->testUser;
        $visit->site = $site;
        $visit->type = VisitType::VISITED;
        $visit->visitedAt = 2023;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/visits', [], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertEquals($visit->id, $responseData[0]['id']);
        $this->assertEquals(VisitType::VISITED->value, $responseData[0]['type']);
        $this->assertEquals(2023, $responseData[0]['visitedAt']);
    }

    public function testListVisitsWithType(): void
    {
        $site1 = $this->createTestSite(1);
        $site2 = $this->createTestSite(2);

        $visit1 = new Visit();
        $visit1->user = $this->testUser;
        $visit1->site = $site1;
        $visit1->type = VisitType::WISHLIST;

        $visit2 = new Visit();
        $visit2->user = $this->testUser;
        $visit2->site = $site2;
        $visit2->type = VisitType::VISITED;
        $visit2->visitedAt = 2022;

        $this->entityManager->persist($visit1);
        $this->entityManager->persist($visit2);
        $this->entityManager->flush();

        // Test filtering by WISHLIST
        $this->client->request('GET', '/api/visits', ['type' => VisitType::WISHLIST->value], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertEquals(VisitType::WISHLIST->value, $responseData[0]['type']);

        // Test filtering by VISITED
        $this->client->request('GET', '/api/visits', ['type' => VisitType::VISITED->value], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertEquals(VisitType::VISITED->value, $responseData[0]['type']);
    }

    public function testListVisitsForAnotherUser(): void
    {
        $otherUser = new User();
        $otherUser->setEmail('otheruser@example.com');
        $otherUser->setPseudo('otheruser');
        $otherUser->setPassword($this->passwordHasher->hashPassword($otherUser, 'password123'));
        $this->entityManager->persist($otherUser);

        $site = $this->createTestSite(1);

        $visit = new Visit();
        $visit->user = $otherUser;
        $visit->site = $site;
        $visit->type = VisitType::WISHLIST;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/visits', ['userId' => $otherUser->getId()], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertCount(1, $responseData);
        $this->assertEquals($visit->id, $responseData[0]['id']);
    }

    

    public function testDeleteVisit(): void
    {
        $site = $this->createTestSite();

        $visit = new Visit();
        $visit->user = $this->testUser;
        $visit->site = $site;
        $visit->type = VisitType::WISHLIST;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/visits/' . $visit->id, [], [], $this->getAuthHeaders());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testGetVisitBySite(): void
    {
        $site = $this->createTestSite();

        $visit = new Visit();
        $visit->user = $this->testUser;
        $visit->site = $site;
        $visit->type = VisitType::VISITED;
        $visit->visitedAt = 2023;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/visits/site/' . $site->id, [], [], $this->getAuthHeaders());

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($visit->id, $responseData['id']);
        $this->assertEquals(VisitType::VISITED->value, $responseData['type']);
        $this->assertEquals(2023, $responseData['visitedAt']);
    }

    public function testGetVisitBySiteNotFound(): void
    {
        $site = $this->createTestSite();

        $this->client->request('GET', '/api/visits/site/' . $site->id, [], [], $this->getAuthHeaders());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testAccessOtherUserVisitForbidden(): void
    {
        $site = $this->createTestSite();

        // Create another user
        $otherUser = new User();
        $otherUser->setEmail('other@example.com');
        $otherUser->setPseudo('otheruser');
        $otherUser->setPassword($this->passwordHasher->hashPassword($otherUser, 'password123'));

        $this->entityManager->persist($otherUser);
        $this->entityManager->flush();

        // Create visit for other user
        $visit = new Visit();
        $visit->user = $otherUser;
        $visit->site = $site;
        $visit->type = VisitType::WISHLIST;

        $this->entityManager->persist($visit);
        $this->entityManager->flush();

        // Try to access other user's visit
        $this->client->request('GET', '/api/visits/' . $visit->id, [], [], $this->getAuthHeaders());

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Access denied', $responseData['error']);
    }
}

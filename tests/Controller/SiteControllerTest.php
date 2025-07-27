<?php

namespace App\Tests\Controller;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SiteControllerTest extends WebTestCase
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

    public function testListEndpoint(): void
    {
        $this->client->request('GET', '/api/sites');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testListEndpointWithData(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $site = new Site();
        $site->id = 9999;
        $site->name = 'Test UNESCO Site';
        $site->category = 'Cultural';
        $site->httpUrl = 'https://example.com';
        $site->imageUrl = 'https://example.com/image.jpg';
        $site->latitude = 48.8566;
        $site->longitude = 2.3522;
        $site->dateInscribed = 2023;
        $site->states = 'France';
        $site->transboundary = false;

        $entityManager->persist($site);
        $entityManager->flush();

        $this->client->request('GET', '/api/sites');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(1, count($responseData));

        $foundSite = null;
        foreach ($responseData as $siteData) {
            if ($siteData['id'] === 9999) {
                $foundSite = $siteData;
                break;
            }
        }

        $this->assertNotNull($foundSite);
        $this->assertEquals('Test UNESCO Site', $foundSite['name']);
        $this->assertEquals('Cultural', $foundSite['category']);
    }

    public function testShowEndpoint(): void
    {
        $container = static::getContainer();
        $entityManager = $container->get('doctrine')->getManager();

        $site = new Site();
        $site->id = 8888;
        $site->name = 'Test UNESCO Site for Show';
        $site->category = 'Natural';
        $site->httpUrl = 'https://example.com';
        $site->imageUrl = 'https://example.com/image.jpg';
        $site->latitude = 45.0;
        $site->longitude = 3.0;
        $site->dateInscribed = 2022;
        $site->states = 'France';
        $site->transboundary = false;

        $entityManager->persist($site);
        $entityManager->flush();

        $this->client->request('GET', '/api/sites/8888');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertEquals(8888, $responseData['id']);
        $this->assertEquals('Test UNESCO Site for Show', $responseData['name']);
        $this->assertEquals('Natural', $responseData['category']);
    }

}

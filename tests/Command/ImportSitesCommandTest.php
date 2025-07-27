<?php

namespace App\Tests\Command;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ImportSitesCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:import-sites');
        $commandTester = new CommandTester($command);

        $client = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../Fixtures/sites.xml')),
        ]);

        $container = static::getContainer();
        $container->set('Symfony\Contracts\HttpClient\HttpClientInterface', $client);

        $commandTester->execute([]);

        // Debug: show the command output
        echo "\n\nCommand output:\n" . $commandTester->getDisplay() . "\n\n";
        echo "Exit code: " . $commandTester->getStatusCode() . "\n\n";

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('1 sites have been successfully imported or updated', $output);
    }

    public function testImportedDataIsCorrectlyStoredInDatabase(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:import-sites');
        $commandTester = new CommandTester($command);

        $client = new MockHttpClient([
            new MockResponse(file_get_contents(__DIR__ . '/../Fixtures/sites.xml')),
        ]);

        $container = static::getContainer();
        $container->set('Symfony\Contracts\HttpClient\HttpClientInterface', $client);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        // Verify the data is correctly stored in database
        $siteRepository = $container->get(SiteRepository::class);
        $site = $siteRepository->find(1627);

        $this->assertInstanceOf(Site::class, $site);
        $this->assertEquals(1627, $site->id);
        $this->assertEquals('Volcans et forêts de la Montagne Pelée et des pitons du nord de la Martinique', $site->name);
        $this->assertEquals('Natural', $site->category);
        $this->assertEquals('(vii)(x)', $site->criteriaTxt);
        $this->assertEquals(2021, $site->dateInscribed);
        $this->assertEquals('https://whc.unesco.org/fr/list/1627', $site->httpUrl);
        $this->assertEquals('https://whc.unesco.org/uploads/sites/site_1627.jpg', $site->imageUrl);
        $this->assertEquals('fr', $site->isoCode);
        $this->assertEquals(4.33333, $site->latitude);
        $this->assertEquals('Guyane française', $site->location);
        $this->assertEquals(-52.65, $site->longitude);
        $this->assertEquals('Europe et Amérique du Nord', $site->region);
        $this->assertStringContainsString('Les Forêts tropicales et montagnes du nord de la Martinique', $site->shortDescription);
        $this->assertEquals('France', $site->states);
        $this->assertFalse($site->transboundary);
    }
}

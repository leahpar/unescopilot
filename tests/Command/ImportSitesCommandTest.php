<?php

namespace App\Tests\Command;

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
}

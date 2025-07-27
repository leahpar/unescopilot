<?php

namespace App\Command;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-sites',
    description: 'Imports UNESCO sites from the official XML data source.',
)]
class ImportSitesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $client,
        #[Autowire('%env(UNESCO_XML_URL)%')] private readonly string $unescoXmlUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting UNESCO sites import');

        try {
            $response = $this->client->request('GET', $this->unescoXmlUrl);
            $xmlContent = $response->getContent();
            $xml = new \SimpleXMLElement($xmlContent);

            $io->progressStart(count($xml->row));

            foreach ($xml->row as $row) {
                $site = $this->entityManager->getRepository(Site::class)->find((int)$row->id_number);
                if (!$site) {
                    $site = new Site();
                    $site->id = (int)$row->id_number;
                }

                $site->name = (string)$row->site;
                $site->category = (string)$row->category;
                $site->shortDescription = strip_tags((string)$row->short_description);
                $site->httpUrl = (string)$row->http_url;
                $site->imageUrl = (string)$row->image_url;
                $site->latitude = (float)$row->latitude;
                $site->longitude = (float)$row->longitude;
                $site->dateInscribed = (int)$row->date_inscribed;
                $site->states = (string)$row->states;
                $site->transboundary = (bool)$row->transboundary;

                $this->entityManager->persist($site);
                $io->progressAdvance();
            }

            $this->entityManager->flush();
            $io->progressFinish();
            $io->success(sprintf('%d sites have been successfully imported or updated.', count($xml->row)));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('An error occurred during the import process: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
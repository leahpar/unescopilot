<?php

namespace App\Command;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:import-sites',
    description: 'Imports UNESCO sites from the official XML data source.',
)]
class ImportSitesCommand extends Command
{

    const DEFAULT_UNESCO_XML_URL = 'https://whc.unesco.org/fr/list/xml';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'purge',
            null,
            InputOption::VALUE_NONE,
            'Purge all existing sites before importing new ones'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting UNESCO sites import');

        try {
            // Handle purge option
            if ($input->getOption('purge')) {
                $io->section('Purging existing sites');
                $siteRepository = $this->entityManager->getRepository(Site::class);
                $existingSites = $siteRepository->findAll();
                
                if (!empty($existingSites)) {
                    $io->progressStart(count($existingSites));
                    foreach ($existingSites as $site) {
                        $this->entityManager->remove($site);
                        $io->progressAdvance();
                    }
                    $this->entityManager->flush();
                    $io->progressFinish();
                    $io->success(sprintf('%d existing sites have been purged.', count($existingSites)));
                } else {
                    $io->note('No existing sites to purge.');
                }
            }
            $response = $this->client->request('GET', self::DEFAULT_UNESCO_XML_URL, [
                'headers' => [
                    'Accept' => 'application/xml',
                ],
            ]);
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
                $site->transboundary = (string)$row->transboundary === '1';
                $site->criteriaTxt = (string)$row->criteria_txt;
                $site->isoCode = (string)$row->iso_code;
                $site->location = (string)$row->location;
                $site->region = (string)$row->region;

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

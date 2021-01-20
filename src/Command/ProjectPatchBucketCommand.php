<?php

namespace App\Command;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProjectPatchBucketCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $githubApiToken;
    private string $travisCiOrgToken;
    private string $travisCiComToken;

    public function configure()
    {
        $this->setName('app:project:patch-bucket');
    }

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager, string $githubApiToken, string $travisCiOrgToken, string $travisCiComToken)
    {
        parent::__construct();
        $this->client = $client;
        $this->entityManager = $entityManager;
        $this->githubApiToken = $githubApiToken;
        $this->travisCiOrgToken = $travisCiOrgToken;
        $this->travisCiComToken = $travisCiComToken;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$this->entityManager->createQuery('DELETE FROM App\Entity\Project')->getResult();

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        $data = $serializer->decode(file_get_contents('data/extract4.csv'), 'csv');

        foreach ($data as $project) {
            $response = $this->client->request('GET', $project['name'], [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'token '.$this->githubApiToken,
                    ],
                ]
            );

            if (200 !== $response->getStatusCode()) {
                $output->writeln('Cannot fetch GitHub API, skipping...');
                continue;
            }

            $content = $response->toArray();

            // We override the short name in case the repo has been renamed...
            $shortName = $content['full_name'];
            
            $existingProject = $this->entityManager->getRepository(Project::class)->findOneBy(['name' => $shortName]);
            if ($existingProject) {
                $existingProject->setBucket($project['bucket']);
                $this->entityManager->flush();

                $output->writeln('Done with: '.$shortName);
            } else {
                $output->writeln('Not found: '.$shortName);
            }
        }

        return Command::SUCCESS;
    }
}

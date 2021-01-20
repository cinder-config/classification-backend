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

class ProjectHasFeaturesCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $githubApiToken;
    private string $travisCiOrgToken;
    private string $travisCiComToken;

    public function configure()
    {
        $this->setName('app:project:has_features');
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

        $indexedProjects = [];
        $projects = $this->entityManager->getRepository(Project::class)->findAll();
        foreach ($projects as $project) {
            $underscoreName = str_replace("/","_",$project->getName());
            $indexedProjects[$underscoreName] = $project;
        }

        $data = $serializer->decode(file_get_contents('data/projects_with_features.csv'), 'csv');

        foreach ($data as $project) {

            if (array_key_exists($project['name'], $indexedProjects)) {
                /** @var Project $project */
                $project = $indexedProjects[$project['name']];
                $project->setFeaturesExtracted(true);
            }
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}

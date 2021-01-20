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

class ProjectRemoveNotInListAnymoreCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $githubApiToken;
    private string $travisCiOrgToken;
    private string $travisCiComToken;

    public function configure()
    {
        $this->setName('app:project:remove');
    }

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        $data = $serializer->decode(file_get_contents('data/extract4.csv'), 'csv');
        $dbProjects = $this->entityManager->getRepository(Project::class)->findAll();

        /*
        foreach ($data as $project) {

        }

        $dbProjetsIndexed = [];
        foreach ($dbProjects as $dbProject) {
            if (array_key_exists($dbProject->getName(), $dbProjetsIndexed)) {
                dump('duplicate!!');
                dump($dbProject->getId());
                dump($dbProject->getClassifications()->count());
                dump($dbProjetsIndexed[$dbProject->getName()]->getId());
                dump($dbProjetsIndexed[$dbProject->getName()]->getClassifications()->count());
            }
            $dbProjetsIndexed[$dbProject->getName()] = $dbProject;
        }*/

        foreach ($data as $project) {
            $shortName = str_replace('https://api.github.com/repos/', '', $project['name']);
            $dbProject = $this->entityManager->getRepository(Project::class)->findBy(['name' => $shortName]);
            if (!$dbProject) {
                dump('add: '.$shortName);
            }
        }

        return Command::SUCCESS;
    }
}

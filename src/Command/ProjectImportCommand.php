<?php

namespace App\Command;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProjectImportCommand extends Command
{
    private const PROJECTS = ['geoserver/geoserver', 'liip/LiipFunctionalTestBundle', 'facebook/rocksdb'];
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    public function configure()
    {
        $this->setName('app:project:import');
    }

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->client = $client;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->entityManager->createQuery('DELETE FROM App\Entity\Project')->getResult();

        foreach (self::PROJECTS as $project) {

            $output->writeln('Fetching: '.$project);

            $response = $this->client->request('GET', "https://api.github.com/repos/".$project, [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]
            );

            if ($response->getStatusCode() !== 200) {
                $output->writeln('Cannot fetch GitHub API, skipping...');
                continue;
            }

            $content = $response->toArray();

            $dbProject = new Project();
            $dbProject->setName($project);
            $dbProject->setDescription($content['description']);
            $dbProject->setDefaultBranch($content['default_branch']);
            $dbProject->setStars($content['watchers']);
            $dbProject->setForks($content['forks']);
            $dbProject->setLastChangeAt(new \DateTime($content['pushed_at']));

            // Number of commits, little fancy..
            $response = $this->client->request('GET', "https://api.github.com/repos/".$project.'/commits?per_page=1', [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                ]
            );
            if (!isset($response->getHeaders()['link'])) {
                $output->writeln('Cannot fetch number of commits, skipping...');
                continue;
            }

            $link = $response->getHeaders()['link'][0];
            preg_match("/.*next.*page=(.*)>;/", $link, $regexOutput);

            if (isset($regexOutput[1])) {
                $dbProject->setCommits($regexOutput[1]);
            }


            // Fetch configuration
            $response = $this->client->request('GET',
                "https://raw.githubusercontent.com/".$project.'/'.$dbProject->getDefaultBranch().'/.travis.yml');

            if ($response->getStatusCode() !== 200) {
                $output->writeln('Cannot fetch configuration, skipping...');
                continue;
            }

            $config = $response->getContent();
            $dbProject->setConfiguration($config);

            $this->entityManager->persist($dbProject);
            $this->entityManager->flush();

            $output->writeln('Done with: '.$project);
        }

        return Command::SUCCESS;
    }
}

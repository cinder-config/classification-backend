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

class ProjectImportCommand extends Command
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private string $githubApiToken;
    private string $travisCiOrgToken;
    private string $travisCiComToken;

    public function configure()
    {
        $this->setName('app:project:import');
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

        $data = $serializer->decode(file_get_contents('data/truth.csv'), 'csv');

        foreach ($data as $project) {
            $shortName = str_replace('https://api.github.com/repos/', '', $project['name']);

            $existingProject = $this->entityManager->getRepository(Project::class)->findOneBy(['name' => $shortName]);
            if (null !== $existingProject) {
                continue;
            }

            $output->writeln('Fetching: '.$shortName);

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
            if (null !== $existingProject) {
                continue;
            }

            $dbProject = new Project();
            $dbProject->setName($shortName);
            $dbProject->setLanguage($project['language'] ?? '');
            $dbProject->setDescription($content['description'] ?? '');
            $dbProject->setDefaultBranch($content['default_branch']);
            $dbProject->setStars($content['watchers']);
            $dbProject->setForks($content['forks']);
            $dbProject->setLastChangeAt(new \DateTime($content['pushed_at']));
            $dbProject->setBucket($project['bucket']);
            $dbProject->setFeaturesExtracted(false);

            // Number of commits, little fancy..
            $response = $this->client->request('GET', 'https://api.github.com/repos/'.$shortName.'/commits?per_page=1',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'token '.$this->githubApiToken,
                    ],
                ]
            );
            if (!isset($response->getHeaders()['link'])) {
                $output->writeln('Cannot fetch number of commits, skipping...');
                continue;
            }

            $link = $response->getHeaders()['link'][0];
            preg_match('/.*next.*page=(.*)>;/', $link, $regexOutput);

            if (isset($regexOutput[1])) {
                $dbProject->setCommits($regexOutput[1]);
            }

            // Fetch configuration
            $response = $this->client->request('GET',
                'https://raw.githubusercontent.com/'.$shortName.'/'.$dbProject->getDefaultBranch().'/.travis.yml');

            if (200 !== $response->getStatusCode()) {
                $output->writeln('Cannot fetch configuration, skipping...');
                continue;
            }

            $config = $response->getContent();
            $dbProject->setConfiguration($config);

            // Fetch whether travis-ci.org OR travis-ci.com
            try {
                $travisCiOrgResponse = $this->client->request('GET',
                    'https://api.travis-ci.org/repo/'.urlencode($shortName), [
                        'headers' => [
                            'Accept'             => 'application/json',
                            'Authorization'      => 'token '.$this->travisCiOrgToken,
                            'Travis-API-Version' => '3',
                        ],
                    ]);
                $travisCiOrgData = $travisCiOrgResponse->toArray();
            } catch (\Exception $exception) {
                $travisCiOrgData['@type'] = 'error';
            }
            if ('error' === $travisCiOrgData['@type'] or false === $travisCiOrgData['active']) {
                $travisCiComResponse = $this->client->request('GET',
                    'https://api.travis-ci.com/repo/'.urlencode($shortName), [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Authorization' => 'token '.$this->travisCiComToken,
                            'Travis-API-Version' => '3',
                        ],
                    ]);
                $travisCiComData = $travisCiComResponse->toArray();
                if ('error' === $travisCiComData['@type'] or false === $travisCiComData['active']) {
                    $output->writeln('Cannot determine TravisCI Type, skipping...');
                    continue;
                }
                $dbProject->setTravisDestination('com');
            } else {
                $dbProject->setTravisDestination('org');
            }

            $this->entityManager->persist($dbProject);
            $this->entityManager->flush();

            $output->writeln('Done with: '.$shortName);
        }

        return Command::SUCCESS;
    }
}

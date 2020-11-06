<?php

namespace App\Command;

use App\Entity\Project;
use App\Entity\ProjectClassification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProjectExportCommand extends Command
{
    private const EXPORT_TARGET = '../ml-model/data/actual.csv';

    private EntityManagerInterface $entityManager;

    public function configure()
    {
        $this->setName('app:project:export');
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        $data = [];

        $projects = $this->entityManager->getRepository(Project::class)->findAll();

        foreach ($projects as $project) {
            /** @var Project $project */
            if ($project->hasValidClassifications() === false) {
                continue;
            }

            $classificationCount = $seriousCount = $tailoredCount = $integratedCount = $interestingCount = $includeCount = 0;
            foreach ($project->getValidClassifications() as $classification) {
                /** @var ProjectClassification $classification */
                $classificationCount += 1;
                $seriousCount += $classification->getSerious() ? 1 : -1;
                $tailoredCount += $classification->getTailored() ? 1 : -1;
                $integratedCount += $classification->getIntegrated() ? 1 : -1;
                $interestingCount += $classification->getInteresting() ? 1 : -1;
                $includeCount += $classification->getInclude() ? 1 : -1;

            }

            $data[] = [
                'name'           => $project->getName(),
                'classification' => $includeCount > 0 ? '1' : '0',
                'include'        => $includeCount,
                'serious'        => $seriousCount,
                'tailored'       => $tailoredCount,
                'integrated'     => $integratedCount,
                'interesting'    => $interestingCount,
                ];
        }

        file_put_contents(self::EXPORT_TARGET, $serializer->encode($data, 'csv'));

        return Command::SUCCESS;
    }
}

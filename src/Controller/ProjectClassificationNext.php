<?php

namespace App\Controller;

use App\Entity\ProjectClassification;
use App\Handler\ProjectClassificationNextHandler;

class ProjectClassificationNext
{
    private ProjectClassificationNextHandler $projectNextHandler;

    public function __construct(ProjectClassificationNextHandler $projectNextHandler)
    {
        $this->projectNextHandler = $projectNextHandler;
    }

    public function __invoke($data): ?ProjectClassification
    {
        return $this->projectNextHandler->handleNext();
    }
}

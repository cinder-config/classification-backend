<?php

namespace App\Handler;

use App\Entity\Project;
use App\Entity\ProjectClassification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class ProjectClassificationNextHandler
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function handleNext(): ?ProjectClassification
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $pendingClassification = $this->entityManager->getRepository(ProjectClassification::class)->findPendingByUser($user);

        if (null !== $pendingClassification) {
            return $pendingClassification;
        }

        $myProjects = array_map(function (ProjectClassification $classification) {
            return $classification->getProject()->getId();
        }, $user->getClassifications()->toArray());

        $project = $this->entityManager->getRepository(Project::class)->findNext($myProjects);

        if (null === $project) {
            return null;
        }

        $classification = new ProjectClassification($project, $user);

        $this->entityManager->persist($classification);
        $this->entityManager->flush();

        $myClassifications[] = $classification->getId();

        return $classification;
    }
}

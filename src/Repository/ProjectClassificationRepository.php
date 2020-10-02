<?php

namespace App\Repository;

use App\Entity\ProjectClassification;
use App\Entity\ProjectClassificationStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectClassificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectClassification::class);
    }

    public function findPendingByUser(User $user): ?ProjectClassification
    {
        $qb = $this->createQueryBuilder('pc')
            ->where('pc.user = :user')
            ->andWhere('pc.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', ProjectClassificationStatus::PENDING)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

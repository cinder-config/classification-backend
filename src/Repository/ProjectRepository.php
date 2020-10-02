<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findNext(array $excludedIds = []): ?Project
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p, COUNT(pc) as cnt')
            ->leftJoin('p.classifications', 'pc')
            ->groupBy('p')
            ->orderBy('cnt', 'ASC')
            ->setMaxResults(1);

        if (!empty($excludedIds)) {
            $qb->where('p.id NOT IN (:ids)')
                ->setParameter('ids', $excludedIds);
        }

        $res = $qb->getQuery()->getOneOrNullResult();

        return isset($res[0]) ? $res[0] : null;
    }
}

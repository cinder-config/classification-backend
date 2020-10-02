<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername(string $accessKey): ?UserInterface
    {
        return $this->createQueryBuilder('u')
            ->where('u.accessKey = :accessKey')
            ->setParameter('accessKey', $accessKey)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

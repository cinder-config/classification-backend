<?php

namespace App\Entity;

use App\Model\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    use Id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $accessKey;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectClassification", mappedBy="user")
     */
    private Collection $classifications;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->accessKey = Uuid::uuid4();
        $this->classifications = new ArrayCollection();
    }

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function getClassifications(): Collection
    {
        return $this->classifications;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->accessKey;
    }

    public function eraseCredentials()
    {
        return false;
    }
}

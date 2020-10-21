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
     * @ORM\Column(type="integer")
     */
    private int $experienceYears;

    /**
     * @ORM\Column(type="integer")
     */
    private int $experienceComparedToOthers;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $usedTravisCI;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $consent;

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

    public function getExperienceYears(): int
    {
        return $this->experienceYears;
    }

    public function setExperienceYears(int $experienceYears): void
    {
        $this->experienceYears = $experienceYears;
    }

    public function getExperienceComparedToOthers(): int
    {
        return $this->experienceComparedToOthers;
    }

    public function setExperienceComparedToOthers(int $experienceComparedToOthers): void
    {
        $this->experienceComparedToOthers = $experienceComparedToOthers;
    }

    public function isUsedTravisCI(): bool
    {
        return $this->usedTravisCI;
    }

    public function setUsedTravisCI(bool $usedTravisCI): void
    {
        $this->usedTravisCI = $usedTravisCI;
    }

    public function isConsent(): bool
    {
        return $this->consent;
    }

    public function setConsent(bool $consent): void
    {
        $this->consent = $consent;
    }
}

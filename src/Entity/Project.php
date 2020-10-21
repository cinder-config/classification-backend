<?php

namespace App\Entity;

use App\Model\Id;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    use Id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="text")
     */
    private string $description;

    /**
     * @ORM\Column(type="text")
     */
    private string $configuration;

    /**
     * @ORM\Column(type="string")
     */
    private string $defaultBranch;

    /**
     * @ORM\Column(type="integer")
     */
    private int $commits;

    /**
     * @ORM\Column(type="integer")
     */
    private int $stars;

    /**
     * @ORM\Column(type="integer")
     */
    private int $forks;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $lastChangeAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ProjectClassification", mappedBy="project")
     */
    private Collection $classifications;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->classifications = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function getDefaultBranch(): string
    {
        return $this->defaultBranch;
    }

    public function setDefaultBranch(string $defaultBranch): void
    {
        $this->defaultBranch = $defaultBranch;
    }

    public function getCommits(): int
    {
        return $this->commits;
    }

    public function setCommits(int $commits): void
    {
        $this->commits = $commits;
    }

    public function getStars(): int
    {
        return $this->stars;
    }

    public function setStars(int $stars): void
    {
        $this->stars = $stars;
    }

    public function getForks(): int
    {
        return $this->forks;
    }

    public function setForks(int $forks): void
    {
        $this->forks = $forks;
    }

    public function getLastChangeAt(): \DateTimeInterface
    {
        return $this->lastChangeAt;
    }

    public function setLastChangeAt(\DateTimeInterface $lastChangeAt): void
    {
        $this->lastChangeAt = $lastChangeAt;
    }

    public function getClassifications(): Collection
    {
        return $this->classifications;
    }

    public function setClassifications(Collection $classifications): void
    {
        $this->classifications = $classifications;
    }
}

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
    private string $configurationUrl;

    /**
     * @ORM\Column(type="string")
     */
    private string $gitUrl;

    /**
     * @ORM\Column(type="string")
     */
    private string $travisCiUrl;

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

    public function getConfigurationUrl(): string
    {
        return $this->configurationUrl;
    }

    public function setConfigurationUrl(string $configurationUrl): void
    {
        $this->configurationUrl = $configurationUrl;
    }

    public function getGitUrl(): string
    {
        return $this->gitUrl;
    }

    public function setGitUrl(string $gitUrl): void
    {
        $this->gitUrl = $gitUrl;
    }

    public function getTravisCiUrl(): string
    {
        return $this->travisCiUrl;
    }

    public function setTravisCiUrl(string $travisCiUrl): void
    {
        $this->travisCiUrl = $travisCiUrl;
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

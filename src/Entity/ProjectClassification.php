<?php

namespace App\Entity;

use App\Model\Id;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectClassificationRepository")
 */
class ProjectClassification
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="classifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Project $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="classifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice(choices={"done","skip","pending"})
     */
    private string $status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $serious = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $tailored = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $interesting = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $include = null;

    public function __construct(Project $project, User $user)
    {
        $this->id = Uuid::uuid4();
        $this->project = $project;
        $this->user = $user;
        $this->status = ProjectClassificationStatus::PENDING;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSerious(): ?bool
    {
        return $this->serious;
    }

    public function setSerious(?bool $serious): void
    {
        $this->serious = $serious;
    }

    public function getTailored(): ?bool
    {
        return $this->tailored;
    }

    public function setTailored(?bool $tailored): void
    {
        $this->tailored = $tailored;
    }

    public function getInteresting(): ?bool
    {
        return $this->interesting;
    }

    public function setInteresting(?bool $interesting): void
    {
        $this->interesting = $interesting;
    }

    public function getInclude(): ?bool
    {
        return $this->include;
    }

    public function setInclude(?bool $include): void
    {
        $this->include = $include;
    }
}

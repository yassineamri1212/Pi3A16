<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReductionRepository;

#[ORM\Entity(repositoryClass: ReductionRepository::class)]
#[ORM\Table(name: 'reductions')]
class Reduction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reductions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: false)]
    private ?string $reduction_percentage = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $valid_until = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    // Getters & Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getReductionPercentage(): ?float
    {
        return $this->reduction_percentage !== null ? (float) $this->reduction_percentage : null;
    }

    public function setReductionPercentage(float $reductionPercentage): self
    {
        $this->reduction_percentage = (string) $reductionPercentage;
        return $this;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->valid_until;
    }

    public function setValidUntil(?\DateTimeInterface $validUntil): self
    {
        $this->valid_until = $validUntil;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PackageRepository;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'package')]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')] // FIXED: Changed from 'decimal' to 'integer'
    private ?int $idPackage = null;

    public function getIdPackage(): ?int
    {
        return $this->idPackage;
    }

    public function setIdPackage(int $idPackage): self
    {
        $this->idPackage = $idPackage;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: false)] // FIXED: Changed from 'decimal' to 'float'
    private ?float $weight = null;

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
}

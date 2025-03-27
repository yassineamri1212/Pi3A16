<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ParcourRepository;

#[ORM\Entity(repositoryClass: ParcourRepository::class)]
#[ORM\Table(name: 'parcours')]
class Parcour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idParcours = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $trajet = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: false)]
    private ?string $distance = null; // Changed to string (Doctrine stores decimals as strings)

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $estimationTemps = null;

    public function getIdParcours(): ?int
    {
        return $this->idParcours;
    }

    public function getTrajet(): ?string
    {
        return $this->trajet;
    }

    public function setTrajet(string $trajet): self
    {
        $this->trajet = $trajet;
        return $this;
    }

    public function getDistance(): ?float
    {
        return (float) $this->distance; // Convert stored string to float when retrieving
    }

    public function setDistance(float $distance): self
    {
        $this->distance = (string) $distance; // Convert float to string before storing
        return $this;
    }

    public function getEstimationTemps(): ?int
    {
        return $this->estimationTemps;
    }

    public function setEstimationTemps(int $estimationTemps): self
    {
        $this->estimationTemps = $estimationTemps;
        return $this;
    }
}

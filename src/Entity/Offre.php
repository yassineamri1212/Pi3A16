<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\OffreRepository;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Table(name: 'offre')]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idOffre = null;

    public function getIdOffre(): ?int
    {
        return $this->idOffre;
    }

    public function setIdOffre(int $idOffre): self
    {
        $this->idOffre = $idOffre;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $idVoiture = null;

    public function getIdVoiture(): ?int
    {
        return $this->idVoiture;
    }

    public function setIdVoiture(int $idVoiture): self
    {
        $this->idVoiture = $idVoiture;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $climatisee = null;

    public function isClimatisee(): ?bool
    {
        return $this->climatisee;
    }

    public function setClimatisee(bool $climatisee): self
    {
        $this->climatisee = $climatisee;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $photo = null;

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type_fuel = null;

    public function getType_fuel(): ?string
    {
        return $this->type_fuel;
    }

    public function setType_fuel(string $type_fuel): self
    {
        $this->type_fuel = $type_fuel;
        return $this;
    }

    public function getTypeFuel(): ?string
    {
        return $this->type_fuel;
    }

    public function setTypeFuel(string $type_fuel): static
    {
        $this->type_fuel = $type_fuel;

        return $this;
    }

}

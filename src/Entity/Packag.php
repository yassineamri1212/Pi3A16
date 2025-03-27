<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PackagRepository;

#[ORM\Entity(repositoryClass: PackagRepository::class)]
#[ORM\Table(name: 'packag')]
class Packag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId_packag(): ?int
    {
        return $this->id_packag;
    }

    public function setId_packag(int $id_packag): self
    {
        $this->id_packag = $id_packag;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $weight_packag = null;

    public function getWeight_packag(): ?int
    {
        return $this->weight_packag;
    }

    public function setWeight_packag(int $weight_packag): self
    {
        $this->weight_packag = $weight_packag;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $description_packag = null;

    public function getDescription_packag(): ?string
    {
        return $this->description_packag;
    }

    public function setDescription_packag(string $description_packag): self
    {
        $this->description_packag = $description_packag;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $id_livrai = null;

    public function getId_livrai(): ?int
    {
        return $this->id_livrai;
    }

    public function setId_livrai(?int $id_livrai): self
    {
        $this->id_livrai = $id_livrai;
        return $this;
    }

    public function getIdPackag(): ?int
    {
        return $this->id_packag;
    }

    public function setIdPackag(int $id_packag): static
    {
        $this->id_packag = $id_packag;

        return $this;
    }

    public function getWeightPackag(): ?int
    {
        return $this->weight_packag;
    }

    public function setWeightPackag(int $weight_packag): static
    {
        $this->weight_packag = $weight_packag;

        return $this;
    }

    public function getDescriptionPackag(): ?string
    {
        return $this->description_packag;
    }

    public function setDescriptionPackag(string $description_packag): static
    {
        $this->description_packag = $description_packag;

        return $this;
    }

    public function getIdLivrai(): ?int
    {
        return $this->id_livrai;
    }

    public function setIdLivrai(?int $id_livrai): static
    {
        $this->id_livrai = $id_livrai;

        return $this;
    }

}

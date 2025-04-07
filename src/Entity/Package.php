<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $weight_packag = null;

    #[ORM\Column(length: 255)]
    private ?string $description_packag = null;

    #[ORM\ManyToOne(inversedBy: 'packages')]
    private ?Livraison $livraison = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLivraison(): ?Livraison
    {
        return $this->livraison;
    }

    public function setLivraison(?Livraison $livraison): static
    {
        $this->livraison = $livraison;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\DBAL\Types\Types; // Import Types
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; // Import Assert

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\Table(name: 'package')] // Explicit table name
class Package
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank(message: 'Weight cannot be empty.')]
    #[Assert\Positive(message: 'Weight must be a positive number.')]
    private ?int $weightPackage = null; // Changed to camelCase (consider unit, e.g., grams?)

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Description cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $descriptionPackage = null; // Changed to camelCase

    #[ORM\ManyToOne(targetEntity: Livraison::class, inversedBy: 'packages')]
    #[ORM\JoinColumn(name: 'livraison_id', referencedColumnName: 'id', nullable: false)] // Explicit JoinColumn, make mandatory
    #[Assert\NotBlank(message: 'Package must be associated with a delivery.')]
    private ?Livraison $livraison = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    // No Setter for ID

    public function getWeightPackage(): ?int
    {
        return $this->weightPackage;
    }

    public function setWeightPackage(int $weightPackage): static
    {
        $this->weightPackage = $weightPackage;
        return $this;
    }

    public function getDescriptionPackage(): ?string
    {
        return $this->descriptionPackage;
    }

    public function setDescriptionPackage(string $descriptionPackage): static
    {
        $this->descriptionPackage = $descriptionPackage;
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

    // __toString for easier identification
    public function __toString(): string
    {
        return sprintf('Package #%d (%d units, %s)', // Adjust unit if weight isn't 'units'
            $this->id ?? 'New',
            $this->weightPackage ?? 0,
            $this->descriptionPackage ? '"' . substr($this->descriptionPackage, 0, 20) . '..."' : 'N/A'
        );
    }
}
<?php

namespace App\Entity;

use App\Repository\ParcourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcourRepository::class)]
#[ORM\Table(name: 'parcours')] // Changed table name to plural 'parcours' as is common practice
class Parcour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idParcours = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The route description (trajet) cannot be empty.")]
    #[Assert\Length(max: 255, maxMessage: "The route description cannot be longer than {{ limit }} characters.")]
    private ?string $trajet = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Distance cannot be empty.")]
    #[Assert\PositiveOrZero(message: "Distance must be zero or a positive number.")]
    private ?string $distance = null; // Doctrine handles DECIMAL as string

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank(message: "Estimated time cannot be empty.")]
    #[Assert\Positive(message: "Estimated time must be a positive number of minutes.")]
    private ?int $estimationTemps = null; // Assuming value is in minutes

    /**
     * One Parcour can be associated with many Offres.
     * 'cascade: ['persist']' means if you persist a Parcour, associated new Offres might also be persisted (optional).
     * 'orphanRemoval: true' means if an Offre is removed from this Parcour's collection, it gets deleted from DB (use cautiously).
     */
    #[ORM\OneToMany(mappedBy: 'parcour', targetEntity: Offre::class, cascade: ['persist'])]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

    public function getIdParcours(): ?int
    {
        return $this->idParcours;
    }

    // No setter for ID

    public function getTrajet(): ?string
    {
        return $this->trajet;
    }

    public function setTrajet(string $trajet): static
    {
        $this->trajet = $trajet;
        return $this;
    }

    /**
     * Gets the distance as stored (string).
     */
    public function getDistance(): ?string
    {
        return $this->distance;
    }

    /**
     * Sets the distance (expects a string representation of the decimal).
     */
    public function setDistance(string $distance): static
    {
        // Basic check if it looks like a valid non-negative number string
        if (!is_numeric($distance) || (float)$distance < 0) {
            throw new \InvalidArgumentException("Distance must be a non-negative numeric value.");
        }
        $this->distance = $distance;
        return $this;
    }

    /**
     * Helper method to get distance as a float for calculations or display formatting.
     */
    public function getDistanceAsFloat(): ?float
    {
        return $this->distance === null ? null : (float)$this->distance;
    }

    /**
     * Helper method to set distance from a float value.
     */
    public function setDistanceFromFloat(float $distance): static
    {
        if ($distance < 0) {
            throw new \InvalidArgumentException("Distance cannot be negative.");
        }
        $this->distance = (string)$distance;
        return $this;
    }


    public function getEstimationTemps(): ?int
    {
        return $this->estimationTemps;
    }

    public function setEstimationTemps(int $estimationTemps): static
    {
        $this->estimationTemps = $estimationTemps;
        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setParcour($this); // Set the owning side
        }
        return $this;
    }

    public function removeOffre(Offre $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getParcour() === $this) {
                $offre->setParcour(null);
            }
        }
        return $this;
    }

    /**
     * String representation used in things like dropdowns.
     */
    public function __toString(): string
    {
        return $this->trajet ?? 'New Parcour';
    }
}
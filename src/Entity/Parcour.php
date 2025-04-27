<?php

namespace App\Entity;

use App\Repository\ParcourRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcourRepository::class)]
#[ORM\Table(name: 'parcours')]
class Parcour
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idParcours = null;

    // --- NEW FIELDS ---
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Parcour name cannot be empty.")]
    #[Assert\Length(max: 255)]
    private ?string $name = null; // Equivalent to Java 'name'

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Pickup location cannot be empty.")]
    #[Assert\Length(max: 255)]
    private ?string $pickup = null; // Equivalent to Java 'pickup'

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Destination location cannot be empty.")]
    #[Assert\Length(max: 255)]
    private ?string $destination = null; // Equivalent to Java 'destination'

    #[ORM\Column(type: Types::FLOAT, nullable: true)] // Use FLOAT for coordinates, allow null initially
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'Latitude must be between -90 and 90.')]
    private ?float $latitudePickup = null; // Equivalent to Java 'latPickup'

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'Longitude must be between -180 and 180.')]
    private ?float $longitudePickup = null; // Equivalent to Java 'lngPickup'

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'Latitude must be between -90 and 90.')]
    private ?float $latitudeDestination = null; // Equivalent to Java 'latDest'

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'Longitude must be between -180 and 180.')]
    private ?float $longitudeDestination = null; // Equivalent to Java 'lngDest'
    // --- END NEW FIELDS ---


    // --- EXISTING/MODIFIED FIELDS ---
    // Keeping DECIMAL for potential non-integer distance (e.g., 10.5 km)
    // The Java version used int, adjust your interpretation if needed.
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)] // Made nullable temporarily? Or Assert NotBlank?
    #[Assert\PositiveOrZero(message: "Distance must be zero or a positive number.")]
    private ?string $distance = null; // Equivalent to Java 'distance', stored as string

    #[ORM\Column(type: Types::INTEGER, nullable: true)] // Renamed from estimationTemps, made nullable temporarily? Or Assert NotBlank?
    #[Assert\Positive(message: "Estimated time must be a positive number.")]
    private ?int $time = null; // Equivalent to Java 'time' (assuming minutes)
    // --- END EXISTING/MODIFIED FIELDS ---


    // --- REMOVED FIELD ---
    // private ?string $trajet = null; // This is replaced by name, pickup, destination
    // --- END REMOVED FIELD ---


    // --- RELATIONSHIP (Unchanged) ---
    #[ORM\OneToMany(mappedBy: 'parcour', targetEntity: Offre::class, cascade: ['persist'])]
    private Collection $offres;
    // --- END RELATIONSHIP ---


    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }

    // --- GETTERS AND SETTERS ---

    public function getIdParcours(): ?int
    {
        return $this->idParcours;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPickup(): ?string
    {
        return $this->pickup;
    }

    public function setPickup(string $pickup): static
    {
        $this->pickup = $pickup;
        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): static
    {
        $this->destination = $destination;
        return $this;
    }

    public function getLatitudePickup(): ?float
    {
        return $this->latitudePickup;
    }

    public function setLatitudePickup(?float $latitudePickup): static
    {
        $this->latitudePickup = $latitudePickup;
        return $this;
    }

    public function getLongitudePickup(): ?float
    {
        return $this->longitudePickup;
    }

    public function setLongitudePickup(?float $longitudePickup): static
    {
        $this->longitudePickup = $longitudePickup;
        return $this;
    }

    public function getLatitudeDestination(): ?float
    {
        return $this->latitudeDestination;
    }

    public function setLatitudeDestination(?float $latitudeDestination): static
    {
        $this->latitudeDestination = $latitudeDestination;
        return $this;
    }

    public function getLongitudeDestination(): ?float
    {
        return $this->longitudeDestination;
    }

    public function setLongitudeDestination(?float $longitudeDestination): static
    {
        $this->longitudeDestination = $longitudeDestination;
        return $this;
    }

    public function getDistance(): ?string
    {
        return $this->distance;
    }

    public function setDistance(?string $distance): static
    {
        $this->distance = $distance;
        return $this;
    }

    public function getDistanceAsFloat(): ?float
    {
        return $this->distance === null ? null : (float)$this->distance;
    }

    public function setDistanceAsFloat(?float $distance): static
    {
        $this->distance = $distance === null ? null : (string)$distance;
        return $this;
    }

    public function getTime(): ?int // Renamed from getEstimationTemps
    {
        return $this->time;
    }

    public function setTime(?int $time): static // Renamed from setEstimationTemps
    {
        $this->time = $time;
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
            $offre->setParcour($this);
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

    public function __toString(): string
    {
        // Use the new name, or fallback to pickup/destination
        return $this->name ?? ($this->pickup && $this->destination ? sprintf('%s to %s', $this->pickup, $this->destination) : 'New Parcour');
    }
}
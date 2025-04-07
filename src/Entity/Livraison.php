<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types; // Import Types
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert; // Import Assert

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
#[ORM\Table(name: 'livraison')] // Explicit table name
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Start location cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $startLocation = null; // Changed to camelCase

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Delivery location cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $deliveryLocation = null; // Changed to camelCase

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])] // Set DB default
    private ?bool $isDelivered = false; // Changed to camelCase, default to false

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(
        mappedBy: 'livraison',          // Matches property name in Package entity
        targetEntity: Package::class,
        cascade: ['persist', 'remove'], // Persist new packages with livraison, remove packages when livraison is removed
        orphanRemoval: true             // Delete packages from DB if removed from this collection
    )]
    private Collection $packages;

    // --- Missing properties from your original error description ---
    // Add these if they actually exist in your DB table / older code
    // #[ORM\Column(length: 100, nullable: true)]
    // private ?string $nomDestinataire = null;

    // #[ORM\Column(length: 100, nullable: true)]
    // private ?string $prenomDestinataire = null;

    // #[ORM\Column(length: 50, nullable: true)]
    // private ?string $status = null; // e.g., 'pending', 'in_transit', 'delivered'

    // #[ORM\Column(length: 20, nullable: true)]
    // private ?string $numTelephone = null;

    // #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    // private ?\DateTimeInterface $dateLivraisonPrevu = null;

    // --- End Missing properties ---

    public function __construct()
    {
        $this->packages = new ArrayCollection();
        $this->isDelivered = false; // Ensure default in constructor too
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    // No Setter for ID

    public function getStartLocation(): ?string
    {
        return $this->startLocation;
    }

    public function setStartLocation(string $startLocation): static
    {
        $this->startLocation = $startLocation;
        return $this;
    }

    public function getDeliveryLocation(): ?string
    {
        return $this->deliveryLocation;
    }

    public function setDeliveryLocation(string $deliveryLocation): static
    {
        $this->deliveryLocation = $deliveryLocation;
        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): static
    {
        $this->isDelivered = $isDelivered;
        return $this;
    }

    /**
     * @return Collection<int, Package>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(Package $package): static
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setLivraison($this); // Set the owning side
        }
        return $this;
    }

    public function removePackage(Package $package): static
    {
        // The orphanRemoval=true handles the DB deletion when flushing
        if ($this->packages->removeElement($package)) {
            // Optional: Set the owning side to null if needed immediately,
            // but orphanRemoval handles the primary use case upon flush.
            // if ($package->getLivraison() === $this) {
            //     $package->setLivraison(null);
            // }
        }
        return $this;
    }

    // --- Add Getters/Setters for missing properties if you uncomment them above ---
    // e.g., getNomDestinataire(), setNomDestinataire(), getStatus(), setStatus() etc.
    // --- End Getters/Setters ---


    // --- CORRECTED: Only ONE __toString method ---
    /**
     * Returns a string representation of the Livraison object.
     * Used by default in EntityType forms if 'choice_label' is not specified.
     *
     * @return string
     */
    public function __toString(): string
    {
        // This version uses properties defined in this entity file
        return sprintf('Delivery #%d (%s to %s)',
            $this->getId() ?? 'New', // Use getId()
            $this->getStartLocation() ?? 'N/A', // Use getStartLocation()
            $this->getDeliveryLocation() ?? 'N/A' // Use getDeliveryLocation()
        );

        // NOTE: The other __toString using getStatus() was removed because
        // the 'status' property and its getter were commented out.
        // If you add the 'status' property back, you could use that version instead.
    }
    // --- END __toString method ---

} // End class Livraison
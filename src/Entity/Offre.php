<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File; // Required for VichUploader
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich; // Required for VichUploader

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Table(name: 'offre')] // Explicitly set table name if it differs from 'offre'
#[Vich\Uploadable] // Enable VichUploader for this entity
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idOffre = null;

    // --- Relationship to Parcour (Many Offres can have One Parcour) ---
    #[ORM\ManyToOne(targetEntity: Parcour::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(name: 'id_parcours', referencedColumnName: 'id_parcours', nullable: false)] // Adjust column names if needed
    #[Assert\NotBlank(message: "Please select a parcour.")]
    private ?Parcour $parcour = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $climatisee = false; // Default value

    // --- Photo Upload Handling (using VichUploader) ---
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null; // Stores the filename

    // NOTE: Mapping name 'offre_photos' must match config in vich_uploader.yaml
    #[Vich\UploadableField(mapping: 'offre_photos', fileNameProperty: 'photo')]
    #[Assert\Image(
        maxSize: '2M', // Example max size
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, GIF).'
    )]
    private ?File $photoFile = null; // Virtual property for the form

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null; // Needed for VichUploader timestamping behavior
    // --- End Photo Upload Handling ---

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Fuel type cannot be empty.")]
    #[Assert\Length(max: 50)]
    private ?string $typeFuel = null; // Changed from type_fuel to camelCase convention

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank(message: "Number of seats cannot be empty.")]
    #[Assert\Positive(message: "Number of seats must be positive.")]
    private ?int $nombrePlaces = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Price cannot be empty.")]
    #[Assert\PositiveOrZero(message: "Price cannot be negative.")]
    private ?string $prix = null; // Doctrine uses string for DECIMAL

    #[ORM\Column(type: Types::DATETIME_MUTABLE)] // Use DATETIME_MUTABLE if you might modify the date object later
    #[Assert\NotBlank(message: "Departure date/time cannot be empty.")]
    #[Assert\GreaterThan("now", message: "Departure time must be in the future.")] // Basic validation
    private ?\DateTimeInterface $dateDepart = null;

    // --- Getters and Setters ---

    public function getIdOffre(): ?int
    {
        return $this->idOffre;
    }

    public function getParcour(): ?Parcour
    {
        return $this->parcour;
    }

    public function setParcour(?Parcour $parcour): static
    {
        $this->parcour = $parcour;
        return $this;
    }

    public function isClimatisee(): ?bool
    {
        return $this->climatisee;
    }

    public function setClimatisee(bool $climatisee): static
    {
        $this->climatisee = $climatisee;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    // --- Methods for VichUploaderBundle ---
    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;
        if (null !== $photoFile) {
            // It is required that at least one field changes if you are using Doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    // --- End Methods for VichUploaderBundle ---

    public function getTypeFuel(): ?string
    {
        return $this->typeFuel;
    }

    public function setTypeFuel(string $typeFuel): static
    {
        $this->typeFuel = $typeFuel;
        return $this;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;
        return $this;
    }

    public function getPrix(): ?string // Keep getter/setter consistent with Doctrine type
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static // Use string type hint consistent with property
    {
        // Validation handled by Assert constraints
        $this->prix = $prix;
        return $this;
    }

    // Helper to get price as float
    public function getPrixAsFloat(): ?float
    {
        return $this->prix === null ? null : (float)$this->prix;
    }

    // Helper to set price from float (use in form type potentially if needed)
    public function setPrixFromFloat(float $prix): static
    {
        $this->prix = (string)$prix;
        return $this;
    }


    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;
        return $this;
    }

    // Useful for choice lists or debugging
    public function __toString(): string
    {
        // Example: "Offer #123 (Paris to Lyon on 2024-07-15 10:00)"
        return sprintf(
            'Offer #%d (%s on %s)',
            $this->idOffre ?? 'New',
            $this->parcour ? $this->parcour->getTrajet() : 'N/A',
            $this->dateDepart ? $this->dateDepart->format('Y-m-d H:i') : 'N/A'
        );
    }
}
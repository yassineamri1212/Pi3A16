<?php

namespace App\Entity;

use App\Repository\OffreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: OffreRepository::class)]
#[ORM\Table(name: 'offre')]
#[Vich\Uploadable]
class Offre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idOffre = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'createdOffres')]
    #[ORM\JoinColumn(name: 'conducteur_id', referencedColumnName: 'id', nullable: false)]
    private ?User $conducteur = null;

    #[ORM\ManyToOne(targetEntity: Parcour::class, inversedBy: 'offres')]
    #[ORM\JoinColumn(name: 'id_parcours', referencedColumnName: 'id_parcours', nullable: false)]
    #[Assert\NotBlank(message: "Please select a parcour.")]
    private ?Parcour $parcour = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $climatisee = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[Vich\UploadableField(mapping: 'offre_photos', fileNameProperty: 'photo')]
    #[Assert\Image(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png', 'image/gif'])]
    private ?File $photoFile = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Fuel type cannot be empty.")]
    #[Assert\Length(max: 50)]
    private ?string $typeFuel = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotBlank(message: "Number of seats cannot be empty.")]
    #[Assert\Positive(message: "Number of seats must be positive.")]
    private ?int $nombrePlaces = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Price cannot be empty.")]
    #[Assert\PositiveOrZero(message: "Price cannot be negative.")]
    private ?string $prix = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "Departure date/time cannot be empty.")]
    #[Assert\GreaterThan("now", message: "Departure time must be in the future.")]
    private ?\DateTimeInterface $dateDepart = null;

    // --- Relation to Reservations for this Offer ---
    // <<< CORRECTED: targetEntity is ReservationOffer >>>
    #[ORM\OneToMany(mappedBy: 'offre', targetEntity: ReservationOffer::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->dateDepart = new \DateTime();
        $this->reservations = new ArrayCollection();
    }

    // --- Getters and Setters (Unchanged from your version) ---
    public function getIdOffre(): ?int { return $this->idOffre; }
    public function getConducteur(): ?User { return $this->conducteur; }
    public function setConducteur(?User $conducteur): static { $this->conducteur = $conducteur; return $this; }
    public function getParcour(): ?Parcour { return $this->parcour; }
    public function setParcour(?Parcour $parcour): static { $this->parcour = $parcour; return $this; }
    public function isClimatisee(): ?bool { return $this->climatisee; }
    public function setClimatisee(?bool $climatisee): static { $this->climatisee = $climatisee ?? false; return $this; }
    public function getPhoto(): ?string { return $this->photo; }
    public function setPhoto(?string $photo): static { $this->photo = $photo; return $this; }
    public function setPhotoFile(?File $photoFile = null): void { $this->photoFile = $photoFile; if (null !== $photoFile) { $this->updatedAt = new \DateTimeImmutable(); } }
    public function getPhotoFile(): ?File { return $this->photoFile; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void { $this->updatedAt = $updatedAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getTypeFuel(): ?string { return $this->typeFuel; }
    public function setTypeFuel(?string $typeFuel): static { $this->typeFuel = $typeFuel; return $this; }
    public function getNombrePlaces(): ?int { return $this->nombrePlaces; }
    public function setNombrePlaces(?int $nombrePlaces): static { $this->nombrePlaces = $nombrePlaces; return $this; }
    public function getPrix(): ?string { return $this->prix; }
    public function setPrix(?string $prix): static { $this->prix = $prix; return $this; }
    public function getPrixAsFloat(): ?float { return $this->prix === null ? null : (float)$this->prix; }
    public function setPrixFromFloat(float $prix): static { $this->prix = (string)$prix; return $this; }
    public function getDateDepart(): ?\DateTimeInterface { return $this->dateDepart; }
    public function setDateDepart(?\DateTimeInterface $dateDepart): static { $this->dateDepart = $dateDepart; return $this; }

    // --- Methods for Reservations ---
    /**
     * <<< CORRECTED: Return type hint for ReservationOffer >>>
     * @return Collection<int, ReservationOffer>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    // <<< CORRECTED: Type hint for ReservationOffer >>>
    public function addReservation(ReservationOffer $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setOffre($this);
        }
        return $this;
    }

    // <<< CORRECTED: Type hint for ReservationOffer >>>
    public function removeReservation(ReservationOffer $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getOffre() === $this) {
                $reservation->setOffre(null);
            }
        }
        return $this;
    }
    // --- END CORRECTION ---

    // --- Helper method to get remaining places (Unchanged) ---
    public function getRemainingPlaces(): int
    {
        $reservedCount = $this->getReservations()->filter(fn(ReservationOffer $r) => $r->getStatus() === 'confirmed')->count(); // Count only confirmed
        $totalPlaces = $this->getNombrePlaces() ?? 0;
        $remaining = $totalPlaces - $reservedCount;
        return max(0, $remaining);
    }

    public function __toString(): string
    {
        $dateString = $this->dateDepart ? $this->dateDepart->format('Y-m-d H:i') : 'N/A';
        return sprintf(
            'Offer #%d (%s on %s)',
            $this->idOffre ?? 'New',
            $this->parcour ? $this->parcour->getTrajet() : 'N/A',
            $dateString
        );
    }
}
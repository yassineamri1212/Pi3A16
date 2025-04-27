<?php

namespace App\Entity;

use App\Repository\ReservationOfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationOfferRepository::class)]
#[ORM\Table(name: 'reservation_offer')] // <<< CORRECTED TABLE NAME >>>
#[ORM\UniqueConstraint(name: 'UNIQ_RESERVATION_OFFRE_PASSENGER', columns: ['offre_id', 'passenger_id'])]
class ReservationOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Offre::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(
        name: 'offre_id',
        referencedColumnName: 'id_offre', // <<< CORRECTED REFERENCE >>>
        nullable: false
    )]
    private ?Offre $offre = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservationsAsPassenger')]
    #[ORM\JoinColumn(
        name: 'passenger_id',
        referencedColumnName: 'id', // <<< ADDED explicit reference >>>
        nullable: false
    )]
    private ?User $passenger = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ["default" => "confirmed"])]
    private ?string $status = 'confirmed';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'confirmed';
    }

    // --- Getters and Setters (Unchanged from your version) ---
    public function getId(): ?int { return $this->id; }
    public function getOffre(): ?Offre { return $this->offre; }
    public function setOffre(?Offre $offre): static { $this->offre = $offre; return $this; }
    public function getPassenger(): ?User { return $this->passenger; }
    public function setPassenger(?User $passenger): static { $this->passenger = $passenger; return $this; }
    public function getStatus(): ?string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}
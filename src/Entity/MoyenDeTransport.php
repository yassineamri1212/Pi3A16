<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity] // Add repositoryClass if you have one: repositoryClass: App\Repository\MoyenDeTransportRepository::class
#[ORM\Table(name: 'moyen_de_transport')]
class MoyenDeTransport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    // Assertions for Price (Required, Positive)
    #[Assert\NotNull(message: 'The price is required.')] // Use NotNull for non-string types if they MUST be provided
    #[Assert\Positive(message: 'The price must be a positive number.')]
    private ?int $prix = null;

    #[ORM\Column(type: 'string', length: 255)]
    // Assertions for Type (Required, Length)
    #[Assert\NotBlank(message: 'The transport type cannot be empty.')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'The type must be at least {{ limit }} characters long.', maxMessage: 'The type cannot be longer than {{ limit }} characters.')]
    private ?string $type = null;

    #[ORM\Column(type: 'integer')]
    // Assertions for Seats (Required, Positive)
    #[Assert\NotNull(message: 'The number of seats is required.')]
    #[Assert\Positive(message: 'The number of seats must be greater than zero.')]
    private ?int $nbrePlaces = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'moyenDeTransports')]
    #[ORM\JoinColumn(name: 'evenement_id', referencedColumnName: 'id', nullable: true)] // Allow null if transport can exist without event
    private ?Evenement $evenement = null;

    // ---> NEW PROPERTY - Making it Required based on instruction <---
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)] // Made NOT nullable
    #[Assert\NotBlank(message: "Please provide a departure point.")] // Required validation
    #[Assert\Length(max: 255, maxMessage: "Departure point description cannot exceed {{ limit }} characters.")]
    private ?string $pointDepart = null; // Keep ? for Doctrine initialization, but logic treats as non-null
    // ---> END NEW PROPERTY <---

    // --- Getters and Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(?int $prix): self // Allow null input to trigger NotNull validation if needed
    {
        $this->prix = $prix;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self // Allow null input to trigger NotBlank validation if needed
    {
        $this->type = $type;
        return $this;
    }

    public function getNbrePlaces(): ?int
    {
        return $this->nbrePlaces;
    }

    public function setNbrePlaces(?int $nbrePlaces): self // Allow null input
    {
        $this->nbrePlaces = $nbrePlaces;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    // ---> NEW GETTER/SETTER <---
    public function getPointDepart(): ?string
    {
        return $this->pointDepart;
    }

    public function setPointDepart(?string $pointDepart): static // Allow null input
    {
        $this->pointDepart = $pointDepart;
        return $this;
    }
    // ---> END NEW GETTER/SETTER <---
}
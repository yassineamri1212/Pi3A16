<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types; // Added for consistency
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReponseRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
#[ORM\Table(name: 'reponse')]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)] // Use MUTABLE if setting default in constructor
    #[Assert\NotNull(message: 'The date is required.')] // Usually handled by constructor
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Assert\NotBlank(message: 'The response text cannot be empty.')]
    #[Assert\Length(min: 5, minMessage: 'Response must be at least {{ limit }} characters long.')] // Example minimum length
    private ?string $reponse = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'reponses')] // Removed extra '\' from Reclamation::class
    #[ORM\JoinColumn(name: 'reclamation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')] // Added column name explicitly
    #[Assert\NotNull(message: 'The associated reclamation is required.')]
    private ?Reclamation $reclamation = null; // Corrected target entity type hint

    #[ORM\Column(type: Types::INTEGER, nullable: false)] // Assuming this ID refers to the admin user who responded
    #[Assert\NotNull(message: 'The user identifier is required.')]
    #[Assert\Positive(message: 'User ID must be a positive number.')]
    private ?int $utilisateur_id = null; // This should ideally be a ManyToOne relationship to your User entity

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'The username cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $username = null; // Username of the admin who responded

    // --- Constructor to set default date ---
    public function __construct()
    {
        $this->date = new \DateTime();
    }

    // --- Getters and Setters ---
    // (Setters adjusted slightly to allow null for NotBlank/NotNull validation triggering)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self // Allow null? Constructor sets default.
    {
        $this->date = $date;
        return $this;
    }

    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): self // Allow null for NotBlank validation
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getReclamation(): ?Reclamation // Corrected return type hint
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self // Corrected parameter type hint
    {
        $this->reclamation = $reclamation;
        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateur_id;
    }

    public function setUtilisateurId(?int $utilisateur_id): self // Allow null for NotNull validation
    {
        $this->utilisateur_id = $utilisateur_id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self // Allow null for NotBlank validation
    {
        $this->username = $username;
        return $this;
    }
}
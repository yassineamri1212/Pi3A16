<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\ReclamationRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamation')]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    // --- Made nom nullable, removed NotBlank ---
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    // --- Made prenom nullable, removed NotBlank ---
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $prenom = null;

    // --- Email still required ---
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'The email cannot be empty.')]
    #[Assert\Email(message: 'Please provide a valid email address.')]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    // --- Made numTele nullable, removed NotNull/Positive ---
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    // Add Assert\Regex if you want a format check when provided
    private ?int $numTele = null;

    // --- Etat still required ---
    #[ORM\Column(type: Types::STRING, length: 50, nullable: false)]
    #[Assert\NotBlank(message: 'The status cannot be empty.')]
    #[Assert\Length(max: 50)]
    private ?string $etat = null;

    // --- Sujet still required ---
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'The subject cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $sujet = null;

    // --- Description still required ---
    #[ORM\Column(type: Types::TEXT, nullable: false)]
    #[Assert\NotBlank(message: 'The description cannot be empty.')]
    #[Assert\Length(min: 10, minMessage: 'Description must be at least {{ limit }} characters long.')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    #[Assert\NotNull(message: 'The date is required.')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $utilisateur_id = null;

    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: \App\Entity\Reponse::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->date = new \DateTime();
        $this->etat = 'Pending'; // Default status
    }

    // --- GETTERS AND SETTERS ---
    // (No changes needed here, they already accept/return nullables)

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getNumTele(): ?int { return $this->numTele; }
    public function setNumTele(?int $numTele): self { $this->numTele = $numTele; return $this; }

    public function getEtat(): ?string { return $this->etat; }
    public function setEtat(?string $etat): self { $this->etat = $etat; return $this; }

    public function getSujet(): ?string { return $this->sujet; }
    public function setSujet(?string $sujet): self { $this->sujet = $sujet; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(?\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getUtilisateurId(): ?int { return $this->utilisateur_id; }
    public function setUtilisateurId(?int $utilisateur_id): self { $this->utilisateur_id = $utilisateur_id; return $this; }

    /** @return Collection<int, Reponse> */
    public function getReponses(): Collection { return $this->reponses; }
}
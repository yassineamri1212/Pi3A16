<?php
namespace App\Entity;

// *** REMOVED Types::DATETIME_IMMUTABLE if no longer needed by other fields ***
// *** Kept Types::STRING, Types::BOOLEAN, Types::INTEGER, Types::JSON ***
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\UserRepository; // Ensure this is correct
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'An account with this email already exists.')]
#[UniqueEntity(fields: ['userName'], message: 'This username is already taken.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: "Email cannot be empty.")]
    #[Assert\Email(message: "The email \`{{ value }}\` is not a valid email.")]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', unique: true)]
    #[Assert\NotBlank(message: "Username cannot be empty.")]
    #[Assert\Length(min: 3, max: 50, minMessage: "Username must be at least \`{{ limit }}\` characters long.", maxMessage: "Username cannot be longer than \`{{ limit }}\` characters.")]
    private ?string $userName = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    // This property is typically used temporarily by forms, not mapped to DB
    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private bool $isBlocked = false;

    // --- REMOVED Password Reset Fields ---
    // private ?string $resetToken = null; // REMOVED
    // private ?\DateTimeImmutable $resetTokenExpiresAt = null; // REMOVED
    // --- END REMOVED FIELDS ---

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Reduction::class)]
    private Collection $reductions;

    #[ORM\ManyToMany(targetEntity: \App\Entity\MoyenDeTransport::class)]
    #[ORM\JoinTable(name: 'user_transport_reservations')]
    private Collection $reservedTransports;

    #[ORM\OneToMany(mappedBy: 'conducteur', targetEntity: Offre::class)]
    private Collection $createdOffres;

    #[ORM\OneToMany(mappedBy: 'passenger', targetEntity: ReservationOffer::class, orphanRemoval: true)]
    private Collection $reservationsAsPassenger;

    public function __construct()
    {
        $this->reductions = new ArrayCollection();
        $this->reservedTransports = new ArrayCollection();
        $this->createdOffres = new ArrayCollection();
        $this->reservationsAsPassenger = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->isBlocked = false;
    }

    // --- Existing Getters/Setters (Unchanged) ---
    public function getId(): ?int { return $this->id; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getUserIdentifier(): string { return (string) $this->email; }
    public function getUserName(): ?string { return $this->userName; }
    public function setUserName(?string $userName): static { $this->userName = $userName; return $this; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }
    public function getPlainPassword(): ?string { return $this->plainPassword; }
    public function setPlainPassword(?string $plainPassword): static { $this->plainPassword = $plainPassword; return $this; }
    public function eraseCredentials(): void { $this->plainPassword = null; }
    public function isBlocked(): bool { return $this->isBlocked; }
    public function setBlocked(bool $isBlocked): static { $this->isBlocked = $isBlocked; return $this; }
    public function getReductions(): Collection { return $this->reductions; }
    public function getReservedTransports(): Collection { return $this->reservedTransports; }
    public function getRoles(): array { $roles = $this->roles; $roles[] = 'ROLE_USER'; return array_unique($roles); }
    public function setRoles(array $roles): static { $this->roles = array_values(array_filter($roles)); return $this; }
    public function addReservedTransport(\App\Entity\MoyenDeTransport $transport): static { if (!$this->reservedTransports->contains($transport)) { $this->reservedTransports->add($transport); } return $this; }
    public function removeReservedTransport(\App\Entity\MoyenDeTransport $transport): static { $this->reservedTransports->removeElement($transport); return $this; }
    public function addReduction(Reduction $reduction): static { if (!$this->reductions->contains($reduction)) { $this->reductions->add($reduction); $reduction->setUser($this); } return $this; }
    public function removeReduction(Reduction $reduction): static { if ($this->reductions->removeElement($reduction)) { if ($reduction->getUser() === $this) { $reduction->setUser(null); } } return $this; }
    /** @return Collection<int, Offre> */
    public function getCreatedOffres(): Collection { return $this->createdOffres; }
    public function addCreatedOffre(Offre $offre): static { if (!$this->createdOffres->contains($offre)) { $this->createdOffres->add($offre); $offre->setConducteur($this); } return $this; }
    public function removeCreatedOffre(Offre $offre): static { if ($this->createdOffres->removeElement($offre)) { if ($offre->getConducteur() === $this) { $offre->setConducteur(null); } } return $this; }
    /** @return Collection<int, ReservationOffer> */
    public function getReservationsAsPassenger(): Collection { return $this->reservationsAsPassenger; }
    public function addReservationsAsPassenger(ReservationOffer $reservation): static { if (!$this->reservationsAsPassenger->contains($reservation)) { $this->reservationsAsPassenger->add($reservation); $reservation->setPassenger($this); } return $this; }
    public function removeReservationsAsPassenger(ReservationOffer $reservation): static { if ($this->reservationsAsPassenger->removeElement($reservation)) { if ($reservation->getPassenger() === $this) { $reservation->setPassenger(null); } } return $this; }
    public function getComputedReservationCode(int $transportId): string { return md5((string) $this->getId() . '_' . $transportId); }

    // --- REMOVED Password Reset Getters/Setters ---
    // public function getResetToken(): ?string ... // REMOVED
    // public function setResetToken(?string $resetToken): static ... // REMOVED
    // public function getResetTokenExpiresAt(): ?\DateTimeImmutable ... // REMOVED
    // public function setResetTokenExpiresAt(?\DateTimeImmutable $resetTokenExpiresAt): static ... // REMOVED
    // public function isPasswordResetTokenValid(?\DateTimeImmutable $now = null): bool ... // REMOVED
    // --- END REMOVED ---
}
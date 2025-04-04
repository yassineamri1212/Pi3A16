<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Repository\UserRepository;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', unique: true)]
    private ?string $userName = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\OneToMany(targetEntity: Reduction::class, mappedBy: 'user')]
    private Collection $reductions;

    #[ORM\ManyToMany(targetEntity: \App\Entity\MoyenDeTransport::class)]
    #[ORM\JoinTable(name: 'user_transport_reservations')]
    private Collection $reservedTransports;

    public function __construct()
    {
        $this->reductions = new ArrayCollection();
        $this->reservedTransports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function setUserName(string $userName): static
    {
        $this->userName = $userName;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getReductions(): Collection
    {
        return $this->reductions;
    }

    public function addReduction(Reduction $reduction): static
    {
        if (!$this->reductions->contains($reduction)) {
            $this->reductions->add($reduction);
            $reduction->setUser($this);
        }
        return $this;
    }

    public function removeReduction(Reduction $reduction): static
    {
        if ($this->reductions->removeElement($reduction)) {
            if ($reduction->getUser() === $this) {
                $reduction->setUser(null);
            }
        }
        return $this;
    }

    public function getReservedTransports(): Collection
    {
        return $this->reservedTransports;
    }

    public function addReservedTransport(\App\Entity\MoyenDeTransport $transport): static
    {
        if (!$this->reservedTransports->contains($transport)) {
            $this->reservedTransports->add($transport);
        }
        return $this;
    }

    public function removeReservedTransport(\App\Entity\MoyenDeTransport $transport): static
    {
        $this->reservedTransports->removeElement($transport);
        return $this;
    }

    /**
     * Computes a secure unique reservation code based on the user ID, transport ID, and a secret key.
     *
     * @param int $transportId
     * @return string
     */
    public function getComputedReservationCode(int $transportId): string
    {
        $secret = 'YourSecretKeyHere'; // Change this secret as needed.
        $data = ($this->id ?? 0) . $transportId . $secret;
        $hash = hash('sha256', $data);
        return strtoupper(substr($hash, 0, 8));
    }
}
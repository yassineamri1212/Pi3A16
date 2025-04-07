<?php

namespace App\Entity;

use App\Repository\LivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $start_location = null;

    #[ORM\Column(length: 255)]
    private ?string $delivery_location = null;

    #[ORM\Column]
    private ?bool $is_delivered = null;

    /**
     * @var Collection<int, Package>
     */
    #[ORM\OneToMany(targetEntity: Package::class, mappedBy: 'livraison')]
    private Collection $packages;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartLocation(): ?string
    {
        return $this->start_location;
    }

    public function setStartLocation(string $start_location): static
    {
        $this->start_location = $start_location;

        return $this;
    }

    public function getDeliveryLocation(): ?string
    {
        return $this->delivery_location;
    }

    public function setDeliveryLocation(string $delivery_location): static
    {
        $this->delivery_location = $delivery_location;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->is_delivered;
    }

    public function setIsDelivered(bool $is_delivered): static
    {
        $this->is_delivered = $is_delivered;

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
            $package->setLivraison($this);
        }

        return $this;
    }

    public function removePackage(Package $package): static
    {
        if ($this->packages->removeElement($package)) {
            // set the owning side to null (unless already changed)
            if ($package->getLivraison() === $this) {
                $package->setLivraison(null);
            }
        }

        return $this;
    }
}

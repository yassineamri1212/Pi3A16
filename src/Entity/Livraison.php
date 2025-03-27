<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\LivraisonRepository;

#[ORM\Entity(repositoryClass: LivraisonRepository::class)]
#[ORM\Table(name: 'livraison')]
class Livraison
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    public function getId_livrai(): ?int
    {
        return $this->id_livrai;
    }

    public function setId_livrai(int $id_livrai): self
    {
        $this->id_livrai = $id_livrai;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $start_location = null;

    public function getStart_location(): ?string
    {
        return $this->start_location;
    }

    public function setStart_location(string $start_location): self
    {
        $this->start_location = $start_location;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $delivery_location = null;

    public function getDelivery_location(): ?string
    {
        return $this->delivery_location;
    }

    public function setDelivery_location(string $delivery_location): self
    {
        $this->delivery_location = $delivery_location;
        return $this;
    }

    #[ORM\Column(type: 'boolean', nullable: false)]
    private ?bool $is_delivered = null;

    public function is_delivered(): ?bool
    {
        return $this->is_delivered;
    }

    public function setIs_delivered(bool $is_delivered): self
    {
        $this->is_delivered = $is_delivered;
        return $this;
    }

    public function getIdLivrai(): ?int
    {
        return $this->id_livrai;
    }

    public function setIdLivrai(int $id_livrai): static
    {
        $this->id_livrai = $id_livrai;

        return $this;
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

}

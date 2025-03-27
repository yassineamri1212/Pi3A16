<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MoyenDeTransportUserRepository;

#[ORM\Entity(repositoryClass: MoyenDeTransportUserRepository::class)]
#[ORM\Table(name: 'moyen_de_transport_user')]
class MoyenDeTransportUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $user_id = null;

    public function getUser_id(): ?int
    {
        return $this->user_id;
    }

    public function setUser_id(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $moyen_de_transport_id = null;

    public function getMoyen_de_transport_id(): ?int
    {
        return $this->moyen_de_transport_id;
    }

    public function setMoyen_de_transport_id(int $moyen_de_transport_id): self
    {
        $this->moyen_de_transport_id = $moyen_de_transport_id;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getMoyenDeTransportId(): ?int
    {
        return $this->moyen_de_transport_id;
    }

    public function setMoyenDeTransportId(int $moyen_de_transport_id): static
    {
        $this->moyen_de_transport_id = $moyen_de_transport_id;

        return $this;
    }

}

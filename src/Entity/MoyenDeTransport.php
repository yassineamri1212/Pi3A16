<?php

        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;

        #[ORM\Entity]
        #[ORM\Table(name: 'moyen_de_transport')]
        class MoyenDeTransport
        {
            #[ORM\Id]
            #[ORM\GeneratedValue]
            #[ORM\Column(type: 'integer')]
            private ?int $id = null;

            #[ORM\Column(type: 'integer')]
            private ?int $prix = null;

            #[ORM\Column(type: 'string', length: 255)]
            private ?string $type = null;

            #[ORM\Column(type: 'integer')]
            private ?int $nbrePlaces = null;

            #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'moyenDeTransports')]
            #[ORM\JoinColumn(nullable: true)]
            private ?Evenement $evenement = null;

            public function getId(): ?int
            {
                return $this->id;
            }

            public function getPrix(): ?int
            {
                return $this->prix;
            }

            public function setPrix(int $prix): self
            {
                $this->prix = $prix;
                return $this;
            }

            public function getType(): ?string
            {
                return $this->type;
            }

            public function setType(string $type): self
            {
                $this->type = $type;
                return $this;
            }

            public function getNbrePlaces(): ?int
            {
                return $this->nbrePlaces;
            }

            public function setNbrePlaces(int $nbrePlaces): self
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
        }
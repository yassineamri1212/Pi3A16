<?php
        // src/Entity/Reponse.php

        namespace App\Entity;

        use Doctrine\ORM\Mapping as ORM;
        use App\Repository\ReponseRepository;

        #[ORM\Entity(repositoryClass: ReponseRepository::class)]
        #[ORM\Table(name: 'reponse')]
        class Reponse
        {
            #[ORM\Id]
            #[ORM\GeneratedValue]
            #[ORM\Column(type: 'integer')]
            private ?int $id = null;

            #[ORM\Column(type: 'datetime', nullable: false)]
            private ?\DateTimeInterface $date = null;

            #[ORM\Column(type: 'text', nullable: false)]
            private ?string $reponse = null;

            #[ORM\ManyToOne(targetEntity: \App\Entity\Reclamation::class, inversedBy: 'reponses')]
            #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
            private ?\App\Entity\Reclamation $reclamation = null;

            #[ORM\Column(type: 'integer', nullable: false)]
            private ?int $utilisateur_id = null;

            #[ORM\Column(type: 'string', length: 255, nullable: false)]
            private ?string $username = null;

            public function getId(): ?int
            {
                return $this->id;
            }

            public function getDate(): ?\DateTimeInterface
            {
                return $this->date;
            }

            public function setDate(\DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }

            public function getReponse(): ?string
            {
                return $this->reponse;
            }

            public function setReponse(string $reponse): self
            {
                $this->reponse = $reponse;
                return $this;
            }

            public function getReclamation(): ?\App\Entity\Reclamation
            {
                return $this->reclamation;
            }

            public function setReclamation(?\App\Entity\Reclamation $reclamation): self
            {
                $this->reclamation = $reclamation;
                return $this;
            }

            public function getUtilisateurId(): ?int
            {
                return $this->utilisateur_id;
            }

            public function setUtilisateurId(int $utilisateur_id): self
            {
                $this->utilisateur_id = $utilisateur_id;
                return $this;
            }

            public function getUsername(): ?string
            {
                return $this->username;
            }

            public function setUsername(string $username): self
            {
                $this->username = $username;
                return $this;
            }
        }
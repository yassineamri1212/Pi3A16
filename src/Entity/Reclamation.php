<?php
    // src/Entity/Reclamation.php

    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\Collections\Collection;
    use App\Repository\ReclamationRepository;

    #[ORM\Entity(repositoryClass: ReclamationRepository::class)]
    #[ORM\Table(name: 'reclamation')]
    class Reclamation
    {
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column(type: 'integer')]
        private ?int $id = null;

        #[ORM\Column(type: 'string', nullable: false)]
        private ?string $nom = null;

        #[ORM\Column(type: 'string', nullable: false)]
        private ?string $prenom = null;

        #[ORM\Column(type: 'string', nullable: false)]
        private ?string $email = null;

        #[ORM\Column(type: 'integer', nullable: false)]
        private ?int $numTele = null;

        #[ORM\Column(type: 'string', nullable: false)]
        private ?string $etat = null;

        #[ORM\Column(type: 'string', nullable: false)]
        private ?string $sujet = null;

        #[ORM\Column(type: 'text', nullable: false)]
        private ?string $description = null;

        #[ORM\Column(type: 'datetime', nullable: false)]
        private ?\DateTimeInterface $date = null;

        #[ORM\Column(type: 'integer', nullable: true)]
        private ?int $utilisateur_id = null;

        #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: \App\Entity\Reponse::class, cascade: ['remove'], orphanRemoval: true)]
        private Collection $reponses;

        public function __construct()
        {
            $this->reponses = new ArrayCollection();
        }

        public function getId(): ?int
        {
            return $this->id;
        }

        public function getNom(): ?string
        {
            return $this->nom;
        }

        public function setNom(string $nom): self
        {
            $this->nom = $nom;
            return $this;
        }

        public function getPrenom(): ?string
        {
            return $this->prenom;
        }

        public function setPrenom(string $prenom): self
        {
            $this->prenom = $prenom;
            return $this;
        }

        public function getEmail(): ?string
        {
            return $this->email;
        }

        public function setEmail(string $email): self
        {
            $this->email = $email;
            return $this;
        }

        public function getNumTele(): ?int
        {
            return $this->numTele;
        }

        public function setNumTele(int $numTele): self
        {
            $this->numTele = $numTele;
            return $this;
        }

        public function getEtat(): ?string
        {
            return $this->etat;
        }

        public function setEtat(string $etat): self
        {
            $this->etat = $etat;
            return $this;
        }

        public function getSujet(): ?string
        {
            return $this->sujet;
        }

        public function setSujet(string $sujet): self
        {
            $this->sujet = $sujet;
            return $this;
        }

        public function getDescription(): ?string
        {
            return $this->description;
        }

        public function setDescription(string $description): self
        {
            $this->description = $description;
            return $this;
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

        public function getUtilisateurId(): ?int
        {
            return $this->utilisateur_id;
        }

        public function setUtilisateurId(?int $utilisateur_id): self
        {
            $this->utilisateur_id = $utilisateur_id;
            return $this;
        }

        /**
         * @return Collection<int, \App\Entity\Reponse>
         */
        public function getReponses(): Collection
        {
            return $this->reponses;
        }

        public function addReponse(\App\Entity\Reponse $reponse): self
        {
            if (!$this->reponses->contains($reponse)) {
                $this->reponses[] = $reponse;
                $reponse->setReclamation($this);
            }
            return $this;
        }

        public function __toString(): string
        {
            // Return a meaningful string. For example, the subject.
            return $this->getSujet();
        }
        public function removeReponse(\App\Entity\Reponse $reponse): self
        {
            if ($this->reponses->removeElement($reponse)) {
                if ($reponse->getReclamation() === $this) {
                    $reponse->setReclamation(null);
                }
            }
            return $this;
        }
    }
<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'evenement')]
#[Vich\Uploadable]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date_evenement = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image_evenement = null;

    #[Vich\UploadableField(mapping: 'event_images', fileNameProperty: 'image_evenement')]
    #[Assert\File(maxSize: '2M', mimeTypes: ['image/jpeg', 'image/png'])]
    private ?File $imageFile = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: MoyenDeTransport::class, cascade: ['persist', 'remove'])]
    private Collection $moyenDeTransports;

    // New property to track updates when a new file is set.
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->moyenDeTransports = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getDateEvenement(): ?\DateTimeInterface
    {
        return $this->date_evenement;
    }

    public function setDateEvenement(?\DateTimeInterface $date_evenement): self
    {
        $this->date_evenement = $date_evenement;
        return $this;
    }

    public function getImageEvenement(): ?string
    {
        return $this->image_evenement;
    }

    public function setImageEvenement(?string $image_evenement): self
    {
        $this->image_evenement = $image_evenement;
        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    // src/Entity/Evenement.php

    public function setImageFile(?File $imageFile = null): self
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            // Use mutable DateTime instead of DateTimeImmutable
            $this->updatedAt = new \DateTime(); // <--- CHANGE THIS LINE
        }
        return $this;
    }

    /**
     * @return Collection<int,MoyenDeTransport>
     */
    public function getMoyenDeTransports(): Collection
    {
        return $this->moyenDeTransports;
    }

    public function addMoyenDeTransport(MoyenDeTransport $moyenDeTransport): self
    {
        if (!$this->moyenDeTransports->contains($moyenDeTransport)) {
            $this->moyenDeTransports->add($moyenDeTransport);
            $moyenDeTransport->setEvenement($this);
        }
        return $this;
    }

    public function removeMoyenDeTransport(MoyenDeTransport $moyenDeTransport): self
    {
        if ($this->moyenDeTransports->removeElement($moyenDeTransport)) {
            if ($moyenDeTransport->getEvenement() === $this) {
                $moyenDeTransport->setEvenement(null);
            }
        }
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
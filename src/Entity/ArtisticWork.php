<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: 'App\Repository\ArtisticWorkRepository')]
#[Vich\Uploadable]
#[Assert\Expression(
    expression: '!this.isForSale() or this.getPrice() != null',
    message: 'Le prix est requis pour une œuvre en vente.'
)]
class ArtisticWork
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Gallery', inversedBy: 'artisticWorks', cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $gallery;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Category', inversedBy: 'galleriesEchange', cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $category;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $picture;

    #[Vich\UploadableField(mapping: 'artisticWorks_images', fileNameProperty: 'picture')]
    #[Assert\File(
        maxSize: '1000k',
        maxSizeMessage: 'Le fichier excède 1000Ko.',
        mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'],
        mimeTypesMessage: 'formats autorisés: png, jpeg, jpg, gif'
    )]
    private $pictureFile;

    #[ORM\Column(type: 'datetime')]
    private $updated_at;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $description;

    public const LISTING_TYPES = ['none', 'sale', 'exchange', 'both'];
    public const STATUSES = ['available', 'reserved', 'sold', 'exchanged'];

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'none'])]
    private string $listingType = 'none';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true, options: ['default' => 'EUR'])]
    private ?string $currency = 'EUR';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $exchangeDescription = null;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'available'])]
    private string $status = 'available';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getPicture();
    }

    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    public function setPictureFile(?File $pictureFile): ArtisticWork
    {
        $this->pictureFile = $pictureFile;
        if ($this->pictureFile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getListingType(): string
    {
        return $this->listingType;
    }

    public function setListingType(string $listingType): self
    {
        $this->listingType = $listingType;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getExchangeDescription(): ?string
    {
        return $this->exchangeDescription;
    }

    public function setExchangeDescription(?string $exchangeDescription): self
    {
        $this->exchangeDescription = $exchangeDescription;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isForSale(): bool
    {
        return in_array($this->listingType, ['sale', 'both']);
    }

    public function isForExchange(): bool
    {
        return in_array($this->listingType, ['exchange', 'both']);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->listingType !== 'none';
    }
}

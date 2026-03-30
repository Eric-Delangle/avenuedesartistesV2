<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GalleryRepository")
 */
class Gallery
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="galleriesEchange")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", cascade={"remove"},inversedBy="galleryEchange")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ArtisticWork", mappedBy="gallery")
     */
    private $artisticWorks;

    public const GALLERY_TYPES = ['showcase', 'sale', 'exchange', 'mixed'];

    /**
     * @ORM\Column(type="string", length=20, options={"default": "showcase"})
     */
    private string $galleryType = 'showcase';

    public function __construct()
    {
        $this->artisticWorks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    /**
     * @return Collection|ArtisticWork[]
     */
    public function getArtisticWorks(): Collection
    {
        return $this->artisticWorks;
    }

    public function __toString() {
        return (string) $this->getName();
    }

    public function getGalleryType(): string
    {
        return $this->galleryType;
    }

    public function setGalleryType(string $galleryType): self
    {
        $this->galleryType = $galleryType;

        return $this;
    }

    public function isMarketplace(): bool
    {
        return $this->galleryType !== 'showcase';
    }

     public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the value of slug
     *
     * @return  self
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
}

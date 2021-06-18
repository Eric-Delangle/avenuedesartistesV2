<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"group1"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="categories")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"group1"})
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group1"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GalleryEchange", mappedBy="category")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $galleriesEchange;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GalleryVente", mappedBy="category")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $galleriesVente;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->galleriesEchange = new ArrayCollection();
        $this->galleriesVente = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
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
     * @return Collection|GalleryEchange[]
     */
    public function getGalleriesEchange(): Collection
    {
        return $this->galleriesEchange;
    }

    /**
     * @return Collection|GalleryVente[]
     */
    public function getGalleriesVente(): Collection
    {
        return $this->galleriesVente;
    }


    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Get the value of slug
     */
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

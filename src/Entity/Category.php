<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\CategoryRepository')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['group1'])]
    private $id;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\User', mappedBy: 'categories')]
    #[Groups(['group1'])]
    private $users;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['group1'])]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Gallery', mappedBy: 'category')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $galleriesEchange;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->galleriesEchange = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

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

    public function getGalleriesEchange(): Collection
    {
        return $this->galleriesEchange;
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\UserRepository')]
#[UniqueEntity(fields: 'email')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['group1'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(min: 8, minMessage: 'Votre mot de passe doit contenir au minimum huit caractères', groups: ['registration'])]
    #[Assert\EqualTo(propertyPath: 'password_verify', message: 'Vos mots de passe ne sont pas identiques', groups: ['registration'])]
    private $password;

    #[Assert\EqualTo(propertyPath: 'password', message: 'Vos mots de passe ne sont pas identiques', groups: ['registration'])]
    public $password_verify;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['group1'])]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['group1'])]
    private $lastName;

    #[ORM\Column(type: 'string', length: 255)]
    private $slug;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['group1'])]
    private $location;

    #[ORM\Column(name: 'avatar', type: 'string', length: 255, nullable: false)]
    private $avatar;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatar')]
    #[Assert\File(
        maxSize: '1000k',
        maxSizeMessage: 'Le fichier excède 1000Ko.',
        mimeTypes: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'],
        mimeTypesMessage: 'formats autorisés: png, jpeg, jpg, gif'
    )]
    private $avatarFile;

    #[ORM\Column(type: 'datetime')]
    private $registeredAt;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Category', inversedBy: 'users')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['group1'])]
    private $categories;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Gallery', mappedBy: 'user')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private $galleryEchange;

    #[ORM\Column(type: 'integer')]
    private $niveau;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Message', mappedBy: 'expediteur')]
    private $message;

    #[ORM\OneToMany(targetEntity: 'App\Entity\Message', mappedBy: 'destinataire')]
    private $messages;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description2;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $adress;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $postalCode;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $tel;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $activation_token;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $reset_token;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->messages = new ArrayCollection();
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = ucfirst($firstName);

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = ucfirst($lastName);

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = ucfirst($location);

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(?File $avatarFile): User
    {
        $this->avatarFile = $avatarFile;
        if ($this->avatarFile instanceof UploadedFile) {
            $this->updated_at = new \DateTime('now');
        }

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeInterface $registeredAt): self
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    #[Groups(['group2'])]
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->slug,
            $this->password,
            $this->location,
            $this->registeredAt,
            $this->niveau,
            $this->categories
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->firstName,
            $this->lastName,
            $this->slug,
            $this->password,
            $this->location,
            $this->registeredAt,
            $this->niveau,
            $this->categories
        ) = unserialize($serialized);
    }

    public function eraseCredentials(): void
    {
    }

    public function getSalt()
    {
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setExpediteur($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);

            if ($message->getExpediteur() === $this) {
                $message->setExpediteur(null);
            }
        }

        return $this;
    }

    public function getDescription2(): ?string
    {
        return $this->description2;
    }

    public function setDescription2(?string $description2): self
    {
        $this->description2 = $description2;

        return $this;
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

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getGalleryEchange()
    {
        return $this->galleryEchange;
    }

    public function setGalleryEchange($galleryEchange)
    {
        $this->galleryEchange = $galleryEchange;

        return $this;
    }

    public function getPassword_verify()
    {
        return $this->password_verify;
    }

    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }

    public function setActivationToken(?string $activation_token): self
    {
        $this->activation_token = $activation_token;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }
}

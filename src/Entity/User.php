<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
//use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email")
 * @Vich\Uploadable()
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"group1"})
     */
    private $id;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $userIdentifier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit contenir au minimum huit caractères", groups={"registration"})
     * @Assert\EqualTo(propertyPath="password_verify", message="Vos mots de passe ne sont pas identiques", groups={"registration"})
     */
    private $password;

    /**
     *  @Assert\EqualTo(propertyPath="password", message="Vos mots de passe ne sont pas identiques", groups={"registration"})
     * @var string|null
     */
    public $password_verify;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group1"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group1"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"group1"})
     */
    private $location;

    /**
     * @ORM\Column(name="avatar", type="string", length=255, nullable = false)
     */
    private $avatar = "avatardefaut.jpg";

    /**
     * @Vich\UploadableField(mapping="avatars", fileNameProperty="avatar")
     * @Assert\File(
     * maxSize="1000k",
     * maxSizeMessage="Le fichier excède 1000Ko.",
     * mimeTypes={"image/png", "image/jpeg", "image/jpg", "image/gif"},
     * mimeTypesMessage= "formats autorisés: png, jpeg, jpg, gif"
     * )
     * @var File|null
     */
    private $avatarFile;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registeredAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="users")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Groups({"group1"})
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GalleryVente", mappedBy="user")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $galleryVente;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GalleryEchange", mappedBy="user")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $galleryEchange;

    /**
     * @ORM\Column(type="integer")
     */
    private $niveau;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="expediteur")
     */
    private $message;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="destinataire")
     */
    private $messages;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postalCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $activation_token;


    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
        $this->messages = new ArrayCollection();
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

    // modifier la méthode getRoles
    public function getRoles()
    {
        if ($this->niveau == 2)
            return ['ROLE_ADMIN'];
        elseif ($this->niveau == 1)
            return ['ROLE_USER'];
        elseif ($this->niveau == 3)
            return ['ROLE_VENDEUR'];
        else
            return [];
    }

    public function setRoles(array $roles)
    {
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        foreach ($roles as $role) {
            if (substr($role, 0, 5) !== 'ROLE_') {
                throw new InvalidArgumentException("Chaque rôle doit commencer par 'ROLE_'");
            }
        }
        $this->roles = $roles;
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

    /**
     * @return null|string
     */
    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    /**
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $AvatarFile
     *  @return User
     */
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

    /**
     * @return Collection|Category[]
     * @Groups({"group2"})
     */

    public function getCategories(): Collection
    {
        return $this->categories;
    }


    /** @see \Serializable::serialize() */
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
            // voir remarques sur salt plus haut
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
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
            // voir remarques sur salt plus haut
            // $this->salt
        ) = unserialize($serialized);
    }
    public function eraseCredentials()
    {
    }
    public function getUserName()
    {
        return $this->email;
    }
    public function getSalt()
    {
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * @return Collection|Message[]
     */

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

    /**
     * Get the value of galleryVente
     */
    public function getGalleryVente()
    {
        return $this->galleryVente;
    }

    /**
     * Set the value of galleryVente
     *
     * @return  self
     */
    public function setGalleryVente($galleryVente)
    {
        $this->galleryVente = $galleryVente;

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

    /**
     * Get the value of userIdentifier
     */
    public function getUserIdentifier()
    {
        return $this->getEmail();
    }

    /**
     * Set the value of userIdentifier
     *
     * @return  self
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    /**
     * Get the value of galleryEchange
     */
    public function getGalleryEchange()
    {
        return $this->galleryEchange;
    }

    /**
     * Set the value of galleryEchange
     *
     * @return  self
     */
    public function setGalleryEchange($galleryEchange)
    {
        $this->galleryEchange = $galleryEchange;

        return $this;
    }

    /**
     * Get the value of password_verify
     *
     * @return  string|null
     */
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
}

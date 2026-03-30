<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 * @ORM\HasLifecycleCallbacks()
 * @Assert\Expression(
 *     "this.getType() != 'purchase' or this.getOfferPrice() != null",
 *     message="Le prix est requis pour une offre d'achat."
 * )
 * @Assert\Expression(
 *     "this.getType() != 'exchange' or this.getProposedWork() != null",
 *     message="Vous devez proposer une de vos œuvres pour un échange."
 * )
 */
class Offer
{
    public const TYPES = ['purchase', 'exchange'];
    public const STATUSES = ['pending', 'accepted', 'rejected', 'countered'];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\Choice(choices=Offer::TYPES, message="Type d'offre invalide.")
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=20, options={"default": "pending"})
     * @Assert\Choice(choices=Offer::STATUSES, message="Statut d'offre invalide.")
     */
    private string $status = 'pending';

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private ?string $offerPrice = null;

    /**
     * @ORM\Column(type="text")
     */
    private string $offerMessage = '';

    /**
     * Œuvre proposée en échange (nullable — uniquement pour type=exchange)
     * @ORM\ManyToOne(targetEntity="App\Entity\ArtisticWork")
     * @ORM\JoinColumn(name="proposed_work_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?ArtisticWork $proposedWork = null;

    /**
     * Œuvre visée par l'offre
     * @ORM\ManyToOne(targetEntity="App\Entity\ArtisticWork")
     * @ORM\JoinColumn(name="target_work_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?ArtisticWork $targetWork = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private ?User $sender = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getOfferPrice(): ?string
    {
        return $this->offerPrice;
    }

    public function setOfferPrice(?string $offerPrice): self
    {
        $this->offerPrice = $offerPrice;

        return $this;
    }

    public function getOfferMessage(): string
    {
        return $this->offerMessage;
    }

    public function setOfferMessage(string $offerMessage): self
    {
        $this->offerMessage = $offerMessage;

        return $this;
    }

    public function getProposedWork(): ?ArtisticWork
    {
        return $this->proposedWork;
    }

    public function setProposedWork(?ArtisticWork $proposedWork): self
    {
        $this->proposedWork = $proposedWork;

        return $this;
    }

    public function getTargetWork(): ?ArtisticWork
    {
        return $this->targetWork;
    }

    public function setTargetWork(?ArtisticWork $targetWork): self
    {
        $this->targetWork = $targetWork;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }
}

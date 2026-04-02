<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\SubscriptionRepository')]
#[ORM\HasLifecycleCallbacks]
class Subscription
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PAST_DUE  = 'past_due';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 30)]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeCustomerId = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $startedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endsAt = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->startedAt = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        if ($this->endsAt !== null && $this->endsAt < new \DateTime()) {
            return false;
        }
        return true;
    }

    public function getId(): ?int { return $this->id ?? null; }

    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getStripeSubscriptionId(): ?string { return $this->stripeSubscriptionId; }
    public function setStripeSubscriptionId(?string $id): self { $this->stripeSubscriptionId = $id; return $this; }

    public function getStripeCustomerId(): ?string { return $this->stripeCustomerId; }
    public function setStripeCustomerId(?string $id): self { $this->stripeCustomerId = $id; return $this; }

    public function getStartedAt(): \DateTimeInterface { return $this->startedAt; }
    public function setStartedAt(\DateTimeInterface $d): self { $this->startedAt = $d; return $this; }

    public function getEndsAt(): ?\DateTimeInterface { return $this->endsAt; }
    public function setEndsAt(?\DateTimeInterface $d): self { $this->endsAt = $d; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
}

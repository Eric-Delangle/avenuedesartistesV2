<?php

namespace App\Twig;

use App\Repository\SubscriptionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SubscriptionExtension extends AbstractExtension
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private Security $security,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_subscribed', [$this, 'isSubscribed']),
        ];
    }

    public function isSubscribed(): bool
    {
        $user = $this->security->getUser();
        if (!$user) {
            return false;
        }
        $sub = $this->subscriptionRepository->findOneBy(['user' => $user]);
        return $sub !== null && $sub->isActive();
    }
}

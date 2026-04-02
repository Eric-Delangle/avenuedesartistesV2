<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private string $stripeSecretKey,
        private string $stripePublicKey,
        private string $stripePriceId,
        private string $stripeWebhookSecret,
    ) {}

    #[Route('', name: 'subscription_index')]
    #[IsGranted('ROLE_USER')]
    public function index(SubscriptionRepository $subRepo): Response
    {
        $user = $this->getUser();
        $sub  = $subRepo->findOneBy(['user' => $user]);

        return $this->render('subscription/index.html.twig', [
            'has_subscription' => $sub !== null && $sub->isActive(),
            'subscription'     => $sub,
            'stripe_public_key' => $this->stripePublicKey,
        ]);
    }

    #[Route('/checkout', name: 'subscription_checkout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(UrlGeneratorInterface $urlGenerator, SubscriptionRepository $subRepo): Response
    {
        $user = $this->getUser();
        $sub  = $subRepo->findOneBy(['user' => $user]);

        if ($sub !== null && $sub->isActive()) {
            $this->addFlash('success', 'Vous avez déjà un abonnement actif.');
            return $this->redirectToRoute('subscription_index');
        }

        $stripe = new StripeClient($this->stripeSecretKey);

        try {
            $session = $stripe->checkout->sessions->create([
                'mode'               => 'subscription',
                'payment_method_types' => ['card'],
                'customer_email'     => $user->getEmail(),
                'line_items'         => [[
                    'price'    => $this->stripePriceId,
                    'quantity' => 1,
                ]],
                'success_url' => $urlGenerator->generate('subscription_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $urlGenerator->generate('subscription_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'metadata'    => ['user_id' => $user->getId()],
            ]);

            return $this->redirect($session->url);
        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Erreur Stripe : ' . $e->getMessage());
            return $this->redirectToRoute('subscription_index');
        }
    }

    #[Route('/success', name: 'subscription_success')]
    #[IsGranted('ROLE_USER')]
    public function success(
        Request $request,
        EntityManagerInterface $em,
        SubscriptionRepository $subscriptionRepository
    ): Response {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('subscription_index');
        }

        $user   = $this->getUser();
        $stripe = new StripeClient($this->stripeSecretKey);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['subscription', 'customer'],
            ]);

            if ($session->payment_status === 'paid' && $session->metadata['user_id'] == $user->getId()) {
                $existing = $subscriptionRepository->findOneBy(['user' => $user]);

                if (!$existing) {
                    $sub = new Subscription();
                    $sub->setUser($user);
                } else {
                    $sub = $existing;
                }

                $sub->setStatus(Subscription::STATUS_ACTIVE);
                $sub->setStripeSubscriptionId($session->subscription->id ?? $session->subscription);
                $sub->setStripeCustomerId($session->customer->id ?? $session->customer);

                if ($session->subscription && isset($session->subscription->current_period_end)) {
                    $sub->setEndsAt(new \DateTime('@' . $session->subscription->current_period_end));
                }

                $em->persist($sub);
                $em->flush();

                $this->addFlash('success', 'Abonnement activé ! Vous pouvez maintenant mettre vos œuvres en vente.');
            }
        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Impossible de vérifier le paiement. Contactez le support.');
        }

        return $this->redirectToRoute('subscription_index');
    }

    #[Route('/cancel', name: 'subscription_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sub  = $user->getSubscription();

        if (!$sub || !$sub->isActive()) {
            return $this->redirectToRoute('subscription_index');
        }

        $stripe = new StripeClient($this->stripeSecretKey);

        try {
            if ($sub->getStripeSubscriptionId()) {
                $stripe->subscriptions->cancel($sub->getStripeSubscriptionId());
            }
            $sub->setStatus(Subscription::STATUS_CANCELLED);
            $em->flush();
            $this->addFlash('success', 'Abonnement annulé. Il reste actif jusqu\'à la fin de la période en cours.');
        } catch (ApiErrorException $e) {
            $this->addFlash('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }

        return $this->redirectToRoute('subscription_index');
    }

    #[Route('/webhook', name: 'subscription_webhook', methods: ['POST'])]
    public function webhook(Request $request, EntityManagerInterface $em, SubscriptionRepository $repo): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        if (empty($this->stripeWebhookSecret)) {
            return new Response('Webhook secret not configured', 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $this->stripeWebhookSecret);
        } catch (\Exception $e) {
            return new Response('Invalid signature', 400);
        }

        $stripeObj = $event->data->object;

        if ($event->type === 'customer.subscription.deleted') {
            $sub = $repo->findOneBy(['stripeSubscriptionId' => $stripeObj->id]);
            if ($sub) {
                $sub->setStatus(Subscription::STATUS_CANCELLED);
                $em->flush();
            }
        }

        if ($event->type === 'customer.subscription.updated') {
            $sub = $repo->findOneBy(['stripeSubscriptionId' => $stripeObj->id]);
            if ($sub) {
                $status = $stripeObj->status === 'active' ? Subscription::STATUS_ACTIVE : Subscription::STATUS_PAST_DUE;
                $sub->setStatus($status);
                if (isset($stripeObj->current_period_end)) {
                    $sub->setEndsAt(new \DateTime('@' . $stripeObj->current_period_end));
                }
                $em->flush();
            }
        }

        return new Response('OK', 200);
    }
}

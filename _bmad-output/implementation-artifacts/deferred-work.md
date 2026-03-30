# Deferred Work — Marketplace Avenue des Artistes

_Objectifs différés suite au split du 2026-03-30_

## B — Page Marketplace
- `MarketplaceController` : browse, search, filtres (type/catégorie/prix/localisation)
- `ArtisticWorkRepository::findForMarketplace()` avec QueryBuilder + KnpPaginator
- Templates : `marketplace/index.html.twig`, `marketplace/show.html.twig`
- Modification `base.html.twig` : lien marketplace + badge offres

## Review findings différés (Goal A — 2026-03-30)

- **Goal C** : Vérifier dans `OfferVoter` qu'un artiste ne peut pas faire une offre sur sa propre œuvre (`sender` ≠ `targetWork.gallery.user`)
- **Goal C** : Ajouter contrainte unique sur `(sender_id, target_work_id, status='pending')` pour éviter les offres dupliquées
- **Goal D** : Stratégie d'archivage des références user sur `Offer`/`Transaction` (éviter perte d'audit à la suppression d'un compte)
- **Pre-existing** : `Gallery::$slug` sans propriété ni `@ORM\Column` — à corriger dans une story dédiée avant la production

## C — Système d'offres
- Entité `Offer` (prérequis : objectif A)
- `OfferController` : new, accept, reject, cancel
- `OfferType` form
- `src/Security/OfferVoter.php`, `ArtisticWorkVoter.php`, `GalleryVoter.php`
- Symfony Workflow : config `workflow.yaml` + transitions ArtisticWork.status
- Notifications automatiques via entité `Message` existante
- Templates : `offer/new.html.twig`, `offer/received.html.twig`, `offer/sent.html.twig`

## D — Paiement Stripe
- `src/Service/StripeService.php` : createPaymentIntent, handleWebhook
- `StripeWebhookController` : route POST /stripe/webhook + vérification signature
- `TransactionController` : historique, détail
- Templates email : offer_received, offer_accepted, offer_rejected, payment_confirmed
- Variable `.env` : `STRIPE_WEBHOOK_SECRET`

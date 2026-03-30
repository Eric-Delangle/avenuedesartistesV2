---
stepsCompleted: [1, 2, 3, 4, 5, 6, 7, 8]
lastStep: 8
status: 'complete'
completedAt: '2026-03-30'
inputDocuments: ['_bmad-output/prd.md']
workflowType: 'architecture'
project_name: 'Avenue des Artistes — Marketplace'
user_name: 'pol'
date: '2026-03-30'
---

# Architecture Decision Document
# Avenue des Artistes — Transformation en Marketplace

_Document construit collaborativement, étape par étape._

---

## Analyse du Contexte Projet

### Vue d'ensemble des exigences

**Exigences Fonctionnelles (9 FRs) — 4 domaines :**

| Domaine | FRs | Complexité |
|---|---|---|
| Gestion des annonces (type, prix, échange, statut) | FR1, FR2, FR3 | Moyenne |
| Marketplace & découverte | FR4 | Faible |
| Cycle offre → transaction | FR5, FR6 | Haute |
| Paiement Stripe & notifications | FR7, FR8 | Haute |

**Exigences Non-Fonctionnelles critiques :**
- Zéro commission → Stripe Checkout simple (pas Connect)
- Ownership enforcement sur toutes les mutations
- Migration Doctrine incrémentale — aucune rupture des fonctionnalités existantes
- Interface en français, responsive

### Évaluation de la complexité

- **Domaine principal :** Web full-stack Symfony MVC (brownfield)
- **Niveau de complexité :** Moyen
- **Composants architecturaux estimés :** ~8 (2 nouvelles entités, 3 nouveaux controllers, 2 form types, 1 service Stripe)

### Contraintes techniques et dépendances

- Symfony 6.0 / PHP 8.1 / MySQL — stack figée (brownfield)
- Stripe SDK déjà installé (clés en `.env`)
- VichUploader actif pour les images d'œuvres
- KnpPaginator disponible pour le marketplace

### Préoccupations transversales identifiées

1. **Gestion d'état des œuvres** — machine à états critique (`disponible → réservé → vendu/échangé`)
2. **Ownership / sécurité** — Symfony Voters pour les mutations sensibles
3. **Intégration Stripe** — Webhook de confirmation de paiement nécessaire
4. **Réutilisation du système Message** — notifications d'offres via messagerie existante
5. **Backward compatibility** — galeries sans type marketplace continuent de fonctionner

---

## Fondation Technique (Brownfield — Stack Existante)

### Stack en place (figée)

| Couche | Technologie | Statut |
|---|---|---|
| Framework | Symfony 6.0 | ✅ En place |
| Langage | PHP 8.1 | ✅ En place |
| ORM | Doctrine ORM + Migrations | ✅ En place |
| Base de données | MySQL | ✅ En place |
| Templates | Twig | ✅ En place |
| Uploads | VichUploader | ✅ En place |
| Pagination | KnpPaginator | ✅ En place |
| Slugs | Cocur Slugify | ✅ En place |
| Emails | Symfony Mailer | ✅ En place |
| Assets | Webpack Encore + Stimulus | ✅ En place |
| Paiement | Stripe PHP SDK | ✅ Clés configurées |

### Dépendances à ajouter

```bash
composer require symfony/workflow   # Machine à états pour le statut des œuvres
composer require stripe/stripe-php  # Si non présent (à vérifier)
```

### Patterns adoptés

| Pattern | Décision |
|---|---|
| Machine à états | Symfony Workflow Component |
| Sécurité ownership | Symfony Voters (1 par entité sensible) |
| Service Stripe | Classe dédiée `src/Service/StripeService.php` |
| Formulaires | Symfony Form Types (cohérent avec l'existant) |
| Routage | Annotations PHP (cohérent avec l'existant) |

---

## Décisions Architecturales Cœur

### Analyse des priorités

**Décisions critiques (bloquantes pour l'implémentation) :**
- Schéma de données : extension `ArtisticWork` + entités `Offer` et `Transaction`
- Machine à états Symfony Workflow pour `ArtisticWork.status`
- Symfony Voters pour l'ownership

**Décisions importantes (structurantes) :**
- Stripe Checkout simple (pas Connect) + Webhook sécurisé
- Notifications via messagerie existante (`Message`)
- Filtres marketplace côté serveur (QueryBuilder)

**Décisions différées (post-MVP) :**
- Système d'avis/notes (`Review` entity) — Phase 2
- Stripe Connect pour paiements directs — Hors périmètre
- Déploiement production — À définir

---

### 1. Architecture des Données

#### 1.1 Extension de `ArtisticWork`

Champs marketplace ajoutés directement à l'entité existante (pas de nouvelle entité `Listing`) :

```php
// Nouveaux champs dans ArtisticWork
listingType: ENUM('none', 'sale', 'exchange', 'both')   // défaut: 'none'
price: decimal(10,2) nullable                            // pour vente
currency: string(3) default 'EUR'
exchangeDescription: text nullable                       // ce que l'artiste veut en échange
status: ENUM('available', 'reserved', 'sold', 'exchanged')  // défaut: 'available'
```

**Rationale :** Évite une jointure systématique, cohérent avec la structure existante. Les œuvres `listingType=none` continuent de fonctionner comme avant.

#### 1.2 Extension de `Gallery`

```php
// Nouveau champ dans Gallery
galleryType: ENUM('showcase', 'sale', 'exchange', 'mixed')  // défaut: 'showcase'
```

Les œuvres héritent le type de leur galerie par défaut, avec possibilité de surcharge individuelle.

#### 1.3 Nouvelle entité `Offer`

```php
class Offer {
    id: integer (PK)
    type: ENUM('purchase', 'exchange')
    status: ENUM('pending', 'accepted', 'rejected', 'countered')
    offerPrice: decimal(10,2) nullable       // pour achat
    offerMessage: text
    proposedWork: ManyToOne(ArtisticWork) nullable  // œuvre proposée en échange
    targetWork: ManyToOne(ArtisticWork)             // œuvre visée
    sender: ManyToOne(User)
    createdAt: datetime
    updatedAt: datetime
}
```

#### 1.4 Nouvelle entité `Transaction`

```php
class Transaction {
    id: integer (PK)
    type: ENUM('sale', 'exchange')
    amount: decimal(10,2) nullable
    stripePaymentIntentId: string nullable
    status: ENUM('completed', 'cancelled', 'refunded')
    artwork: ManyToOne(ArtisticWork)
    buyer: ManyToOne(User)
    seller: ManyToOne(User)
    offer: OneToOne(Offer)
    completedAt: datetime
}
```

#### 1.5 Machine à états — Symfony Workflow

Transitions de `ArtisticWork.status` :

```
available ──(offre acceptée)──► reserved
reserved  ──(paiement confirmé)──► sold
reserved  ──(échange confirmé)──► exchanged
reserved  ──(offre annulée/expirée)──► available
```

Configuré dans `config/packages/workflow.yaml`.

---

### 2. Authentification & Sécurité

#### 2.1 Auth existante conservée intégralement
Form-based login Symfony, activation email, reset password — aucun changement.

#### 2.2 Symfony Voters

| Voter | Actions protégées |
|---|---|
| `ArtisticWorkVoter` | edit, delete, changeStatus, manageOffers |
| `OfferVoter` | accept, reject, cancel |
| `GalleryVoter` | edit, delete |

Règle : **seul le propriétaire** peut modifier ses annonces et gérer les offres reçues.

#### 2.3 Stripe Webhook sécurisé
Vérification de signature (`Stripe\Webhook::constructEvent`) dans `StripeWebhookController`.
Route publique `/stripe/webhook` mais signature obligatoire — clé webhook en `.env`.

#### 2.4 CSRF
Symfony Form intègre CSRF nativement — déjà actif sur tous les formulaires.

---

### 3. API & Communication

#### 3.1 Architecture MVC Symfony pure
Pas d'API REST séparée. Tout en Twig + Controllers Symfony. Cohérent avec l'existant.

#### 3.2 AJAX ciblé (Stimulus + fetch natif)
Endpoints JSON légers uniquement pour :
- Vérifier disponibilité d'une œuvre (temps réel)
- Soumettre une offre sans rechargement de page
- Badge compteur d'offres reçues

#### 3.3 Notifications via messagerie existante
Les offres génèrent un `Message` automatique dans le système existant. Pas de nouvelle table.

#### 3.4 Emails transactionnels (Symfony Mailer)
- Confirmation d'offre envoyée
- Offre acceptée / refusée
- Confirmation de paiement (vendeur + acheteur)

---

### 4. Frontend

#### 4.1 Twig + Stimulus
Nouveaux templates dans : `templates/marketplace/`, `templates/offer/`, `templates/transaction/`.

#### 4.2 Filtres marketplace côté serveur
Filtres (type, catégorie, prix, localisation) traités en PHP via `QueryBuilder` Doctrine. Pagination KnpPaginator réutilisée directement.

---

### 5. Infrastructure & Déploiement

#### 5.1 Environnement de développement
XAMPP local. Docker disponible (`docker-compose.yml` présent) en option.

#### 5.2 Migrations Doctrine incrémentales
Une migration par feature. Jamais de `doctrine:schema:update --force` en prod.

#### 5.3 Variables d'environnement
`.env.local` pour overrides locaux. Clés Stripe + Mailer + Webhook secret en `.env`.

#### 5.4 Séquence d'implémentation (ordre des dépendances)
1. Migrations DB (nouveaux champs + nouvelles entités)
2. Symfony Workflow config
3. Voters
4. StripeService + WebhookController
5. MarketplaceController + templates
6. OfferController + OfferType
7. TransactionController
8. Emails transactionnels

---

## Patterns d'Implémentation & Règles de Cohérence

### Points de conflit identifiés : 6 zones critiques

### Naming Patterns

**Conventions de nommage :**

| Élément | Convention | Exemple |
|---|---|---|
| Classes PHP | PascalCase | `ArtisticWork`, `Offer`, `Transaction` |
| Propriétés PHP | camelCase | `$listingType`, `$offerPrice` |
| Colonnes SQL | snake_case (auto Doctrine) | `listing_type`, `offer_price` |
| Tables SQL | snake_case (auto Doctrine) | `artistic_work`, `offer`, `transaction` |
| Routes URL | kebab-case français | `/marketplace`, `/offre/nouvelle`, `/transaction/{id}` |
| Controllers | `NomController.php` | `MarketplaceController.php` |
| Entities | `Nom.php` | `Offer.php`, `Transaction.php` |
| Repositories | `NomRepository.php` | `OfferRepository.php` |
| Form Types | `NomType.php` | `OfferType.php` |
| Voters | `NomVoter.php` | `ArtisticWorkVoter.php` |
| Services | `NomService.php` | `StripeService.php` |
| Templates | `nom/action.html.twig` | `marketplace/index.html.twig` |

### Structure Patterns

**Organisation des controllers — schéma strict :**
```php
#[Route('/offre/nouvelle/{id}', name: 'offer_new')]
public function new(ArtisticWork $work, Request $request): Response
{
    $this->denyAccessUnlessGranted('OFFER', $work); // Voter obligatoire
    // form → message notification → redirect + flash
}
```
- Un controller = un domaine (Marketplace, Offer, Transaction, StripeWebhook)
- PRG pattern systématique (Post-Redirect-Get)
- Flash messages en français uniquement

### Format Patterns

**Flash messages standardisés :**
Types autorisés : `success`, `error`, `warning`, `info` — aucun autre.

```php
$this->addFlash('success', 'Votre offre a été envoyée.');
$this->addFlash('error', 'Vous n\'êtes pas autorisé.');
$this->addFlash('warning', 'Cette œuvre n\'est plus disponible.');
```

**Notifications automatiques (pattern standard) :**
```php
$notification = new Message();
$notification->setExpediteur($sender);
$notification->setDestinataire($recipient);
$notification->setTitre('[Marketplace] ...');
$notification->setMessage($content);
$notification->setPostedAt(new \DateTime());
```

### Process Patterns

**Machine à états — règle absolue :**
```php
// ✅ CORRECT — toujours passer par Workflow
$workflow->apply($artwork, 'reserve');

// ❌ INTERDIT — jamais modifier status directement
$artwork->setStatus('reserved');
```

**Ownership — règle absolue :**
```php
// ✅ CORRECT — toujours Voter
$this->denyAccessUnlessGranted('EDIT', $artwork);

// ❌ INTERDIT — jamais vérification manuelle
if ($artwork->getGallery()->getUser() !== $this->getUser()) { throw ... }
```

### Règles obligatoires (MUST / MUST NOT)

**MUST :**
- camelCase PHP / snake_case SQL
- Voters pour tout accès aux ressources sensibles
- Workflow pour tout changement de `ArtisticWork.status`
- PRG pattern sur tous les formulaires
- Flash messages en français
- Une migration Doctrine par feature

**MUST NOT :**
- Modifier `ArtisticWork.status` sans passer par Workflow
- Bypasser les Voters avec des vérifications `getUser() === $owner`
- Utiliser `doctrine:schema:update --force`
- Créer des routes sans préfixe de domaine clair

---

## Structure du Projet & Frontières

### Arborescence complète

```
avenuedesartistesV2/
│
├── config/
│   ├── packages/
│   │   ├── security.yaml              [EXIST]
│   │   └── workflow.yaml              [NEW] ← machine à états ArtisticWork
│   └── services.yaml                  [EXIST — ajouter StripeService]
│
├── src/
│   ├── Controller/
│   │   ├── HomeController.php         [EXIST]
│   │   ├── SecurityController.php     [EXIST]
│   │   ├── MemberController.php       [EXIST]
│   │   ├── UserController.php         [EXIST]
│   │   ├── GalleryController.php      [MOD] ← + galleryType
│   │   ├── ArtisticWorkController.php [MOD] ← + champs marketplace
│   │   ├── MessageController.php      [EXIST]
│   │   ├── ContactController.php      [EXIST]
│   │   ├── AdminController.php        [EXIST]
│   │   ├── MarketplaceController.php  [NEW] ← browse, search, filter
│   │   ├── OfferController.php        [NEW] ← new, accept, reject, cancel
│   │   ├── TransactionController.php  [NEW] ← historique, détail
│   │   └── StripeWebhookController.php [NEW] ← webhook sécurisé
│   │
│   ├── Entity/
│   │   ├── User.php                   [EXIST]
│   │   ├── Gallery.php                [MOD] ← + galleryType
│   │   ├── ArtisticWork.php           [MOD] ← + listingType, price, currency,
│   │   │                                        exchangeDescription, status
│   │   ├── Category.php               [EXIST]
│   │   ├── Message.php                [EXIST]
│   │   ├── Offer.php                  [NEW]
│   │   └── Transaction.php            [NEW]
│   │
│   ├── Form/
│   │   ├── GalleryType.php            [MOD] ← + galleryType field
│   │   ├── ArtisticWorkType.php       [MOD] ← + marketplace fields
│   │   ├── OfferType.php              [NEW]
│   │   └── (autres existants)         [EXIST]
│   │
│   ├── Repository/
│   │   ├── ArtisticWorkRepository.php [MOD] ← + findForMarketplace() avec filtres
│   │   ├── OfferRepository.php        [NEW]
│   │   ├── TransactionRepository.php  [NEW]
│   │   └── (autres existants)         [EXIST]
│   │
│   ├── Security/
│   │   ├── ArtisticWorkVoter.php      [NEW]
│   │   ├── OfferVoter.php             [NEW]
│   │   └── GalleryVoter.php           [NEW]
│   │
│   └── Service/
│       └── StripeService.php          [NEW] ← createPaymentIntent, handleWebhook
│
├── templates/
│   ├── base.html.twig                 [MOD] ← + lien marketplace, badge offres
│   ├── marketplace/
│   │   ├── index.html.twig            [NEW]
│   │   └── show.html.twig             [NEW]
│   ├── offer/
│   │   ├── new.html.twig              [NEW]
│   │   ├── received.html.twig         [NEW]
│   │   └── sent.html.twig             [NEW]
│   ├── transaction/
│   │   ├── history.html.twig          [NEW]
│   │   └── show.html.twig             [NEW]
│   ├── email/
│   │   ├── offer_received.html.twig   [NEW]
│   │   ├── offer_accepted.html.twig   [NEW]
│   │   ├── offer_rejected.html.twig   [NEW]
│   │   └── payment_confirmed.html.twig [NEW]
│   ├── gallery/                       [MOD] ← afficher galleryType
│   └── artistic_work/                 [MOD] ← afficher prix/échange/statut
│
└── migrations/
    ├── (existantes)                   [EXIST]
    ├── Version_001_gallery_type.php   [NEW]
    ├── Version_002_artwork_marketplace.php [NEW]
    ├── Version_003_offer.php          [NEW]
    └── Version_004_transaction.php    [NEW]
```

### Mapping Exigences → Structure

| FR | Domaine | Fichiers clés |
|---|---|---|
| FR1–FR3 | Annonces | `ArtisticWork.php` (MOD), `Gallery.php` (MOD), migrations |
| FR4 | Marketplace | `MarketplaceController`, `marketplace/*.twig`, `ArtisticWorkRepository` |
| FR5–FR6 | Offres | `OfferController`, `Offer.php`, `OfferType.php`, `OfferVoter.php` |
| FR7 | Paiement | `StripeService`, `StripeWebhookController`, `TransactionController` |
| FR8 | Transactions | `Transaction.php`, `TransactionRepository`, `transaction/*.twig` |

### Points d'intégration externes

| Service | Point d'entrée | Sécurité |
|---|---|---|
| Stripe API | `StripeService::createPaymentIntent()` | Clé secrète en `.env` |
| Stripe Webhook | `POST /stripe/webhook` | `STRIPE_WEBHOOK_SECRET` en `.env` |
| Symfony Mailer | `MailerInterface` injecté dans controllers | Config existante |
| Symfony Workflow | `WorkflowInterface` injecté | `config/packages/workflow.yaml` |

---

## Validation de l'Architecture

### Cohérence ✅

- Symfony Workflow ↔ Doctrine : compatibles nativement
- Stripe Checkout ↔ Webhook : flux cohérent `createPaymentIntent → paiement → webhook → Transaction`
- Voters ↔ AbstractController : `denyAccessUnlessGranted` natif
- KnpPaginator ↔ QueryBuilder : compatibilité native

### Couverture des Exigences ✅

| FR | Statut |
|---|---|
| FR1 — Type galerie/œuvre | ✅ `galleryType` + `listingType` |
| FR2 — Prix et échange | ✅ `price`, `currency`, `exchangeDescription` |
| FR3 — Statut | ✅ Symfony Workflow + ENUM `status` |
| FR4 — Marketplace | ✅ `MarketplaceController` + QueryBuilder + KnpPaginator |
| FR5 — Offres | ✅ `Offer` + `OfferController` + `OfferType` |
| FR6 — Gestion offres | ✅ `OfferVoter` + transitions Workflow |
| FR7 — Stripe | ✅ `StripeService` + `StripeWebhookController` |
| FR8 — Transactions | ✅ `Transaction` + `TransactionController` |

**NFR :** Zéro commission ✅ · Sécurité ✅ · Performance ✅ · Backward compatibility ✅

### Lacunes identifiées

| Priorité | Lacune | Action |
|---|---|---|
| ⚠️ Importante | `src/Security/` et `src/Service/` à créer | Lors de la première story |
| ⚠️ Importante | `STRIPE_WEBHOOK_SECRET` à ajouter en `.env` | Lors de la config Stripe |
| 💡 Nice-to-have | Tests unitaires Voters + StripeService | Phase 2 |
| 💡 Nice-to-have | Entité `Review` (avis post-transaction) | Phase 2 |

**Aucune lacune critique bloquante.**

### Checklist finale

- [x] Analyse du contexte et des exigences
- [x] Fondation technique (stack brownfield documentée)
- [x] Décisions architecturales cœur (données, sécurité, API, frontend, infra)
- [x] Patterns d'implémentation et règles de cohérence
- [x] Structure complète du projet (fichiers EXIST/MOD/NEW)
- [x] Validation et analyse des lacunes

**Statut : PRÊT POUR L'IMPLÉMENTATION — Confiance : Élevée**

### Séquence d'implémentation recommandée

1. Migrations DB (4 migrations dans l'ordre)
2. `config/packages/workflow.yaml`
3. `src/Security/` — 3 Voters
4. `src/Service/StripeService.php`
5. `MarketplaceController` + templates + `ArtisticWorkRepository::findForMarketplace()`
6. `OfferController` + `Offer` entity + `OfferType`
7. `StripeWebhookController` + `TransactionController`
8. Templates email + Symfony Mailer


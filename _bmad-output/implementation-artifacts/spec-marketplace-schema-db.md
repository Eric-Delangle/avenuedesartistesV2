---
title: 'Marketplace — Schéma DB (Goal A)'
type: 'feature'
created: '2026-03-30'
status: 'done'
baseline_commit: '0f83cc83088772d1af6fc4f911ad4325a2ba2db9'
context:
  - '_bmad-output/architecture.md'
  - '_bmad-output/prd.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Avenue des Artistes ne dispose pas des structures de données nécessaires pour permettre la vente et l'échange d'œuvres d'art entre utilisateurs.

**Approach:** Étendre les entités `ArtisticWork` et `Gallery` avec les champs marketplace, créer les entités `Offer` et `Transaction`, mettre à jour les Form Types correspondants, et générer les migrations Doctrine.

## Boundaries & Constraints

**Always:**
- Valeurs par défaut backward-compatible : `listingType='none'` et `galleryType='showcase'` — les galeries existantes continuent de fonctionner sans modification
- Utiliser les annotations Doctrine (`@ORM\Column`) cohérentes avec le style existant du projet
- `nullable=true` sur tous les champs marketplace optionnels (`price`, `currency`, `exchangeDescription`)
- Une migration par entité modifiée ou créée

**Ask First:**
- Si une migration existante entre en conflit avec les nouvelles (vérifier `migrations/` avant d'exécuter)

**Never:**
- Utiliser `doctrine:schema:update --force`
- Modifier les entités `User`, `Category`, `Message`
- Configurer Symfony Workflow (appartient au Goal C)
- Créer les controllers ou templates (Goals B, C, D)

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Œuvre existante après migration | Ligne `artistic_work` sans `listing_type` | `listing_type = 'none'`, `status = 'available'` | Migration avec DEFAULT |
| Galerie existante après migration | Ligne `gallery` sans `gallery_type` | `gallery_type = 'showcase'` | Migration avec DEFAULT |
| Création offre sans prix | `Offer` type=exchange, `offerPrice=null` | Persiste sans erreur | `nullable=true` sur `offerPrice` |
| Création offre achat sans `proposedWork` | `Offer` type=purchase, `proposedWork=null` | Persiste sans erreur | `nullable=true` sur `proposedWork` |

</frozen-after-approval>

## Code Map

- `src/Entity/ArtisticWork.php` -- entité à étendre : +listingType, +price, +currency, +exchangeDescription, +status
- `src/Entity/Gallery.php` -- entité à étendre : +galleryType (le champ `$slug` existant n'a pas de @ORM\Column — ne pas toucher, hors scope)
- `src/Entity/Offer.php` -- nouvelle entité à créer
- `src/Entity/Transaction.php` -- nouvelle entité à créer
- `src/Form/ArtisticWorkType.php` -- ajouter champs marketplace (ChoiceType listingType, NumberType price, TextareaType exchangeDescription)
- `src/Form/GalleryType.php` -- ajouter champ galleryType (ChoiceType)
- `migrations/` -- 4 nouvelles migrations à générer

## Tasks & Acceptance

**Execution:**
- [x] `src/Entity/ArtisticWork.php` -- Ajouter propriétés ORM : `$listingType` (string, défaut 'none'), `$price` (decimal nullable), `$currency` (string défaut 'EUR', nullable), `$exchangeDescription` (text nullable), `$status` (string, défaut 'available') + getters/setters
- [x] `src/Entity/Gallery.php` -- Ajouter propriété ORM : `$galleryType` (string, défaut 'showcase') + getter/setter
- [x] `src/Entity/Offer.php` -- Créer entité complète : id, type (string), status (string, défaut 'pending'), offerPrice (decimal nullable), offerMessage (text), proposedWork (ManyToOne ArtisticWork nullable onDelete=SET NULL), targetWork (ManyToOne ArtisticWork onDelete=SET NULL), sender (ManyToOne User onDelete=SET NULL), createdAt (datetime), updatedAt (datetime) + constructeur + getters/setters
- [x] `src/Entity/Transaction.php` -- Créer entité complète : id, type (string), amount (decimal nullable), stripePaymentIntentId (string nullable), status (string, défaut 'pending'), artwork (ManyToOne ArtisticWork onDelete=SET NULL), buyer (ManyToOne User onDelete=SET NULL), seller (ManyToOne User onDelete=SET NULL), offer (OneToOne Offer nullable onDelete=SET NULL), completedAt (datetime nullable) + getters/setters
- [x] `src/Form/ArtisticWorkType.php` -- Ajouter : ChoiceType `listingType` (choices: none/sale/exchange/both, labels français), MoneyType `price` (required false, currency EUR), TextareaType `exchangeDescription` (required false)
- [x] `src/Form/GalleryType.php` -- Ajouter : ChoiceType `galleryType` (choices: showcase/sale/exchange/mixed, labels français)
- [x] `migrations/` -- 4 migrations créées manuellement (MySQL non démarré) : Version20260330120001-120004

**Acceptance Criteria:**
- Given une galerie existante, when la migration est exécutée, then `gallery_type = 'showcase'` et l'affichage existant est inchangé
- Given une œuvre existante, when la migration est exécutée, then `listing_type = 'none'` et `status = 'available'` et l'affichage existant est inchangé
- Given un utilisateur connecté crée une offre d'achat, when il persiste l'entité Offer, then l'offre est enregistrée avec `status='pending'` et `type='purchase'`
- Given un utilisateur connecté crée une offre d'échange, when `offerPrice` est null et `proposedWork` est renseigné, then l'entité persiste sans erreur de validation
- Given le formulaire ArtisticWorkType, when `listingType='sale'`, then le champ `price` est visible et requis côté formulaire
- Given le formulaire GalleryType, when soumis avec `galleryType='mixed'`, then la galerie est sauvegardée avec la bonne valeur

## Design Notes

**ENUM simulé avec string Doctrine :**
Doctrine ORM 2.x (utilisé ici) ne supporte pas nativement les ENUMs MySQL via annotations simples. Utiliser `type="string"` avec une constante de classe pour les valeurs valides :

```php
// Dans ArtisticWork
public const LISTING_TYPES = ['none', 'sale', 'exchange', 'both'];
public const STATUSES = ['available', 'reserved', 'sold', 'exchanged'];

/** @ORM\Column(type="string", length=20, options={"default": "none"}) */
private string $listingType = 'none';
```

**Relations Offer → ArtisticWork (double ManyToOne) :**
`Offer` a deux relations vers `ArtisticWork` : `targetWork` (obligatoire) et `proposedWork` (nullable pour échange). Utiliser des `@ORM\JoinColumn` distincts avec `name` explicite pour éviter les conflits de colonnes.

## Verification

**Commands:**
- `php bin/console doctrine:schema:validate` -- expected: mapping is valid, database schema is in sync
- `php bin/console doctrine:migrations:migrate --no-interaction` -- expected: 4 nouvelles migrations exécutées sans erreur
- `php bin/console doctrine:mapping:info` -- expected: toutes les entités listées dont Offer et Transaction

**Manual checks (if no CLI):**
- Vérifier dans phpMyAdmin que les tables `offer` et `transaction` existent avec les bonnes colonnes
- Vérifier que les colonnes `gallery_type`, `listing_type`, `price`, `exchange_description`, `status` existent dans `gallery` et `artistic_work`
- Créer une galerie via l'interface — `galleryType` doit apparaître dans le formulaire

## Suggested Review Order

**Validation croisée (point d'entrée)**

- Contraintes `@Assert\Expression` price/proposedWork selon type d'offre — cœur du schéma
  [`Offer.php:7`](../../src/Entity/Offer.php#L7)

- Expression assert price requis si listingType sale
  [`ArtisticWork.php:17`](../../src/Entity/ArtisticWork.php#L17)

**Entités nouvelles**

- Entité Offer complète : types, statuts, relations double ArtisticWork
  [`Offer.php:11`](../../src/Entity/Offer.php#L11)

- Entité Transaction : buyer/seller distincts, completedAt auto sur setStatus('completed')
  [`Transaction.php:11`](../../src/Entity/Transaction.php#L11)

**Extensions d'entités existantes**

- Champs marketplace ArtisticWork : listingType, price, currency, exchangeDescription, status + helpers isForSale/isForExchange/isAvailable
  [`ArtisticWork.php:84`](../../src/Entity/ArtisticWork.php#L84)

- Champ galleryType Gallery + isMarketplace()
  [`Gallery.php:43`](../../src/Entity/Gallery.php#L43)

**Formulaires**

- ArtisticWorkType : listingType (ChoiceType), price (MoneyType), exchangeDescription (TextareaType)
  [`ArtisticWorkType.php:21`](../../src/Form/ArtisticWorkType.php#L21)

- GalleryType : galleryType (ChoiceType)
  [`GalleryType.php:18`](../../src/Form/GalleryType.php#L18)

**Migrations (ordre d'exécution)**

- gallery_type sur gallery (backward-compatible DEFAULT 'showcase')
  [`Version20260330120001.php:17`](../../migrations/Version20260330120001.php#L17)

- Champs marketplace sur artistic_work (currency NULL corrigé)
  [`Version20260330120002.php:17`](../../migrations/Version20260330120002.php#L17)

- Création table offer (2 FK vers artistic_work avec noms explicites)
  [`Version20260330120003.php:17`](../../migrations/Version20260330120003.php#L17)

- Création table transaction (FK offer en UNIQUE)
  [`Version20260330120004.php:17`](../../migrations/Version20260330120004.php#L17)

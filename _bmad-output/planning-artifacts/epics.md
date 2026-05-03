# Epics — Avenue des Artistes : Marketplace Artistique

**Projet :** avenuedesartistesV2
**PRD :** `_bmad-output/prd.md`
**Architecture :** `_bmad-output/architecture.md`
**Date :** 2026-05-02

---

## Epic 1: Fondations DB & Entités Marketplace

Mise en place du schéma de base de données et des entités Symfony nécessaires à la marketplace : champs `listingType`, `price`, `status` sur `ArtisticWork`, entité `Offer`, entité `Transaction`, abonnement Stripe.

### Story 1.1: Schéma DB et entités (Goal A)

Ajouter les champs marketplace à `ArtisticWork`, créer les entités `Offer` et `Transaction`, configurer les migrations Doctrine.

**Critères d'acceptation :**
- `ArtisticWork` possède `listingType` (none/sale/exchange/both), `price` (nullable), `status` (available/reserved/sold/exchanged)
- Entité `Offer` avec relations `sender`, `targetWork`, `status` (pending/accepted/rejected/cancelled)
- Entité `Transaction` avec références `buyer`, `seller`, `work`, `amount`, `type` (sale/exchange)
- Migrations générées et appliquées sans erreur

### Story 1.2: Abonnement Stripe (Goal D)

Intégration Stripe Checkout pour l'abonnement mensuel 4.99€/mois.

**Critères d'acceptation :**
- Flux Stripe Checkout fonctionnel avec redirection success/cancel
- Webhook Stripe configuré et vérifié par signature
- Statut abonnement visible dans le profil utilisateur

---

## Epic 2: Page Marketplace Browse

Création de la page publique de navigation des œuvres disponibles avec filtres et pagination.

### Story 2.1: Page Marketplace Browse (Goal B)

Créer `MarketplaceController`, repository `findForMarketplace()`, templates liste et détail.

**Critères d'acceptation :**
- `/marketplace` affiche une grille paginée 9 œuvres/page
- Filtres `type`, `category`, `priceMin`, `priceMax` fonctionnels via query string
- `/marketplace/{id}` retourne 404 si œuvre non disponible
- Lien "Marketplace" dans la navbar

---

## Epic 3: Système d'Offres

Permettre aux utilisateurs de faire des offres d'achat ou d'échange sur les œuvres marketplace.

### Story 3.1: Implémentation système d'offres (Goal C)

Créer `OfferController`, `OfferType`, `OfferVoter`, workflow Symfony pour les transitions de statut.

**Critères d'acceptation :**
- Bouton "Faire une offre" accessible depuis la page détail marketplace
- Formulaire d'offre avec montant (vente) ou description (échange)
- Notifications automatiques via entité `Message` existante
- Actions accept/reject/cancel pour le propriétaire
- Vues "offres reçues" et "offres envoyées"

### Story 3.2: Sécurité et validation des offres

Renforcement de la sécurité : interdire l'auto-offre, contrainte unicité offre en attente.

**Critères d'acceptation :**
- `OfferVoter` : `sender` ≠ `targetWork.gallery.user` (artiste ne peut pas s'offrir sa propre œuvre)
- Contrainte unique DB sur `(sender_id, target_work_id, status='pending')`
- Message d'erreur explicite si l'utilisateur n'est pas authentifié ("Connectez-vous pour faire une offre")
- Test : tentative d'auto-offre → réponse 403

---

## Epic 4: Qualité & Production

Correction des issues différées et pré-existantes avant mise en production.

### Story 4.1: Correction Gallery::$slug

Ajouter la propriété et l'annotation `@ORM\Column` manquantes sur `Gallery::$slug`.

**Critères d'acceptation :**
- `Gallery::$slug` possède une propriété PHP + annotation Doctrine correcte
- Migration générée sans perte de données
- Pas de régression sur les routes galerie existantes

### Story 4.2: Archivage références utilisateur

Définir et implémenter la stratégie de conservation des données `Offer`/`Transaction` lors de la suppression d'un compte.

**Critères d'acceptation :**
- Audit `Offer` et `Transaction` préservé après suppression du compte (soft-delete ou nullification contrôlée)
- Migration et mise à jour des relations FK
- Aucune perte d'historique de transaction

### Story 4.3: Corrections UX et données marketplace

Lot de petites corrections issues des review findings différés.

**Critères d'acceptation :**
- `?type=both` ignoré ou redirigé correctement (aucun cas réel)
- DEFAULT CURRENT_TIMESTAMP vérifié sur migration `artistic_work.created_at`
- Visiteur anonyme cliquant "Faire une offre" → redirect login avec message flash
- `createdAt` ordering documenté pour les données existantes

---

## Epic 5: Design & Expérience Utilisateur

Améliorations de l'interface graphique et de l'expérience utilisateur sur les pages existantes.

### Story 5.1: Refonte design page membres et finitions UI

Amélioration du style de la page membres, icônes, footer sticky, et cohérence visuelle.

**Critères d'acceptation :**
- Page membres avec nouveau style validé visuellement
- Footer sticky fonctionnel sur toutes les pages
- Icône œil dans le champ mot de passe (login/register)
- Titres dynamiques sur les pages galerie

### Story 5.2: SEO et métadonnées

Ajout des meta tags, Open Graph, JSON-LD, sitemap.xml, robots.txt.

**Critères d'acceptation :**
- Meta tags title/description sur toutes les pages principales
- Open Graph pour partage réseaux sociaux
- JSON-LD pour les œuvres marketplace
- sitemap.xml et robots.txt accessibles

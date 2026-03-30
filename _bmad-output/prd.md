# PRD — Avenue des Artistes : Marketplace Artistique

**Statut :** Micro-PRD (généré depuis contexte existant)
**Date :** 2026-03-30
**Auteur :** pol
**Projet :** Avenue des Artistes V2 → Marketplace

---

## 1. Vision Produit

Transformer la plateforme communautaire **Avenue des Artistes V2** en une **marketplace gratuite** (sans commission) permettant à chaque artiste inscrit de créer des galeries pour **vendre** et/ou **échanger** ses œuvres directement avec d'autres utilisateurs.

La plateforme reste **100% gratuite** pour son propriétaire et ses utilisateurs — aucune commission prélevée.

---

## 2. Contexte Existant (Brownfield)

**Stack technique :** Symfony 6.0, PHP 8.1, MySQL, Doctrine ORM, VichUploader, KnpPaginator, Stripe (clés configurées), Symfony Mailer.

**Entités existantes :**
- `User` — auth, profil, avatar, localisation, catégories, niveau
- `Gallery` — galerie d'un user, appartient à une catégorie
- `ArtisticWork` — œuvre avec image, description, appartient à une galerie
- `Category` — catégorisation des galeries et œuvres
- `Message` — messagerie directe entre utilisateurs

**Ce qui existe déjà :**
- Inscription/connexion avec activation email
- Création et gestion de galeries
- Upload d'œuvres avec images
- Messagerie entre utilisateurs
- Pagination, slugs, rôles (USER, ADMIN)

---

## 3. Objectifs

1. Permettre à chaque utilisateur de transformer ses galeries en espaces de vente et/ou d'échange
2. Afficher un marketplace global pour parcourir toutes les œuvres disponibles
3. Permettre aux acheteurs/échangeurs de faire des offres sur les œuvres
4. Gérer le cycle de vie d'une annonce (disponible → réservé → vendu/échangé)
5. Intégrer Stripe pour les paiements de vente (déjà configuré)
6. Système de notifications pour les offres reçues/acceptées/refusées

---

## 4. Fonctionnalités Requises

### FR1 — Type de galerie / œuvre
- Chaque galerie peut être marquée : `vente`, `échange`, `vente_et_échange`, `vitrine`
- Chaque œuvre hérite du type de sa galerie OU a son propre type

### FR2 — Prix et conditions d'échange
- Pour les œuvres en vente : prix (float) + devise (EUR par défaut)
- Pour les œuvres en échange : description de ce que l'artiste souhaite en échange

### FR3 — Statut des œuvres
- Statuts : `disponible`, `réservé`, `vendu`, `échangé`
- Seul le propriétaire peut changer le statut manuellement
- Le statut change automatiquement lors d'une transaction acceptée

### FR4 — Page Marketplace
- Vue globale de toutes les œuvres `disponibles`
- Filtres : type (vente/échange), catégorie, fourchette de prix, localisation
- Pagination existante (KnpPaginator)
- Tri : récent, prix croissant/décroissant

### FR5 — Offres (Offer)
- Pour une œuvre en vente : un acheteur soumet une offre d'achat (au prix demandé ou négocié)
- Pour une œuvre en échange : un utilisateur soumet une proposition d'échange (description + optionnellement une de ses œuvres)
- Le propriétaire reçoit une notification (message interne)

### FR6 — Gestion des offres
- Le propriétaire peut Accepter / Refuser / Contrer chaque offre
- Si acceptée pour vente → déclenche le flux de paiement Stripe
- Si acceptée pour échange → marque les deux œuvres comme `échangées`

### FR7 — Paiement Stripe
- Stripe Checkout simple (paiement vers compte plateforme)
- Le vendeur est notifié du paiement via email et message interne
- Pas de Stripe Connect (trop complexe pour une plateforme gratuite/personnelle)

### FR8 — Historique des transactions
- Entité `Transaction` enregistrant chaque vente ou échange réalisé
- Visible dans le profil de l'utilisateur (acheteur et vendeur)

### FR9 — Avis / Notes (optionnel, phase 2)
- Après transaction complétée, les deux parties peuvent laisser un avis

---

## 5. Non-Fonctionnels

- **Performance :** Pagination sur le marketplace, images lazy-loaded
- **Sécurité :** Seul le propriétaire gère ses annonces ; paiements via Stripe HTTPS
- **Gratuité :** 0% de commission — aucun modèle monétaire
- **Responsive :** Continuité avec le design existant
- **Langue :** Interface en français

---

## 6. Hors Périmètre (pour l'instant)

- Stripe Connect (paiements directs vers vendeurs)
- Application mobile native
- Système d'enchères
- Frais de livraison / expédition
- Authentification OAuth (Google, Facebook)

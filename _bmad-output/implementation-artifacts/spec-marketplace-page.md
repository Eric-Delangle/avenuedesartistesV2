---
title: 'Marketplace — Page Browse (Goal B)'
type: 'feature'
created: '2026-03-30'
status: 'done'
baseline_commit: '6c6b6be5dd67078e6f49444eb7493c51cb8f4228'
context:
  - '_bmad-output/architecture.md'
  - '_bmad-output/prd.md'
---

<frozen-after-approval reason="human-owned intent — do not modify unless human renegotiates">

## Intent

**Problem:** Il n'existe aucune page permettant aux visiteurs de parcourir les œuvres disponibles à la vente ou à l'échange sur Avenue des Artistes.

**Approach:** Créer un `MarketplaceController` avec une page liste filtrée (type/catégorie/prix) paginée via KnpPaginator, une page détail par œuvre, et ajouter le lien "Marketplace" dans la navbar.

## Boundaries & Constraints

**Always:**
- N'afficher que les œuvres avec `listingType != 'none'` et `status = 'available'`
- Suivre le pattern existant : annotation routing, `ManagerRegistry`, `PaginatorInterface`, PRG sur les formulaires
- Bootstrap 4 Cerulean — pas de nouveaux composants CSS/JS
- 9 œuvres par page (3 colonnes × 3 rangées)

**Ask First:**
- Si le filtre "localisation" (ville/département) doit être ajouté dès maintenant — non prévu car `ArtisticWork` n'a pas de champ localisation

**Never:**
- Créer de formulaire d'offre ici (Goal C)
- Afficher les œuvres `listingType='none'` ou `status != 'available'`
- Modifier les entités existantes (hors scope)

## I/O & Edge-Case Matrix

| Scenario | Input / State | Expected Output / Behavior | Error Handling |
|----------|--------------|---------------------------|----------------|
| Aucune œuvre disponible | Base vide ou tout en vitrine | Message "Aucune œuvre disponible" — page ne plante pas | Twig `{% if works is empty %}` |
| Filtre type=sale | `?type=sale` en query string | Seules les œuvres `listingType IN (sale, both)` | Valeur ignorée si invalide |
| Filtre catégorie | `?category=2` | Seules les œuvres de cette catégorie | 404 si catégorie inexistante ignorée (filtre annulé) |
| Filtre prix max | `?priceMax=100` | Seules les œuvres avec `price <= 100` | Ignoré si non-numérique |
| Œuvre sans prix (échange seul) | `listingType=exchange`, `price=null` | Affichée — badge "Échange" sans prix | — |
| Page détail œuvre vendue/réservée | `status != available` | Accès direct `/marketplace/{id}` → 404 | `throw $this->createNotFoundException()` |

</frozen-after-approval>

## Code Map

- `src/Controller/MarketplaceController.php` -- nouveau controller à créer
- `src/Repository/ArtisticWorkRepository.php` -- ajouter `findForMarketplace(array $filters): Query`
- `templates/marketplace/index.html.twig` -- page liste avec filtres + pagination
- `templates/marketplace/show.html.twig` -- page détail d'une œuvre
- `templates/partials/_navbar.html.twig` -- ajouter lien "Marketplace"
- `src/Entity/ArtisticWork.php` -- référence pour champs listingType, price, status, category
- `src/Entity/Category.php` -- référence pour le filtre catégorie (id, name)
- `src/Controller/GalleryController.php` -- pattern KnpPaginator à reproduire

## Tasks & Acceptance

**Execution:**
- [x] `src/Repository/ArtisticWorkRepository.php` -- Ajouter `findForMarketplace(array $filters): \Doctrine\ORM\Query` : QueryBuilder sur `listingType != 'none'` AND `status = 'available'`, avec filtres optionnels `type` (sale/exchange/both), `category` (int id), `priceMin` (decimal), `priceMax` (decimal), tri par `createdAt DESC`
- [x] `src/Controller/MarketplaceController.php` -- Créer controller `@Route("/marketplace")` avec deux actions : `index` (GET, filtres via query string, pagination 9/page) et `show` (GET `/{id}`, 404 si non disponible)
- [x] `templates/marketplace/index.html.twig` -- Page browse : formulaire GET filtres (type ChoiceType, category select, priceMin/priceMax), grille Bootstrap 3 colonnes de cards (photo, nom, badge type, prix ou "Échange"), pagination KnpPaginator
- [x] `templates/marketplace/show.html.twig` -- Page détail : photo large, nom, description, prix/mode d'échange, galerie source (lien), bouton "Faire une offre" (désactivé Goal C)
- [x] `templates/partials/_navbar.html.twig` -- Ajouter `<li class="nav-item bleu"><a class="nav-link massenet" href="{{ path('marketplace_index') }}">Marketplace</a></li>` après le lien "Echanger"

**Acceptance Criteria:**
- Given la marketplace avec des œuvres disponibles, when je visite `/marketplace`, then je vois une grille paginée d'œuvres avec badge type et prix
- Given un filtre `?type=sale`, when la page se charge, then seules les œuvres `listingType IN (sale, both)` apparaissent
- Given un filtre `?category=2`, when la page se charge, then seules les œuvres de cette catégorie apparaissent
- Given une œuvre avec `status=sold`, when j'accède à `/marketplace/{id}`, then j'obtiens une 404
- Given la navbar, when je clique "Marketplace", then j'arrive sur `/marketplace`
- Given aucune œuvre disponible, when je visite `/marketplace`, then le message "Aucune œuvre disponible pour le moment." s'affiche sans erreur

## Design Notes

**QueryBuilder — filtre `type` (sale/exchange) :**
`listingType IN ('sale', 'both')` pour `type=sale`, `listingType IN ('exchange', 'both')` pour `type=exchange`. Pas de filtre si `type=all` ou absent.

```php
// Dans ArtisticWorkRepository::findForMarketplace()
$qb = $this->createQueryBuilder('a')
    ->join('a.category', 'c')
    ->where("a.listingType != 'none'")
    ->andWhere("a.status = 'available'")
    ->orderBy('a.createdAt', 'DESC');

if (!empty($filters['type']) && $filters['type'] === 'sale') {
    $qb->andWhere("a.listingType IN ('sale', 'both')");
} elseif (!empty($filters['type']) && $filters['type'] === 'exchange') {
    $qb->andWhere("a.listingType IN ('exchange', 'both')");
}
if (!empty($filters['category'])) {
    $qb->andWhere('c.id = :cat')->setParameter('cat', (int)$filters['category']);
}
if (!empty($filters['priceMax']) && is_numeric($filters['priceMax'])) {
    $qb->andWhere('a.price <= :pmax')->setParameter('pmax', $filters['priceMax']);
}
return $qb->getQuery();
```

**Card badge :** `listingType=sale` → badge `badge-success "Vente"`, `exchange` → `badge-info "Échange"`, `both` → deux badges.

## Verification

**Commands:**
- `php bin/console debug:router | grep marketplace` -- expected: routes `marketplace_index` et `marketplace_show` listées
- `php bin/console lint:twig templates/marketplace/` -- expected: no errors

**Manual checks (if no CLI):**
- Visiter `/marketplace` — grille visible, filtre fonctionnel, pagination si > 9 œuvres
- Visiter `/marketplace/{id}` avec une œuvre disponible — page détail correcte
- Visiter `/marketplace/{id}` avec une œuvre `status=sold` — 404

## Suggested Review Order

**Point d'entrée — filtre QueryBuilder**

- Filtre principal : listingType/status guards + eager-load catégorie (`addSelect`)
  [`ArtisticWorkRepository.php:22`](../../src/Repository/ArtisticWorkRepository.php#L22)

- Filtres optionnels type/catégorie/prix avec cast float et IS NULL guard
  [`ArtisticWorkRepository.php:30`](../../src/Repository/ArtisticWorkRepository.php#L30)

**Controller — routing et sécurité d'accès**

- Action `index` : extraction filtres query string, page clamped `max(1, ...)`, pagination 9/page
  [`MarketplaceController.php:22`](../../src/Controller/MarketplaceController.php#L22)

- Action `show` : 404 explicite si listingType=none ou status!=available
  [`MarketplaceController.php:51`](../../src/Controller/MarketplaceController.php#L51)

**Templates — rendu et UX**

- Grille cards 3 colonnes : badges sale/exchange, prix ou "Prix sur échange", lien détail
  [`marketplace/index.html.twig:58`](../../templates/marketplace/index.html.twig#L58)

- Page détail : prix avec fallback "Prix sur échange" pour les œuvres exchange-only
  [`marketplace/show.html.twig:42`](../../templates/marketplace/show.html.twig#L42)

**Config et navigation**

- KnpPaginator Bootstrap 4 template (évite le rendu non-stylisé par défaut)
  [`knp_paginator.yaml:1`](../../config/packages/knp_paginator.yaml#L1)

- Lien navbar "Marketplace" après "Echanger"
  [`_navbar.html.twig:14`](../../templates/partials/_navbar.html.twig#L14)

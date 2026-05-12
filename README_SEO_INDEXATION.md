# SEO Indexation

## Architecture

- `app/Http/Controllers/AdPublicController.php`
  Gère les annonces publiques, les métadonnées uniques, la canonical, le `noindex, follow` conditionnel, le maillage interne et l'optimisation HTML des images.
- `app/Services/AdSeoAuditService.php`
  Attribue un score SEO aux annonces. Les annonces faibles peuvent rester crawlables en `noindex, follow` et sont exclues du sitemap.
- `app/Services/SitemapService.php`
  Génère un index `sitemap.xml` et des sous-sitemaps thématiques dans `public/sitemap/`:
  `pages-core.xml`, `services.xml`, `articles.xml`, `portfolio.xml`, puis `ads-service-*.xml`.
- `resources/views/ads/show.blade.php`
  Rendu SEO local: contenu visible, FAQ éditoriale, maillage interne, lazy loading et JSON-LD `ProfessionalService` / `RoofingContractor` + `Service`.
- `resources/views/partials/schema-org.blade.php`
  Schéma global de marque/entreprise pour tout le site.

## Audit SEO des annonces

Commande:

```bash
php artisan seo:audit-ads --sample=500
php artisan seo:audit-ads --all
```

Sorties:

- `storage/app/seo-audits/ads-audit-*.json`
- `storage/app/seo-audits/ads-audit-*.csv`

Interprétation:

- `score >= 80`: garder indexable
- `score 60-79`: enrichir le contenu et le maillage
- `score < 60`: réécrire, fusionner ou laisser en `noindex, follow`
- `duplicate_fingerprint_groups > 0`: prioriser les contenus trop proches
- `thin_content_ads > 0`: renforcer les pages trop courtes avant toute demande d'indexation

## Sitemaps

Le sitemap principal est `https://artisan-louis-hoffmann.fr/sitemap.xml`.

Il référence les sous-sitemaps publics situés dans `public/sitemap/`. Les annonces non indexables selon `AdSeoAuditService` ne sont pas ajoutées.

Régénération manuelle:

```bash
php artisan sitemap:generate-daily
```

## Routine recommandée

- Audit quotidien ou hebdomadaire:
  `php artisan seo:audit-ads --sample=1000`
- Régénération sitemap après audit ou après lot de publications:
  `php artisan sitemap:generate-daily`
- Vérification Search Console:
  contrôler `Crawled - currently not indexed`, `Discovered - currently not indexed`, et inspecter un échantillon d'URLs stratégiques.

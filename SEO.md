# Documentation SEO - Optimisations Implémentées

## 📦 Packages Installés

- ✅ `ralphjsmit/laravel-seo` - Gestion automatique des meta tags SEO
- ✅ `spatie/laravel-sitemap` - Génération de sitemap XML
- ✅ `spatie/laravel-sluggable` - Génération automatique de slugs
- ✅ `intervention/image` - Optimisation d'images

## 🗄️ Migrations Créées

### Services
- Ajout de champs SEO : `slug`, `meta_title`, `meta_description`, `og_image`, `price_from`, `order`
- Index sur `slug`, `is_active`, `order`

### Cities
- Ajout de champs SEO local : `slug`, `description`, `latitude`, `longitude`, `phone`, `email`, `meta_title`, `meta_description`, `is_active`
- Index sur `slug`, `is_active`

### Articles
- Ajout de `is_published`, `author_id`
- Index composé sur `is_published` et `published_at`

## 🎯 Modèles Améliorés

### Service Model
- ✅ Trait `HasSEO` pour meta tags automatiques
- ✅ Trait `HasSlug` pour slugs auto-générés
- ✅ Méthode `getDynamicSEOData()` pour SEO personnalisé
- ✅ Scopes `active()` et `ordered()`

### City Model
- ✅ Trait `HasSEO` pour SEO local
- ✅ Trait `HasSlug` pour slugs auto-générés
- ✅ Méthode `getDynamicSEOData()` optimisée pour le SEO local
- ✅ Scope `active()`

### Article Model
- ✅ Trait `HasSEO` avec type 'article'
- ✅ Trait `HasSlug` pour slugs auto-générés
- ✅ Méthode `getDynamicSEOData()` avec dates de publication
- ✅ Scopes `published()`, `latest()`, `draft()`
- ✅ Relation `author()`

## 🛠️ Composants Créés

### Breadcrumbs Component
- ✅ Composant Blade réutilisable avec Schema.org BreadcrumbList
- ✅ Utilisation : `<x-breadcrumbs :breadcrumbs="$breadcrumbs" />`

### Middleware CanonicalUrl
- ✅ Ajoute automatiquement les headers `Link: canonical`
- ✅ Nettoie les query strings inutiles
- ✅ Enregistré dans le groupe `web`

## 🗺️ Sitemap Amélioré

### SitemapController
- ✅ Utilise Spatie Sitemap avec cache (24h)
- ✅ Inclut : Homepage, Services, Cities, Articles, Pages statiques, Pages légales
- ✅ Priorités et fréquences de mise à jour optimisées

## 📝 Commandes Artisan

### `php artisan seo:validate`
Valide la configuration SEO complète :
- ✅ Packages installés
- ✅ Configuration SEO
- ✅ Sitemap accessible
- ✅ Robots.txt
- ✅ Services et villes présents
- ✅ Routes principales
- ✅ HTTPS en production

### `php artisan cache:clear-all`
Vide tous les caches Laravel :
- Configuration
- Routes
- Vues
- Cache applicatif
- Optimisations

### `php artisan deploy`
Déploie l'application avec optimisations :
- Mode maintenance
- Optimisation autoloader
- Migrations
- Cache de configuration/routes/vues
- Génération sitemap
- Désactivation maintenance

## 🎨 Utilisation dans les Vues

### Utiliser le SEO automatique dans une vue

```blade
@extends('layouts.app')

@section('head')
    {!! seo($service) !!} {{-- Pour un Service --}}
    {!! seo($city) !!}     {{-- Pour une City --}}
    {!! seo($article) !!}  {{-- Pour un Article --}}
@endsection
```

### Ajouter des Breadcrumbs

```php
$breadcrumbs = [
    ['label' => 'Accueil', 'url' => route('home')],
    ['label' => 'Services', 'url' => route('services.index')],
    ['label' => $service->title], // Pas d'URL = page courante
];
```

```blade
<x-breadcrumbs :breadcrumbs="$breadcrumbs" />
```

### Ajouter des Schémas JSON-LD

```blade
@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "{{ $service->title }}",
    "description": "{{ strip_tags($service->description) }}",
    "provider": {
        "@type": "LocalBusiness",
        "name": "{{ setting('company_name') }}"
    }
}
</script>
@endpush
```

## 🔧 Configuration

### Fichier `config/seo.php`
Configuré avec :
- `site_name` : Nom du site
- `title_suffix` : Suffixe par défaut
- `canonical_link` : true
- `robots` : Par environnement (production: index,follow)

## 📊 Checklist Avant Publication

- [ ] Meta title < 60 caractères
- [ ] Meta description entre 150-160 caractères
- [ ] Image OG définie (1200x630px recommandé)
- [ ] Slug optimisé (pas de caractères spéciaux)
- [ ] Contenu > 300 mots
- [ ] Liens internes ajoutés
- [ ] Breadcrumbs présents
- [ ] Schema JSON-LD ajouté si pertinent

## 🚀 Maintenance

- Le sitemap se régénère automatiquement via le cache (24h)
- Utiliser `php artisan seo:validate` pour vérifier la configuration
- Utiliser `php artisan cache:clear-all` après modifications importantes

## 📈 Prochaines Étapes Recommandées

1. Ajouter des schémas JSON-LD dans les vues principales (Home, Services, Articles)
2. Configurer le scheduler pour régénérer le sitemap automatiquement
3. Optimiser les images avec Intervention Image
4. Ajouter des tests SEO automatiques
5. Configurer Google Search Console


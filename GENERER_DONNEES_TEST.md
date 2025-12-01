# 🧪 Générer des données de test

## Commande

### Avec suppression des données existantes (recommandé)

```bash
php artisan generate:test-data --force
```

### Sans suppression (ajoute aux données existantes)

```bash
php artisan generate:test-data
```

⚠️ **Attention** : Sans l'option `--force`, les nouvelles données seront **ajoutées** aux données existantes. Utilisez `--force` pour tout supprimer avant de générer.

## Données générées

### Appels téléphoniques (57 au total)

- **32 appels** depuis **Chevigny-Saint-Sauveur**
- **25 appels** répartis sur les autres villes de Côte-d'Or :
  - Dijon
  - Beaune
  - Chenôve
  - Talant
  - Quetigny
  - Et autres villes de la région
  
- **Période** : du 28 octobre 2025 au 29 novembre 2025
- **Heures** : entre 8h et 20h (heures de bureau réalistes)
- **Pages sources** : variées (home, ads, services, contact, etc.)

### Visites (1980 au total)

- **1189 visites** depuis **Google Search**
  - Avec requêtes réalistes (couvreur chevigny, rénovation toiture dijon, etc.)
  
- **15 visites** depuis **Google My Business**
  - Via Google Maps
  
- **776 visites** depuis **autres sources**
  - Accès directs
  - Autres moteurs de recherche (Bing, Yahoo)
  - Réseaux sociaux
  - Autres sites web

- **Période** : du 28 octobre 2025 au 29 novembre 2025
- **Répartition géographique** : principalement Côte-d'Or, avec 30% de Chevigny-Saint-Sauveur
- **Pages visitées** : variées (accueil, services, annonces, contact, portfolio, etc.)

## Caractéristiques des données

### Réalisme
- ✅ User agents de vrais navigateurs (Chrome, Firefox, Safari, Edge)
- ✅ IPs françaises (plages privées pour test)
- ✅ Villes réelles de Côte-d'Or
- ✅ Dates et heures distribuées naturellement
- ✅ Durées de visite réalistes (10 à 300 secondes)
- ✅ Référents Google Search avec vraies requêtes

### Détection bots
- ✅ Tous les user agents sont de vrais navigateurs (pas de bots)
- ✅ `is_bot = false` pour toutes les données générées
- ✅ Les données apparaîtront dans les statistiques normales

## Utilisation

### Exécuter la commande en SSH

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html

# Supprimer toutes les données existantes et générer les nouvelles
php artisan generate:test-data --force
```

### Vérifier les données générées

```bash
# Vérifier les appels
php artisan tinker
>>> \App\Models\PhoneCall::count()
>>> \App\Models\PhoneCall::where('city', 'Chevigny-Saint-Sauveur')->count()
>>> exit

# Vérifier les visites
php artisan tinker
>>> \App\Models\Visit::count()
>>> \App\Models\Visit::where('referrer_url', 'like', '%google.com/search%')->count()
>>> exit
```

## Attention

⚠️ **Cette commande ajoute des données à votre base de données existante.**

- Les données sont ajoutées (pas remplacées)
- Si vous relancez la commande, vous ajouterez encore plus de données
- Pour supprimer, utilisez l'interface admin ou directement en base

## Supprimer les données de test

Si vous voulez supprimer les données générées :

```bash
php artisan tinker
```

```php
// Supprimer tous les appels (ATTENTION : supprime TOUS les appels !)
// \App\Models\PhoneCall::truncate();

// Ou supprimer seulement ceux créés après une date
// \App\Models\PhoneCall::where('created_at', '>=', '2025-11-30 00:00:00')->delete();

// Même chose pour les visites
// \App\Models\Visit::truncate();

exit
```

## Résultat attendu

Après exécution :

- **57 appels** dans `/admin/phone-calls`
  - 32 depuis Chevigny-Saint-Sauveur
  - 25 depuis autres villes
  
- **1980 visites** dans `/admin/visits`
  - 1189 avec referrer Google Search
  - 15 avec referrer Google Maps/Business
  - 776 autres sources

Les statistiques devraient maintenant montrer :
- Des appels répartis sur la période
- Des visites avec une bonne répartition des sources
- Des données réalistes pour tester l'interface admin


  
- **1980 visites** dans `/admin/visits`
  - 1189 avec referrer Google Search
  - 15 avec referrer Google Maps/Business
  - 776 autres sources

Les statistiques devraient maintenant montrer :
- Des appels répartis sur la période
- Des visites avec une bonne répartition des sources
- Des données réalistes pour tester l'interface admin


  
- **1980 visites** dans `/admin/visits`
  - 1189 avec referrer Google Search
  - 15 avec referrer Google Maps/Business
  - 776 autres sources

Les statistiques devraient maintenant montrer :
- Des appels répartis sur la période
- Des visites avec une bonne répartition des sources
- Des données réalistes pour tester l'interface admin


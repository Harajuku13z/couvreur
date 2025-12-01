# 🚀 Commande SSH pour générer les données de test

## Commande complète

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html && php artisan generate:test-data --force
```

## Explication

- `cd ...` : Se placer dans le répertoire du site
- `php artisan generate:test-data --force` : Lancer la commande avec suppression des données existantes

## Résultat attendu

La commande va :
1. ✅ Supprimer toutes les données existantes (appels et visites)
2. ✅ Générer 57 appels téléphoniques :
   - 32 depuis Chevigny-Saint-Sauveur
   - 25 depuis autres villes de Côte-d'Or
   - Période : 28 octobre 2025 au 29 novembre 2025
3. ✅ Générer 1980 visites :
   - 1189 depuis Google Search
   - 15 depuis Google My Business
   - 776 depuis autres sources
   - Période : 28 octobre 2025 au 29 novembre 2025

## Vérification après exécution

```bash
php artisan tinker
```

Puis dans tinker :
```php
\App\Models\PhoneCall::count()      // Doit afficher 57
\App\Models\PhoneCall::where('city', 'Chevigny-Saint-Sauveur')->count()  // Doit afficher 32
\App\Models\Visit::count()          // Doit afficher 1980
\App\Models\Visit::where('referrer_url', 'like', '%google.com/search%')->count()  // Doit afficher 1189
exit
```


## Commande complète

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html && php artisan generate:test-data --force
```

## Explication

- `cd ...` : Se placer dans le répertoire du site
- `php artisan generate:test-data --force` : Lancer la commande avec suppression des données existantes

## Résultat attendu

La commande va :
1. ✅ Supprimer toutes les données existantes (appels et visites)
2. ✅ Générer 57 appels téléphoniques :
   - 32 depuis Chevigny-Saint-Sauveur
   - 25 depuis autres villes de Côte-d'Or
   - Période : 28 octobre 2025 au 29 novembre 2025
3. ✅ Générer 1980 visites :
   - 1189 depuis Google Search
   - 15 depuis Google My Business
   - 776 depuis autres sources
   - Période : 28 octobre 2025 au 29 novembre 2025

## Vérification après exécution

```bash
php artisan tinker
```

Puis dans tinker :
```php
\App\Models\PhoneCall::count()      // Doit afficher 57
\App\Models\PhoneCall::where('city', 'Chevigny-Saint-Sauveur')->count()  // Doit afficher 32
\App\Models\Visit::count()          // Doit afficher 1980
\App\Models\Visit::where('referrer_url', 'like', '%google.com/search%')->count()  // Doit afficher 1189
exit
```


## Commande complète

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html && php artisan generate:test-data --force
```

## Explication

- `cd ...` : Se placer dans le répertoire du site
- `php artisan generate:test-data --force` : Lancer la commande avec suppression des données existantes

## Résultat attendu

La commande va :
1. ✅ Supprimer toutes les données existantes (appels et visites)
2. ✅ Générer 57 appels téléphoniques :
   - 32 depuis Chevigny-Saint-Sauveur
   - 25 depuis autres villes de Côte-d'Or
   - Période : 28 octobre 2025 au 29 novembre 2025
3. ✅ Générer 1980 visites :
   - 1189 depuis Google Search
   - 15 depuis Google My Business
   - 776 depuis autres sources
   - Période : 28 octobre 2025 au 29 novembre 2025

## Vérification après exécution

```bash
php artisan tinker
```

Puis dans tinker :
```php
\App\Models\PhoneCall::count()      // Doit afficher 57
\App\Models\PhoneCall::where('city', 'Chevigny-Saint-Sauveur')->count()  // Doit afficher 32
\App\Models\Visit::count()          // Doit afficher 1980
\App\Models\Visit::where('referrer_url', 'like', '%google.com/search%')->count()  // Doit afficher 1189
exit
```




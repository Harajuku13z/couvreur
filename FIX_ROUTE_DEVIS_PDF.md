# Fix Route devis.public.pdf

## Problème
La route `devis.public.pdf` n'est pas trouvée sur le serveur de production.

## Solution

### 1. Vider tous les caches sur le serveur

Connectez-vous en SSH sur le serveur et exécutez :

```bash
cd /chemin/vers/votre/projet

# Vider les caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Optimiser les routes (optionnel mais recommandé)
php artisan route:cache
php artisan config:cache
```

### 2. Vérifier que la route existe

```bash
php artisan route:list --name=devis.public
```

Vous devriez voir :
```
GET|HEAD  devis/public/{id}/{token} devis.public.pdf › Admin\DevisController@publicPdf
```

### 3. Si ça ne fonctionne toujours pas

Vérifiez que le fichier `routes/web.php` contient bien :

```php
// Route publique pour accéder au PDF d'un devis avec token (hors du groupe admin)
Route::get('/devis/public/{id}/{token}', [DevisController::class, 'publicPdf'])
    ->name('devis.public.pdf');
```

Cette route doit être **EN DEHORS** du groupe `admin` (pas dans `Route::prefix('admin')`).

### 4. Script de vérification

Vous pouvez aussi exécuter le script `clear-routes-cache.php` :

```bash
php clear-routes-cache.php
```

### 5. Redémarrer les services (si nécessaire)

Sur certains serveurs, il peut être nécessaire de redémarrer PHP-FPM ou le serveur web :

```bash
# Pour PHP-FPM
sudo service php8.2-fpm restart
# ou
sudo systemctl restart php-fpm

# Pour Nginx
sudo service nginx restart
# ou
sudo systemctl restart nginx
```

## Vérification finale

Testez l'URL directement dans le navigateur (remplacez {id} et {token} par des valeurs réelles) :

```
https://www.jd-renovation-service.fr/devis/public/15/[token]
```

Si vous obtenez une erreur 403, c'est normal (token invalide), mais si vous obtenez une erreur 404, la route n'est pas correctement enregistrée.


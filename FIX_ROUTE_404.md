# 🔧 Corriger l'erreur 404 sur /api/track-phone-call

## Problème

La route `/api/track-phone-call` retourne une page 404 HTML au lieu d'une réponse JSON.

## Solution : Vider le cache des routes

En SSH, exécuter ces commandes :

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html

# Vider le cache des routes (CRUCIAL)
php artisan route:clear

# Vider tous les caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Vérifier que la route est bien enregistrée
php artisan route:list | grep track-phone-call
```

**La commande devrait afficher :**
```
POST   api/track-phone-call ................................ api.track.phone.call
```

## Si la route n'apparaît pas

### 1. Vérifier que le fichier routes/web.php est à jour

```bash
grep "track-phone-call" routes/web.php
```

**Devrait afficher :**
```
Route::post('/api/track-phone-call', [FormControllerSimple::class, 'trackPhoneCall'])->name('api.track.phone.call');
```

### 2. Si le fichier n'est pas à jour, pull les dernières modifications

```bash
git pull origin main
```

### 3. Vérifier les permissions et la structure

```bash
# Vérifier que le contrôleur existe
ls -la app/Http/Controllers/FormControllerSimple.php

# Vérifier la méthode trackPhoneCall
grep "public function trackPhoneCall" app/Http/Controllers/FormControllerSimple.php
```

### 4. Forcer la recompilation des routes

```bash
# Supprimer le cache bootstrap
rm -rf bootstrap/cache/*.php

# Recréer le cache
php artisan route:cache
# Ou si route:cache ne fonctionne pas, utiliser route:clear
php artisan route:clear
```

## Test après correction

```bash
# Tester avec curl
curl -X POST https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0612345678","source_page":"test-ssh"}'
```

**Devrait retourner du JSON, par exemple :**
```json
{"success":true,"id":123,"message":"Appel tracké avec succès"}
```

**Ou en cas d'erreur :**
```json
{"success":false,"error":"..."}
```

**Mais PAS de HTML 404 !**

## Si ça ne fonctionne pas encore

### Vérifier le middleware

```bash
# Vérifier que le CSRF est bien exclu
grep "track-phone-call" app/Http/Middleware/VerifyCsrfToken.php
grep "track-phone-call" bootstrap/app.php
```

### Vérifier les logs

```bash
# Voir si la requête arrive au serveur
tail -f storage/logs/laravel.log | grep "trackPhoneCall"
```

### Vérifier la configuration du serveur web

Si vous utilisez Apache/Nginx, vérifier que les routes Laravel sont bien configurées pour rediriger vers `public/index.php`.

## Commandes complètes de réparation

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html

# 1. Pull les dernières modifications
git pull origin main

# 2. Vider tous les caches
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# 3. Supprimer les caches bootstrap
rm -rf bootstrap/cache/*.php

# 4. Vérifier la route
php artisan route:list | grep track-phone-call

# 5. Tester
curl -X POST https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0612345678","source_page":"test"}'
```

## Vérification finale

1. ✅ La route apparaît dans `php artisan route:list`
2. ✅ curl retourne du JSON (pas du HTML)
3. ✅ Le bouton "Tester le tracking" dans l'admin fonctionne
4. ✅ Les appels sont trackés dans les logs


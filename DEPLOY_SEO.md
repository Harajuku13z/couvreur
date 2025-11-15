# 🚀 Guide de Déploiement SEO sur le Serveur

## ⚠️ Erreur : "Trait HasSEO not found"

Cette erreur signifie que les packages SEO ne sont **pas installés** sur votre serveur.

## 📋 Étapes de Déploiement

### 1. Se connecter en SSH au serveur

```bash
ssh votre-utilisateur@votre-serveur
cd /chemin/vers/votre/projet
```

### 2. Récupérer les dernières modifications

```bash
git pull origin main
```

### 3. Installer les packages Composer

**IMPORTANT** : Exécutez cette commande pour installer les nouveaux packages SEO :

```bash
composer install --no-dev --optimize-autoloader
```

Cette commande va installer :
- `ralphjsmit/laravel-seo`
- `spatie/laravel-sluggable`
- `intervention/image`

### 4. Régénérer l'autoload

```bash
composer dump-autoload
```

### 5. Publier les configurations SEO (si nécessaire)

```bash
php artisan vendor:publish --tag=seo-config
```

### 6. Exécuter les migrations

```bash
php artisan migrate --force
```

### 7. Vider les caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 8. Mettre en cache (production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 9. Vérifier que tout fonctionne

```bash
php artisan seo:validate
```

## 🔧 Script Automatique

Vous pouvez créer un fichier `deploy-seo.sh` sur votre serveur :

```bash
#!/bin/bash
echo "🚀 Déploiement SEO en cours..."

# Récupérer les modifications
git pull origin main

# Installer les packages
composer install --no-dev --optimize-autoloader

# Régénérer l'autoload
composer dump-autoload

# Publier les configs
php artisan vendor:publish --tag=seo-config --force

# Migrations
php artisan migrate --force

# Vider les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Mettre en cache (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Déploiement terminé!"
```

Rendre exécutable :
```bash
chmod +x deploy-seo.sh
./deploy-seo.sh
```

## ⚠️ Si l'erreur persiste

1. **Vérifier que Composer est à jour** :
   ```bash
   composer self-update
   ```

2. **Vérifier les permissions** :
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

3. **Vérifier la version PHP** (doit être >= 8.2) :
   ```bash
   php -v
   ```

4. **Vérifier les extensions PHP requises** :
   ```bash
   php -m | grep -E "gd|imagick|mbstring|xml"
   ```

5. **Vérifier que le dossier vendor existe** :
   ```bash
   ls -la vendor/ralphjsmit/laravel-seo
   ```

Si le dossier n'existe pas, réinstallez :
```bash
composer require ralphjsmit/laravel-seo --no-interaction
```

## 📞 Support

Si le problème persiste après ces étapes, vérifiez les logs :
```bash
tail -f storage/logs/laravel.log
```


# 🔧 Instructions pour corriger les erreurs sur le serveur

## Problèmes identifiés dans les logs

1. ❌ **Erreur syntaxe phone-calls.blade.php** (ligne 694)
2. ❌ **Route reviews.create manquante**

## Solutions à appliquer en SSH

### 1. Vider le cache des vues (OBLIGATOIRE)

```bash
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 2. Vérifier que le fichier phone-calls.blade.php est à jour

```bash
# Vérifier la dernière ligne du fichier
tail -5 resources/views/admin/phone-calls.blade.php

# Devrait se terminer par @endsection
```

### 3. Vérifier les routes

```bash
# Lister toutes les routes reviews
php artisan route:list | grep reviews

# Devrait afficher reviews.create
```

### 4. Si l'erreur persiste, vérifier la syntaxe PHP

```bash
# Vérifier la syntaxe du fichier Blade compilé
php -l storage/framework/views/c9574973f53c8896117774a6f74ae04f.php

# Si erreur, vider le cache et recompiler
rm -rf storage/framework/views/*
php artisan view:clear
```

## Commandes complètes à exécuter en SSH

```bash
# Se connecter
ssh user@serveur.com
cd /home/u570136219/domains/couvreur-chevigny-saint-sauveur.fr/public_html

# Vider tous les caches
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Vérifier que les routes sont bien enregistrées
php artisan route:list | grep reviews

# Si besoin, supprimer tous les fichiers de vues compilées
rm -rf storage/framework/views/*

# Vérifier les permissions
chmod -R 775 storage/
chown -R www-data:www-data storage/  # ou votre utilisateur web
```

## Vérification après correction

```bash
# Tester que la page phone-calls fonctionne
curl -I https://couvreur-chevigny-saint-sauveur.fr/admin/phone-calls

# Vérifier que la route reviews.create existe
php artisan route:list | grep "reviews.create"
```


# ⚠️ Commandes URGENTES pour corriger l'erreur en production

## 🔴 Erreur actuelle

L'erreur `ParseError: syntax error, unexpected token ";"` dans `ads/show.blade.php` indique que le serveur utilise encore l'ancienne version du fichier.

## ✅ Solution : Mettre à jour le serveur

```bash
# 1. Connexion SSH
ssh votre-utilisateur@votre-serveur.com

# 2. Aller dans le répertoire
cd ~/public_html

# 3. Mettre à jour le code (IMPORTANT - corrige toutes les erreurs)
git pull origin main

# 4. Vider TOUS les caches (CRUCIAL)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Optimiser (optionnel mais recommandé)
php artisan optimize:clear
```

## 📋 Commandes complètes (copier-coller)

```bash
ssh votre-utilisateur@votre-serveur.com && cd ~/public_html && git pull origin main && php artisan cache:clear && php artisan config:clear && php artisan view:clear && echo "✅ Mise à jour terminée !"
```

## 🔍 Vérification après mise à jour

Après la mise à jour, vérifiez que le fichier est bien corrigé :

```bash
# Voir les dernières lignes du fichier Schema.org
tail -n 30 resources/views/ads/show.blade.php | grep -A 5 "Schema.org"
```

Vous devriez voir `json_encode([` et non `@json(`.

## ⚡ Si l'erreur persiste

Si l'erreur persiste après `git pull`, essayez :

```bash
# Forcer la mise à jour
git fetch origin
git reset --hard origin/main

# Vider tous les caches
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Recompiler les vues
php artisan view:clear
```

## 📝 Corrections apportées

Les corrections suivantes sont déjà dans Git et seront appliquées après `git pull` :

1. ✅ Correction erreur syntaxe dans `AdPublicController.php` (accolade en trop)
2. ✅ Correction erreur syntaxe dans `BotDetectionService.php` (code dupliqué)
3. ✅ Correction erreur syntaxe JSON dans `ads/show.blade.php` (Schema.org)

**Toutes ces corrections sont prêtes et attendent d'être appliquées avec `git pull` !**


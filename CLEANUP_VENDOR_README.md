# 🧹 Script de Nettoyage du Dossier Vendor

## Problème
Vous avez une limite d'inodes (nombre de fichiers/répertoires) sur votre serveur. Le dossier `vendor/` contient **41 569 fichiers**, ce qui consomme beaucoup d'inodes.

## Solution
Ce script supprime les fichiers inutiles du dossier `vendor/` sans casser le site :
- ✅ Tests (tests/, test/, phpunit.xml, etc.)
- ✅ Documentation (README.md, CHANGELOG.md, LICENSE, etc.)
- ✅ Fichiers de développement (.git, .github, etc.)
- ✅ Exemples et samples
- ✅ Fichiers de configuration de développement (phpstan, psalm, etc.)

## ⚠️ Ce qui est PRÉSERVÉ
- ✅ Tous les fichiers PHP nécessaires au fonctionnement
- ✅ `composer.json` (nécessaire pour l'autoload)
- ✅ `autoload.php` et fichiers d'autoload
- ✅ Tous les fichiers de code source

## 📋 Utilisation

### 1. Simulation (Recommandé en premier)
```bash
php cleanup-vendor.php --dry-run
```
Cela affiche ce qui sera supprimé **sans rien supprimer réellement**.

### 2. Nettoyage réel
```bash
php cleanup-vendor.php
```

### 3. Après le nettoyage
```bash
composer dump-autoload --optimize
```

## 📊 Résultats attendus

Après le nettoyage, vous devriez libérer :
- **Plusieurs milliers d'inodes** (fichiers + dossiers)
- **Plusieurs dizaines de Mo** d'espace disque
- Le site continuera de fonctionner normalement

## 🔍 Vérification

Avant :
```bash
find vendor -type f | wc -l
# Résultat: ~41 569 fichiers
```

Après :
```bash
find vendor -type f | wc -l
# Résultat: ~30 000-35 000 fichiers (réduction de 15-25%)
```

## ⚠️ Important

1. **Faites une sauvegarde** avant d'exécuter le script
2. **Testez en mode `--dry-run`** d'abord
3. **Exécutez `composer dump-autoload --optimize`** après le nettoyage
4. **Testez votre site** après le nettoyage pour vérifier que tout fonctionne

## 🚀 Pour la Production

En production, vous pouvez aussi installer Composer sans les dépendances de développement :

```bash
composer install --no-dev --optimize-autoloader
```

Cela supprime automatiquement tous les packages dans `require-dev` (phpunit, faker, etc.).

## 📝 Notes

- Le script est **sécurisé** : il ne supprime que les fichiers inutiles
- Les fichiers critiques (composer.json, autoload.php) sont préservés
- Le script peut être exécuté plusieurs fois sans problème


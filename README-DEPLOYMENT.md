# 🚀 Guide de Déploiement - Système de Templates

## 📋 Instructions pour la Production

### 1️⃣ **Après git pull, exécuter :**
```bash
php deploy-templates-production-only.php
```

### 2️⃣ **Ou utiliser le script complet :**
```bash
./deploy-production.sh
```

## ✅ C'est tout !

Le script va automatiquement :
- ✅ Vérifier l'environnement (production uniquement)
- ✅ Créer la table `ad_templates`
- ✅ Ajouter la colonne `template_id` dans `ads`
- ✅ Marquer les migrations comme exécutées
- ✅ Nettoyer le cache
- ✅ Vérifier que tout fonctionne

## 🌐 Accès

Une fois déployé, allez sur :
**`https://votre-site.com/admin/ads/templates`**

## 🔄 Déploiement Automatique

J'ai créé un hook Git (`.git/hooks/post-merge`) qui s'exécute automatiquement après `git pull` si des fichiers de templates sont modifiés.

## 📁 Fichiers de Déploiement

- `deploy-templates-production-only.php` - Script principal (production uniquement)
- `deploy-production.sh` - Script complet avec git pull
- `.git/hooks/post-merge` - Hook automatique après git pull
- `DEPLOYMENT-SIMPLE.md` - Guide simple

## 🆘 En cas de problème

### Erreur de connexion base de données
Vérifiez que votre `.env` de production contient les bonnes informations de connexion.

### Erreur de permissions
```bash
chmod +x deploy-templates-production-only.php
chmod +x deploy-production.sh
```

### Script ne s'exécute pas
```bash
php -f deploy-templates-production-only.php
```

## 🎯 Fonctionnalités Disponibles

Une fois déployé, vous aurez accès à :
- ✅ Création de templates à partir des services
- ✅ Génération automatique par IA
- ✅ Réutilisation pour plusieurs villes
- ✅ Personnalisation automatique par ville
- ✅ Interface admin complète

---

**🎉 C'est tout ! Votre système de templates sera opérationnel après un simple `git pull` et l'exécution du script.**
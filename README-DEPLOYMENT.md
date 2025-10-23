# 🚀 Déploiement JD Renovation Service sur Hostinger

## 📋 Prérequis

- Compte Hostinger avec accès FTP
- Base de données MySQL créée
- Domaine configuré : `jd-renovation-service.fr`

## 🗄️ Configuration Base de Données

**Paramètres MySQL Hostinger :**
- **Host:** localhost
- **Port:** 3306
- **Base de données:** `u182601382_jdrenov`
- **Utilisateur:** `u182601382_jdrenov`
- **Mot de passe:** `Harajuku1993@`

## 📁 Structure de Déploiement

```
www/
├── public/          # Point d'entrée (Document Root)
│   ├── index.php
│   ├── .htaccess
│   └── assets/
├── app/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env
└── artisan
```

## 🔧 Étapes de Déploiement

### 1. Upload des Fichiers
- Uploadez tout le contenu du dossier `simulateur2` dans le dossier `www/` de votre hébergement
- **IMPORTANT:** Le dossier `public/` doit être le Document Root

### 2. Configuration Hostinger
1. Connectez-vous à votre panneau Hostinger
2. Allez dans "Bases de données MySQL"
3. Créez la base de données `u182601382_jdrenov`
4. Créez l'utilisateur `u182601382_jdrenov` avec le mot de passe `Harajuku1993@`
5. Accordez tous les privilèges à l'utilisateur sur la base de données

### 3. Configuration du Domaine
1. Dans le panneau Hostinger, configurez le domaine `jd-renovation-service.fr`
2. Pointez le Document Root vers le dossier `public/` de votre application

### 4. Exécution du Script de Déploiement
```bash
# Connectez-vous en SSH ou utilisez le terminal Hostinger
cd /path/to/your/application
chmod +x deploy-hostinger.sh
./deploy-hostinger.sh
```

### 5. Configuration Email (Optionnel)
Modifiez le fichier `.env` pour configurer l'envoi d'emails :
```env
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@jd-renovation-service.fr
MAIL_PASSWORD=votre_mot_de_passe_email
MAIL_ENCRYPTION=tls
```

## 🔐 Sécurité

- Le fichier `.env` contient des informations sensibles, ne le partagez jamais
- Les permissions des dossiers `storage/` et `bootstrap/cache/` sont automatiquement configurées
- Un fichier `.htaccess` est inclus pour la sécurité et l'optimisation

## 🧪 Test de Déploiement

1. Visitez `https://jd-renovation-service.fr`
2. Vérifiez que la page d'accueil se charge correctement
3. Testez le formulaire de contact
4. Vérifiez l'administration (si applicable)

## 🆘 Dépannage

### Erreur 500
- Vérifiez les permissions des dossiers
- Vérifiez la configuration de la base de données
- Consultez les logs d'erreur

### Problème de base de données
- Vérifiez les paramètres de connexion dans `.env`
- Assurez-vous que la base de données existe
- Vérifiez les privilèges de l'utilisateur

### Problème d'emails
- Vérifiez la configuration SMTP dans `.env`
- Testez avec un email simple

## 📞 Support

En cas de problème, vérifiez :
1. Les logs d'erreur dans `storage/logs/`
2. La configuration de la base de données
3. Les permissions des fichiers
4. La configuration du domaine

---
**Version:** Production Ready  
**Dernière mise à jour:** $(date)  
**Domaine:** jd-renovation-service.fr

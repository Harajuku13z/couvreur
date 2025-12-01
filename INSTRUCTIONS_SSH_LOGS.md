# 📞 Instructions : Voir les logs des appels téléphoniques en SSH

## 🚀 Connexion SSH

```bash
# Se connecter au serveur
ssh votre-utilisateur@couvreur-chevigny-saint-sauveur.fr

# Aller dans le répertoire du projet
cd public_html
```

## 📋 Commandes principales

### Voir les derniers logs d'appels (50 dernières lignes)
```bash
tail -n 50 storage/logs/laravel.log | grep "📞\|✅ Appel\|❌\|⚠️"
```

### Suivre les logs en temps réel (Ctrl+C pour arrêter)
```bash
tail -f storage/logs/laravel.log | grep "📞\|✅ Appel\|❌\|⚠️"
```

### Voir tous les logs récents (100 dernières lignes)
```bash
tail -n 100 storage/logs/laravel.log
```

## 🔍 Recherches spécifiques

### Voir uniquement les appels réussis
```bash
grep "✅ Appel tracké" storage/logs/laravel.log | tail -n 20
```

### Voir uniquement les erreurs
```bash
grep "❌\|⚠️.*Tracking échoué" storage/logs/laravel.log | tail -n 20
```

### Compter le nombre d'appels trackés aujourd'hui
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "✅ Appel tracké" | wc -l
```

### Voir les détails complets d'une requête
```bash
grep -A 20 "📞 Requête trackPhoneCall" storage/logs/laravel.log | tail -n 50
```

### Chercher un numéro de téléphone spécifique
```bash
grep "0612345678" storage/logs/laravel.log
```

### Voir les appels d'une date spécifique
```bash
grep "2025-11-30" storage/logs/laravel.log | grep "📞\|✅ Appel"
```

## 📊 Statistiques

### Compter les appels réussis vs échecs
```bash
echo "Appels réussis:"
grep "✅ Appel tracké" storage/logs/laravel.log | wc -l

echo "Appels échoués:"
grep "❌\|⚠️.*Tracking échoué" storage/logs/laravel.log | wc -l
```

### Voir les appels des dernières 24 heures
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "📞\|✅ Appel" | tail -n 50
```

## 📝 Types de messages dans les logs

- `📞 Requête trackPhoneCall reçue` = Nouvelle requête reçue
- `📞 Données extraites` = Données extraites de la requête
- `✅ Appel tracké avec succès` = Appel enregistré en base de données
- `⚠️ Pas de numéro de téléphone` = Requête sans numéro
- `⚠️ Tracking échoué` = Problème mineur lors du tracking
- `❌ Erreur tracking appel téléphonique` = Erreur grave

## 🗑️ Nettoyer les logs (attention !)

```bash
# Vider le fichier de log (⚠️ supprime tout)
> storage/logs/laravel.log

# OU vider de manière plus propre
truncate -s 0 storage/logs/laravel.log
```

## 💡 Astuces

### Voir les logs avec les couleurs (si supporté)
```bash
tail -f storage/logs/laravel.log | grep --color=always "📞\|✅\|❌\|⚠️"
```

### Sauvegarder les logs d'appels dans un fichier
```bash
grep "📞\|✅ Appel\|❌\|⚠️" storage/logs/laravel.log > ~/appels_$(date +%Y%m%d).log
```

### Chercher toutes les erreurs d'aujourd'hui
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "❌\|ERROR\|Erreur" | tail -n 30
```

## 🔗 Voir aussi

- Interface admin : `/admin/phone-calls` pour voir les appels enregistrés dans la base de données
- Guide complet : `GUIDE_LOGS_APPELS.md`


# Guide : Accéder aux logs des appels téléphoniques

## 📍 Localisation des logs

Les logs Laravel sont stockés dans : `storage/logs/laravel.log`

## 🔍 Méthodes d'accès

### 1. Via SSH (Serveur de production)

```bash
# Se connecter au serveur
ssh votre-utilisateur@couvreur-chevigny-saint-sauveur.fr

# Aller dans le répertoire du projet
cd public_html

# Voir les dernières lignes du log (50 dernières lignes)
tail -n 50 storage/logs/laravel.log

# Suivre le log en temps réel (Ctrl+C pour arrêter)
tail -f storage/logs/laravel.log

# Filtrer uniquement les logs d'appels téléphoniques
grep "📞\|trackPhoneCall\|Appel\|PhoneCall" storage/logs/laravel.log | tail -n 50

# Voir les erreurs d'appels
grep "❌\|ERROR\|Erreur.*appel\|appel.*erreur" storage/logs/laravel.log | tail -n 50

# Compter le nombre d'appels trackés
grep "✅ Appel tracké" storage/logs/laravel.log | wc -l

# Voir les appels récents (dernière heure)
grep "📞" storage/logs/laravel.log | grep "$(date +%Y-%m-%d)" | tail -n 20
```

### 2. Via l'interface d'administration (si disponible)

- Aller sur `/admin/phone-calls` pour voir les appels enregistrés dans la base de données

### 3. Commandes utiles pour les logs

```bash
# Voir les 100 dernières lignes
tail -n 100 storage/logs/laravel.log

# Chercher un numéro de téléphone spécifique
grep "0612345678" storage/logs/laravel.log

# Voir tous les logs d'une journée spécifique
grep "2025-11-30" storage/logs/laravel.log

# Voir les appels avec leurs détails complets
grep -A 10 "📞 Requête trackPhoneCall" storage/logs/laravel.log | tail -n 50

# Compter les appels réussis vs échecs
echo "Appels réussis:" && grep "✅ Appel tracké" storage/logs/laravel.log | wc -l
echo "Appels échoués:" && grep "❌\|⚠️.*Tracking échoué" storage/logs/laravel.log | wc -l
```

## 📊 Types de logs d'appels

### Logs d'information (✅)
- `📞 Requête trackPhoneCall reçue` : Une requête a été reçue
- `📞 Données extraites` : Les données ont été extraites
- `✅ Appel tracké avec succès` : L'appel a été enregistré en base

### Logs d'avertissement (⚠️)
- `⚠️ Pas de numéro de téléphone` : Requête sans numéro
- `⚠️ Tracking échoué` : Le tracking a échoué mais sans erreur fatale

### Logs d'erreur (❌)
- `❌ Erreur tracking appel téléphonique` : Erreur lors du tracking

## 🔎 Exemples de recherche

```bash
# Voir tous les appels d'aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "📞\|✅ Appel tracké"

# Voir les erreurs récentes
grep "❌\|ERROR" storage/logs/laravel.log | tail -n 20

# Voir les appels avec détails (format JSON)
grep -B 2 -A 15 "📞 Requête trackPhoneCall" storage/logs/laravel.log | tail -n 50
```

## 📝 Note importante

Les logs Laravel peuvent devenir très volumineux. Pensez à les nettoyer régulièrement ou utiliser la rotation des logs.

Pour nettoyer le log actuel :
```bash
> storage/logs/laravel.log  # Vide le fichier
# OU
truncate -s 0 storage/logs/laravel.log  # Vide le fichier de manière plus propre
```


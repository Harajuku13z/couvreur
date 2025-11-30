# Guide : Vérifier les logs Laravel en SSH

## 📍 Localisation des logs

Les logs Laravel sont stockés dans : `storage/logs/`

### Fichier principal
- **Log unique** : `storage/logs/laravel.log`
- **Logs quotidiens** : `storage/logs/laravel-YYYY-MM-DD.log` (si configuré en mode daily)

## 🔍 Commandes de base

### 1. Se connecter en SSH
```bash
ssh user@votre-serveur.com
cd /home/u686558857/domains/alexandre-couverture.fr/public_html
# ou le chemin vers votre projet Laravel
```

### 2. Voir les dernières lignes du log
```bash
# Dernières 50 lignes
tail -n 50 storage/logs/laravel.log

# Dernières 100 lignes
tail -n 100 storage/logs/laravel.log

# Suivre les logs en temps réel (comme tail -f)
tail -f storage/logs/laravel.log
```

### 3. Rechercher des logs spécifiques

#### Rechercher les logs de tracking d'appels téléphoniques
```bash
# Rechercher tous les logs de tracking
grep "📞" storage/logs/laravel.log

# Rechercher les requêtes trackPhoneCall
grep "trackPhoneCall" storage/logs/laravel.log

# Rechercher avec contexte (5 lignes avant et après)
grep -C 5 "📞 Requête trackPhoneCall reçue" storage/logs/laravel.log

# Rechercher les erreurs de tracking
grep -i "erreur.*track" storage/logs/laravel.log
```

#### Rechercher les erreurs
```bash
# Toutes les erreurs
grep "ERROR" storage/logs/laravel.log

# Erreurs avec contexte
grep -C 10 "ERROR" storage/logs/laravel.log

# Erreurs récentes (dernières 1000 lignes)
tail -n 1000 storage/logs/laravel.log | grep "ERROR"
```

### 4. Filtrer par date/heure

```bash
# Logs d'aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log

# Logs d'une date spécifique (ex: 2025-01-15)
grep "2025-01-15" storage/logs/laravel.log

# Logs de la dernière heure
grep "$(date +'%Y-%m-%d %H')" storage/logs/laravel.log
```

### 5. Combinaisons utiles

#### Voir les derniers logs de tracking d'appels
```bash
tail -n 500 storage/logs/laravel.log | grep "📞"
```

#### Voir les erreurs récentes avec contexte
```bash
tail -n 1000 storage/logs/laravel.log | grep -A 20 "ERROR"
```

#### Compter le nombre d'appels trackés aujourd'hui
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "📞 Requête trackPhoneCall reçue" | wc -l
```

#### Voir les appels trackés avec succès
```bash
grep "✅ Appel téléphonique tracké" storage/logs/laravel.log | tail -n 50
```

#### Voir les erreurs de tracking
```bash
grep "❌ Erreur tracking appel" storage/logs/laravel.log | tail -n 50
```

## 📊 Commandes avancées

### 1. Suivre les logs en temps réel (très utile pour debug)
```bash
tail -f storage/logs/laravel.log | grep "📞"
```

### 2. Rechercher dans plusieurs fichiers de logs (mode daily)
```bash
# Rechercher dans tous les fichiers de logs
grep "📞" storage/logs/laravel-*.log

# Voir le dernier fichier de log
ls -t storage/logs/laravel-*.log | head -1 | xargs tail -n 100
```

### 3. Extraire les informations structurées
```bash
# Voir uniquement les IDs d'appels trackés
grep "✅ Appel téléphonique tracké" storage/logs/laravel.log | grep -oP "'id' => \K[0-9]+"

# Voir les numéros de téléphone trackés aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "phone_number" | grep -oP "'phone_number' => '[^']+'" | sort | uniq
```

### 4. Analyser les logs avec moins/more
```bash
# Parcourir le log avec moins (navigation avec flèches, q pour quitter)
less storage/logs/laravel.log

# Rechercher dans less : appuyer sur / puis taper votre recherche
# Navigation : n pour suivant, N pour précédent
```

## 🎯 Commandes spécifiques pour le tracking d'appels

### Voir toutes les requêtes de tracking reçues
```bash
grep "📞 Requête trackPhoneCall reçue" storage/logs/laravel.log | tail -n 50
```

### Voir les données extraites des requêtes
```bash
grep "📞 Données extraites" storage/logs/laravel.log | tail -n 50
```

### Voir les appels trackés avec succès
```bash
grep "✅ Appel.*tracké" storage/logs/laravel.log | tail -n 50
```

### Voir les erreurs de tracking
```bash
grep "❌ Erreur tracking" storage/logs/laravel.log | tail -n 50
```

### Voir les bots détectés
```bash
grep "🤖 Bot" storage/logs/laravel.log | tail -n 50
```

### Voir les appels déjà trackés (déduplication)
```bash
grep "déduplication\|déjà tracké" storage/logs/laravel.log | tail -n 50
```

## 📝 Créer un alias pour faciliter l'accès

Ajouter dans votre `~/.bashrc` ou `~/.zshrc` :

```bash
# Alias pour les logs Laravel
alias logs='tail -n 100 storage/logs/laravel.log'
alias logs-f='tail -f storage/logs/laravel.log'
alias logs-call='grep "📞" storage/logs/laravel.log | tail -n 50'
alias logs-err='grep "ERROR\|❌" storage/logs/laravel.log | tail -n 50'
```

Puis recharger : `source ~/.bashrc`

## 🔧 Vider les logs (attention !)

```bash
# Vider le log (le fichier sera recréé automatiquement)
> storage/logs/laravel.log

# Ou utiliser la commande Laravel
php artisan log:clear  # si disponible
```

## 📦 Exporter les logs

```bash
# Exporter les logs de tracking dans un fichier
grep "📞" storage/logs/laravel.log > tracking_logs_export.txt

# Exporter avec date
grep "📞" storage/logs/laravel.log > tracking_logs_$(date +%Y%m%d).txt
```

## 🚨 Vérifier la taille des logs

```bash
# Taille du fichier de log
du -h storage/logs/laravel.log

# Taille de tous les logs
du -sh storage/logs/

# Voir les plus gros fichiers de logs
ls -lhS storage/logs/
```

## 💡 Astuce : Filtrer avec jq (si installé)

Si vous avez des logs en JSON, vous pouvez utiliser `jq` :

```bash
# Installer jq (Ubuntu/Debian)
sudo apt-get install jq

# Filtrer les logs JSON
cat storage/logs/laravel.log | jq 'select(.message | contains("📞"))'
```

## 🔍 Exemple de workflow complet pour debug tracking

```bash
# 1. Se connecter en SSH
ssh user@serveur.com
cd /chemin/vers/projet

# 2. Suivre les logs en temps réel
tail -f storage/logs/laravel.log | grep "📞"

# 3. Dans un autre terminal SSH (ou Ctrl+C pour arrêter le tail -f)
# Cliquer sur un bouton d'appel sur le site

# 4. Voir les logs de cette session
grep "📞" storage/logs/laravel.log | tail -n 20

# 5. Voir les erreurs éventuelles
grep "❌\|ERROR" storage/logs/laravel.log | tail -n 20
```

## 📋 Checklist de diagnostic

Quand un appel n'est pas tracké, vérifier dans l'ordre :

1. ✅ **La requête arrive-t-elle au serveur ?**
   ```bash
   grep "📞 Requête trackPhoneCall reçue" storage/logs/laravel.log | tail -n 5
   ```

2. ✅ **Les données sont-elles extraites correctement ?**
   ```bash
   grep "📞 Données extraites" storage/logs/laravel.log | tail -n 5
   ```

3. ✅ **Y a-t-il des erreurs ?**
   ```bash
   grep "❌ Erreur\|ERROR" storage/logs/laravel.log | tail -n 10
   ```

4. ✅ **L'appel est-il créé en base ?**
   ```bash
   grep "✅ Appel téléphonique tracké" storage/logs/laravel.log | tail -n 5
   ```

5. ✅ **L'appel est-il détecté comme bot ?**
   ```bash
   grep "🤖 Bot" storage/logs/laravel.log | tail -n 5
   ```


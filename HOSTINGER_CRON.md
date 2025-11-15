# Configuration du Cron pour Hostinger

## 🎯 Solution pour Hostinger

Hostinger ne permet pas toujours d'exécuter directement `php artisan schedule:run` via cron. Voici la solution via HTTP.

## 📋 Méthode 1 : Utiliser le Gestionnaire de Cron de Hostinger

### Étape 1 : Générer un token de sécurité

1. Accédez à votre site : `https://votredomaine.com/cron/run`
2. Un token sera automatiquement généré et affiché
3. Copiez le token et l'URL complète

### Étape 2 : Configurer dans Hostinger

1. Connectez-vous à votre **panneau Hostinger (hPanel)**
2. Allez dans **Cron Jobs** (ou **Tâches planifiées**)
3. Créez une nouvelle tâche cron avec :
   - **Commande** : `curl -s "https://votredomaine.com/cron/run?token=VOTRE_TOKEN" > /dev/null 2>&1`
   - **Fréquence** : `* * * * *` (toutes les minutes)
   - **Ou utilisez** : `wget -q -O - "https://votredomaine.com/cron/run?token=VOTRE_TOKEN" > /dev/null 2>&1`

### Étape 3 : Vérifier que ça fonctionne

Testez l'URL dans votre navigateur :
```
https://votredomaine.com/cron/run?token=VOTRE_TOKEN
```

Vous devriez voir :
```json
{
  "success": true,
  "message": "Scheduler exécuté avec succès",
  "execution_time": "X.XX secondes",
  "timestamp": "2025-01-XX XX:XX:XX"
}
```

## 📋 Méthode 2 : Utiliser un Service Externe (Recommandé)

Si Hostinger ne permet pas de configurer des cron, utilisez un service externe gratuit :

### Option A : cron-job.org (Gratuit)

1. Créez un compte sur [cron-job.org](https://cron-job.org)
2. Ajoutez une nouvelle tâche :
   - **URL** : `https://votredomaine.com/cron/run?token=VOTRE_TOKEN`
   - **Fréquence** : Toutes les minutes (`* * * * *`)
   - **Méthode** : GET
3. Sauvegardez

### Option B : UptimeRobot (Gratuit)

1. Créez un compte sur [UptimeRobot](https://uptimerobot.com)
2. Ajoutez un **HTTP(s) Monitor** :
   - **URL** : `https://votredomaine.com/cron/run?token=VOTRE_TOKEN`
   - **Intervalle** : 5 minutes (minimum gratuit)
3. Sauvegardez

### Option C : EasyCron (Payant mais fiable)

1. Créez un compte sur [EasyCron](https://www.easycron.com)
2. Ajoutez une tâche :
   - **URL** : `https://votredomaine.com/cron/run?token=VOTRE_TOKEN`
   - **Fréquence** : Toutes les minutes
3. Sauvegardez

## 🔐 Sécurité

Le token est stocké dans la base de données et protège votre route. Ne partagez jamais ce token publiquement.

### Changer le token

Si vous devez changer le token, exécutez dans votre terminal SSH :

```bash
php artisan tinker
```

Puis :
```php
\App\Models\Setting::set('cron_run_token', \Illuminate\Support\Str::random(32), 'string', 'system');
```

## 📊 Tâches Exécutées

Cette route exécute automatiquement toutes les tâches planifiées dans `routes/console.php` :

- ✅ **Sitemap** : Génération automatique chaque jour à 3h
- ✅ **Indexation Google** : Indexation quotidienne à 2h (si activée)
- ✅ **Articles SEO** : Génération automatique selon configuration
- ✅ **Soumissions abandonnées** : Marquage toutes les heures

## 🐛 Dépannage

### Le cron ne s'exécute pas

1. **Vérifiez le token** : Testez l'URL dans votre navigateur
2. **Vérifiez les logs** : `storage/logs/laravel.log`
3. **Vérifiez les permissions** : Assurez-vous que Laravel peut écrire dans `storage/`

### Erreur 401 (Token invalide)

- Vérifiez que le token dans l'URL correspond à celui dans la base de données
- Régénérez le token si nécessaire

### Timeout

- Les tâches peuvent prendre jusqu'à 5 minutes
- Si timeout, vérifiez les logs pour voir quelle tâche bloque

## 📝 Notes Importantes

- ⚠️ **Ne configurez PAS** `php artisan schedule:run` directement dans Hostinger
- ✅ **Utilisez** la route HTTP `/cron/run?token=...` à la place
- ✅ **Appelez-la toutes les minutes** pour que Laravel puisse déterminer quelles tâches exécuter
- ✅ **Le scheduler Laravel** vérifie automatiquement l'heure et exécute seulement les tâches dues

## 🔗 URLs Utiles

- Route cron : `https://votredomaine.com/cron/run?token=VOTRE_TOKEN`
- Route articles SEO : `https://votredomaine.com/schedule/run?token=VOTRE_TOKEN` (alternative)


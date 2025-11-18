# 🔍 Guide de Test SSH pour le Scheduler SEO

## Commandes de Diagnostic

### 1. Diagnostic Complet
```bash
php artisan seo:diagnose
```
Cette commande vérifie :
- ✅ Automatisation activée
- ✅ Heure configurée vs heure actuelle
- ✅ Villes favorites
- ✅ Scheduler et horaires planifiés
- ✅ Articles créés aujourd'hui
- ✅ Erreurs récentes

### 2. Tester la Création d'Article (Force)
```bash
php artisan seo:run-automations --force
```
Force la création d'un article maintenant, même si ce n'est pas l'heure prévue.

### 3. Tester le Scheduler Laravel
```bash
php artisan schedule:run
```
Exécute toutes les tâches planifiées qui sont dues maintenant.

### 4. Voir les Tâches Planifiées
```bash
php artisan schedule:list
```
Affiche toutes les tâches planifiées et leur prochaine exécution.

### 5. Vérifier les Logs
```bash
tail -f storage/logs/laravel.log | grep -i "seo\|automation\|scheduler"
```
Affiche les logs en temps réel filtrés pour SEO/automation.

### 6. Vérifier les Articles Créés Aujourd'hui
```bash
php artisan tinker
```
Puis dans tinker :
```php
\App\Models\Article::whereDate('created_at', today())->orderBy('created_at', 'desc')->get(['id', 'city_id', 'created_at']);
```

### 7. Vérifier les Erreurs Récentes
```bash
php artisan tinker
```
Puis dans tinker :
```php
\App\Models\SeoAutomation::where('status', 'failed')->where('created_at', '>=', now()->subDay())->orderBy('created_at', 'desc')->get(['id', 'city_id', 'error_message', 'created_at']);
```

## Vérification du Cron Hostinger

### 1. Vérifier si le cron est configuré
```bash
crontab -l
```

### 2. Tester la route HTTP du cron
```bash
curl "https://votredomaine.com/schedule/run?token=VOTRE_TOKEN"
```

### 3. Vérifier les dernières exécutions
Regardez dans les logs :
```bash
grep "Schedule HTTP" storage/logs/laravel.log | tail -20
```

## Problèmes Courants

### Le créneau est marqué "Manqué" mais l'article devrait être créé

1. **Vérifier que le cron s'exécute** :
   ```bash
   php artisan seo:diagnose
   ```

2. **Vérifier les logs pour cette heure** :
   ```bash
   grep "12:26\|12:2[0-9]" storage/logs/laravel.log | tail -20
   ```

3. **Tester manuellement** :
   ```bash
   php artisan seo:run-automations --force
   ```

### Le scheduler ne se déclenche pas

1. **Vérifier l'heure configurée** :
   ```bash
   php artisan tinker
   ```
   ```php
   \App\Models\Setting::where('key', 'seo_automation_time')->value('value');
   ```

2. **Vérifier que le cron est actif** :
   - Dans Hostinger : Vérifiez que le cron est bien configuré
   - Testez la route HTTP : `curl "https://votredomaine.com/schedule/run?token=XXX"`

3. **Vérifier les conditions** :
   ```bash
   php artisan seo:diagnose
   ```

### Erreurs silencieuses

1. **Vérifier les logs d'erreur** :
   ```bash
   tail -100 storage/logs/laravel.log | grep -i error
   ```

2. **Vérifier les jobs échoués** :
   ```bash
   php artisan queue:failed
   ```

## Test Complet en Une Commande

```bash
echo "=== DIAGNOSTIC COMPLET ===" && \
php artisan seo:diagnose && \
echo "" && \
echo "=== DERNIERS ARTICLES ===" && \
php artisan tinker --execute="echo \App\Models\Article::whereDate('created_at', today())->count() . ' articles créés aujourd\'hui';" && \
echo "" && \
echo "=== DERNIÈRES ERREURS ===" && \
php artisan tinker --execute="echo \App\Models\SeoAutomation::where('status', 'failed')->where('created_at', '>=', now()->subDay())->count() . ' erreurs dans les dernières 24h';"
```


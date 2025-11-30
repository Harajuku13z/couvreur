# 🧪 Test du tracking des appels téléphoniques

## Test 1 : Vérifier que l'endpoint répond

En SSH ou avec curl :

```bash
curl -X POST https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0612345678","source_page":"test-curl"}'
```

Devrait retourner : `{"success":true,"id":123}` ou `OK`

## Test 2 : Vérifier dans les logs

```bash
# Voir les dernières requêtes
tail -n 100 storage/logs/laravel.log | grep "📞"
```

## Test 3 : Vérifier en base de données

```bash
php artisan tinker
>>> \App\Models\PhoneCall::latest()->take(5)->get(['id', 'phone_number', 'source_page', 'clicked_at', 'is_bot'])
>>> exit
```

## Test 4 : Page de test dans le navigateur

Aller sur : `https://couvreur-chevigny-saint-sauveur.fr/test-phone-tracking`

Cette page permet de tester manuellement le tracking.

## Test 5 : Vérifier le JavaScript dans la console

1. Ouvrir n'importe quelle page du site
2. Ouvrir la console (F12)
3. Taper : `window.trackPhoneCall('0612345678', 'test-console')`
4. Vérifier dans les logs que la requête arrive

## Diagnostic en temps réel

```bash
# Terminal 1 : Suivre les logs
tail -f storage/logs/laravel.log | grep "📞"

# Terminal 2 : Tester depuis le navigateur
# Cliquer sur un bouton d'appel ou utiliser la console
```

## Vérifications importantes

### 1. Le fichier JavaScript existe-t-il ?

```bash
ls -la public/js/phone-tracking.js
```

### 2. La route est-elle accessible ?

```bash
php artisan route:list | grep track-phone-call
```

### 3. La colonne is_bot existe-t-elle ?

```bash
php artisan tinker
>>> \Schema::hasColumn('phone_calls', 'is_bot')
>>> exit
```

Si `false`, exécuter :
```bash
php artisan migrate
```


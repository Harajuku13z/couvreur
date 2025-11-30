# 🔍 Diagnostic : Pourquoi les appels ne sont pas trackés

## Checklist de diagnostic à suivre

### 1. Vérifier que les requêtes arrivent au serveur

```bash
# En SSH, vérifier les logs de tracking
tail -f storage/logs/laravel.log | grep "📞"

# Ou voir les dernières requêtes
grep "📞 Requête trackPhoneCall reçue" storage/logs/laravel.log | tail -n 10
```

**Si aucune requête n'apparaît** : Le JavaScript ne s'exécute pas ou la requête n'arrive pas.

### 2. Vérifier que le JavaScript est chargé

Dans la console du navigateur (F12), vérifier :
- `window.trackPhoneCall` existe-t-il ?
- Y a-t-il des erreurs JavaScript ?
- Le script `phone-tracking.js` est-il chargé ?

### 3. Vérifier la route API

```bash
# En SSH
php artisan route:list | grep track-phone-call

# Devrait afficher :
# POST  api/track-phone-call
```

### 4. Vérifier que les appels sont créés en base

```bash
# En SSH, vérifier directement en base
php artisan tinker
>>> \App\Models\PhoneCall::count()
>>> \App\Models\PhoneCall::latest()->first()
>>> exit
```

### 5. Vérifier si la colonne is_bot existe

```bash
# En SSH
php artisan tinker
>>> \Schema::hasColumn('phone_calls', 'is_bot')
>>> exit
```

Si `false`, exécuter la migration :
```bash
php artisan migrate
```

### 6. Vérifier les erreurs dans les logs

```bash
# Erreurs de tracking
grep "❌ Erreur tracking" storage/logs/laravel.log | tail -n 20

# Erreurs générales
grep "ERROR" storage/logs/laravel.log | tail -n 20
```

## Tests manuels

### Test 1 : Vérifier le JavaScript dans la console

1. Ouvrir la page avec un bouton d'appel
2. Ouvrir la console (F12)
3. Taper : `window.trackPhoneCall`
4. Devrait afficher : `function trackPhoneCall() {...}`
5. Si `undefined`, le script n'est pas chargé

### Test 2 : Tester manuellement le tracking

Dans la console du navigateur :
```javascript
window.trackPhoneCall('0612345678', 'test-page');
```

Vérifier dans les logs que la requête arrive.

### Test 3 : Vérifier le endpoint API

```bash
# Tester avec curl
curl -X POST https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0612345678","source_page":"test"}'
```

## Solutions possibles

### Problème 1 : JavaScript pas chargé
- Vérifier que `phone-tracking.js` est inclus dans le layout
- Vérifier le chemin : `/js/phone-tracking.js`
- Vider le cache navigateur

### Problème 2 : Requêtes bloquées par CORS/CSRF
- Vérifier que la route est exclue du CSRF (déjà fait)
- Vérifier les erreurs réseau dans la console

### Problème 3 : Erreur serveur silencieuse
- Vérifier les logs pour les erreurs PHP
- Vérifier les permissions sur `storage/logs/`

### Problème 4 : Appels trackés mais pas affichés
- Cocher "Inclure les bots" dans l'admin
- Vérifier la pagination
- Vérifier les filtres de date


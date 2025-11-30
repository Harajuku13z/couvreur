# 🔍 Commandes SSH pour diagnostiquer le tracking des appels

## ⚡ Diagnostic rapide (5 minutes)

### 1. Vérifier si des appels sont enregistrés en base

```bash
php artisan tinker
>>> \App\Models\PhoneCall::count()
>>> \App\Models\PhoneCall::latest()->take(5)->get(['id', 'phone_number', 'source_page', 'clicked_at'])
>>> exit
```

**Si count = 0** : Aucun appel n'a été tracké → problème de JavaScript/requêtes
**Si count > 0** : Les appels sont trackés → problème d'affichage/filtre

### 2. Vérifier les dernières requêtes reçues

```bash
# Voir les 20 dernières requêtes de tracking
grep "📞 Requête trackPhoneCall reçue" storage/logs/laravel.log | tail -n 20
```

**Si aucune ligne** : Les requêtes n'arrivent pas au serveur
**Si des lignes apparaissent** : Les requêtes arrivent, vérifier les erreurs

### 3. Vérifier les erreurs de tracking

```bash
# Voir les erreurs de tracking
grep "❌ Erreur tracking\|ERROR.*track" storage/logs/laravel.log | tail -n 20
```

### 4. Vérifier si la colonne is_bot existe

```bash
php artisan tinker
>>> \Schema::hasColumn('phone_calls', 'is_bot')
>>> exit
```

**Si false** : Exécuter la migration :
```bash
php artisan migrate
```

### 5. Tester manuellement l'endpoint

```bash
curl -X POST https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0612345678","source_page":"test-ssh"}'
```

Vérifier ensuite dans les logs :
```bash
grep "test-ssh" storage/logs/laravel.log
```

## 🔧 Solutions selon le problème

### Problème 1 : Aucun appel en base (count = 0)

**Causes possibles :**
- JavaScript pas chargé
- Requêtes bloquées (CORS, CSRF)
- Erreur serveur silencieuse

**Actions :**
1. Vérifier que `public/js/phone-tracking.js` existe
2. Vider le cache navigateur
3. Vérifier la console navigateur (F12) pour erreurs JS
4. Tester manuellement : `window.trackPhoneCall('0612345678', 'test')`

### Problème 2 : Appels en base mais pas affichés

**Causes possibles :**
- Filtre bots actif (is_bot = true)
- Pagination
- Erreur dans la requête d'affichage

**Actions :**
1. Cocher "Inclure les bots" dans l'admin
2. Vérifier les logs : `grep "📊 Admin phoneCalls" storage/logs/laravel.log`
3. Voir tous les appels : 
```bash
php artisan tinker
>>> \App\Models\PhoneCall::all(['id', 'phone_number', 'is_bot', 'clicked_at'])
>>> exit
```

### Problème 3 : Erreurs dans les logs

**Actions :**
1. Lire l'erreur complète dans les logs
2. Si erreur "is_bot" : exécuter `php artisan migrate`
3. Si erreur SQL : vérifier la structure de la table

## 📋 Checklist complète

```bash
# 1. Compter les appels en base
php artisan tinker
>>> \App\Models\PhoneCall::count()
>>> exit

# 2. Voir les requêtes reçues aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "📞 Requête" | wc -l

# 3. Voir les appels trackés avec succès aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "✅ Appel.*tracké" | wc -l

# 4. Voir les erreurs aujourd'hui
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep "❌\|ERROR" | wc -l

# 5. Vérifier la colonne is_bot
php artisan tinker
>>> \Schema::hasColumn('phone_calls', 'is_bot')
>>> exit

# 6. Si colonne n'existe pas, exécuter migration
php artisan migrate

# 7. Vider les caches
php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## 🚀 Test en temps réel

```bash
# Terminal 1 : Suivre les logs en temps réel
tail -f storage/logs/laravel.log | grep "📞"

# Terminal 2 : Tester depuis le navigateur
# Ouvrir le site et cliquer sur un bouton d'appel
# Ou utiliser la console : window.trackPhoneCall('0612345678', 'test')
```

## ✅ Vérification finale

Après avoir appliqué les corrections :

1. ✅ Vider les caches
2. ✅ Exécuter les migrations
3. ✅ Tester un appel depuis le navigateur
4. ✅ Vérifier dans les logs que la requête arrive
5. ✅ Vérifier en base que l'appel est créé
6. ✅ Vérifier dans l'admin que l'appel s'affiche


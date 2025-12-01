# 🔍 Diagnostic : Tracking des appels téléphoniques

## Problème : Erreur 404 lors du test de tracking

### Solutions appliquées

1. **URL absolue dans le script JavaScript**
   - Fichier : `public/js/phone-tracking.js`
   - Changement : Utilisation de `window.location.origin` pour construire l'URL absolue
   - Avant : `const TRACKING_ENDPOINT = '/api/track-phone-call';`
   - Après : `const TRACKING_ENDPOINT = (window.location.origin || window.location.protocol + '//' + window.location.host) + '/api/track-phone-call';`

2. **URL absolue dans le test admin**
   - Fichier : `resources/views/admin/phone-calls.blade.php`
   - Changement : Utilisation de `url()` helper Blade
   - Avant : `fetch('/api/track-phone-call', ...)`
   - Après : `fetch('{{ url("/api/track-phone-call") }}', ...)`

### Vérifications à faire

#### 1. Vérifier que la route est bien définie

```bash
php artisan route:list | grep track-phone-call
```

Doit retourner :
```
POST      api/track-phone-call ................ api.track.phone.call
```

#### 2. Tester l'endpoint directement

```bash
# Depuis le serveur
curl -X POST https://www.jd-renovation-service.fr/api/track-phone-call \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"0633532123","source_page":"/test"}'
```

#### 3. Vérifier les logs

```bash
# Voir les dernières erreurs
tail -n 50 storage/logs/laravel.log | grep "trackPhoneCall\|404"

# Voir toutes les requêtes reçues
tail -f storage/logs/laravel.log | grep "📞"
```

#### 4. Vérifier la configuration du serveur

Pour Apache (.htaccess), vérifier que les routes sont bien redirigées :
```apache
RewriteEngine On
RewriteRule ^(.*)$ public/index.php?$1 [L]
```

Pour Nginx, vérifier la configuration :
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Configuration pour plusieurs domaines

La route `/api/track-phone-call` doit fonctionner sur tous les domaines :
- `https://www.jd-renovation-service.fr/api/track-phone-call`
- `https://couvreur-chevigny-saint-sauveur.fr/api/track-phone-call`
- Tout autre domaine configuré

### Test rapide dans la console du navigateur

Ouvrir la console (F12) et exécuter :

```javascript
// Vérifier que l'URL est correcte
console.log('URL tracking:', window.location.origin + '/api/track-phone-call');

// Test manuel
fetch(window.location.origin + '/api/track-phone-call', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        phone_number: '0633532123',
        source_page: '/admin/phone-calls',
        referrer_url: window.location.href
    })
})
.then(r => r.json())
.then(d => console.log('✅ Réponse:', d))
.catch(e => console.error('❌ Erreur:', e));
```

### Si le problème persiste

1. **Vérifier les permissions du fichier**
   ```bash
   ls -la public/js/phone-tracking.js
   chmod 644 public/js/phone-tracking.js
   ```

2. **Vider le cache Laravel**
   ```bash
   php artisan cache:clear
   php artisan route:clear
   php artisan config:clear
   php artisan view:clear
   ```

3. **Vérifier que le fichier JavaScript est bien chargé**
   - Ouvrir les outils de développement (F12)
   - Onglet Network
   - Recharger la page
   - Chercher `phone-tracking.js`
   - Vérifier qu'il charge avec un statut 200

4. **Vérifier le Content-Security-Policy (CSP)**
   - Si un CSP est configuré, il peut bloquer les requêtes
   - Vérifier dans les en-têtes HTTP

### Commandes utiles pour le diagnostic

```bash
# Voir toutes les routes API
php artisan route:list --path=api

# Tester la route spécifique
php artisan route:list | grep track-phone

# Voir les logs en temps réel
tail -f storage/logs/laravel.log | grep -E "📞|404|track"

# Vérifier la configuration Laravel
php artisan config:show app.url
```

### Support

Si le problème persiste après ces vérifications, consulter :
- Les logs Laravel : `storage/logs/laravel.log`
- Les logs du serveur web (Apache/Nginx)
- La console du navigateur pour les erreurs JavaScript

# 📸 Configuration Visuelle : cron-job.org

## 🎯 Valeurs exactes à remplir dans le formulaire cron-job.org

### 1. Titre
```
Laravel Scheduler
```
*(ou un nom de votre choix)*

### 2. URL *
```
https://votredomaine.com/schedule/run?token=VOTRE_TOKEN_SECRET
```

**⚠️ Important :**
- Remplacez `votredomaine.com` par votre vrai domaine
- Remplacez `VOTRE_TOKEN_SECRET` par le token que vous avez copié depuis l'interface admin (`/admin/seo-automation` → "Afficher le token et l'URL")

### 3. Activer tâche
✅ **Cocher** la case "Activer tâche"

### 4. Sauvegarder les réponses dans l'historique des tâches
✅ **Cocher** (recommandé pour le débogage)

### 5. Calendrier d'exécution

**Option A : Expression Crontab (Recommandé)**

1. Cliquez sur **"Personnalisé"** (Custom)
2. Dans le champ **"Expression Crontab"**, entrez exactement :
   ```
   * * * * *
   ```
3. Vérifiez que les "Prochaines exécutions" affichent des heures toutes les minutes :
   - `dimanche 9 novembre 2025 19:00`
   - `dimanche 9 novembre 2025 19:01`
   - `dimanche 9 novembre 2025 19:02`
   - etc.

**Option B : Interface graphique**

1. Sélectionnez **"Chaque"**
2. Dans le premier champ numérique, entrez : `1`
3. Dans le menu déroulant, sélectionnez : **"minute(s)"**

### 6. Fuseau horaire

Vérifiez que le fuseau horaire est : **Europe/Paris** (ou votre fuseau horaire)

Vous pouvez le voir dans "In this job's individual timezone (Europe/Paris)."

### 7. Avertissez-moi lorsque... (Notifications)

**Recommandé :**
- ✅ **"l'exécution du cronjob échoue"** (Notify when job execution fails)
  - **Notify after** : `1` failure (ou plus selon vos préférences)

**Optionnel :**
- ⬜ **"l'exécution du cronjob réussit après avoir échoué auparavant"** (Notify when job succeeds after previous failure)
- ⬜ **"le cronjob sera désactivé en raison d'un trop grand nombre d'échecs"** (Notify when job will be disabled due to too many failures)

### 8. Schedule expires

Laissez vide (pas d'expiration) sauf si vous voulez que le cron s'arrête à une date précise.

## ✅ Vérification avant de sauvegarder

Avant de cliquer sur "Create cronjob" (ou "Sauvegarder"), vérifiez :

1. ✅ L'URL est correcte avec le token
2. ✅ L'expression crontab est `* * * * *` (toutes les minutes)
3. ✅ Les "Prochaines exécutions" montrent des heures toutes les minutes
4. ✅ Le fuseau horaire est correct (Europe/Paris)
5. ✅ La case "Activer tâche" est cochée

## 🎬 Après la création

1. **Attendez 1-2 minutes**
2. Allez dans **"Cronjobs"** → votre cron job
3. Vérifiez l'onglet **"Execution history"** :
   - Les appels doivent être en **vert** (succès)
   - Code HTTP : **200**
   - Message : `{"success":true,"message":"Scheduler exécuté à ..."}`

4. **Testez dans votre application** :
   - Allez dans `/admin/seo-automation`
   - Cliquez sur **"Tester la route HTTP"**
   - Vous devriez voir un succès

## 🐛 Si ça ne fonctionne pas

### Erreur 401 (Unauthorized)
- Le token est incorrect
- Vérifiez que vous avez copié le bon token depuis l'interface admin
- Régénérez le token si nécessaire

### Erreur 500 (Server Error)
- Vérifiez les logs Laravel : `storage/logs/laravel.log`
- Testez manuellement l'URL dans votre navigateur
- Vérifiez que le scheduler Laravel fonctionne : `php artisan schedule:run`

### Le cron ne s'exécute pas
- Vérifiez que "Activer tâche" est bien coché
- Vérifiez l'expression crontab : doit être `* * * * *`
- Vérifiez les "Prochaines exécutions" dans cron-job.org

## 📊 Exemple de configuration complète

```
Titre: Laravel Scheduler
URL: https://couvreur-chevigny-saint-sauveur.fr/schedule/run?token=abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
Activer tâche: ✅
Sauvegarder les réponses: ✅
Calendrier: Personnalisé → * * * * *
Fuseau horaire: Europe/Paris
Notifications: ✅ l'exécution échoue (après 1 échec)
```

## 💡 Astuce

Vous pouvez tester l'URL manuellement avant de créer le cron job :
1. Copiez l'URL complète depuis l'interface admin
2. Collez-la dans votre navigateur
3. Vous devriez voir : `{"success":true,"message":"Scheduler exécuté à ..."}`

Si ça fonctionne manuellement, ça fonctionnera aussi avec cron-job.org !


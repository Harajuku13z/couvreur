# ⚙️ Configuration du Cron Laravel pour Hostinger

## 🚨 IMPORTANT

Sans le cron configuré, le scheduler Laravel ne s'exécutera **JAMAIS** automatiquement. Les articles ne seront pas générés à l'heure configurée.

## 📋 Solution 1 : Configurer le cron via hPanel (RECOMMANDÉ pour Hostinger)

Sur Hostinger, vous n'avez pas toujours accès au crontab système, mais vous pouvez utiliser le gestionnaire de tâches cron dans le panneau hPanel.

### Étapes :

1. **Connectez-vous à votre hPanel Hostinger**

2. **Allez dans** : `Avancé` → `Cron Jobs`

3. **Cliquez sur** : "Créer un nouveau cron job"

4. **Configurez le cron :**
   - **Fréquence** : `* * * * *` (chaque minute)
   - **Commande** :
   ```bash
   /usr/bin/php /home/USERNAME/domains/tondomaine.com/public_html/artisan schedule:run >> /home/USERNAME/domains/tondomaine.com/public_html/storage/logs/cron.log 2>&1
   ```

   **🔁 Remplacez :**
   - `USERNAME` → votre nom d'utilisateur Hostinger (ex: `u570136219`)
   - `tondomaine.com` → votre domaine réel (ex: `couvreur-chevigny-saint-sauveur.fr`)

   **💡 Si votre projet Laravel est dans un sous-dossier** (ex: `/laravel`), ajustez le chemin :
   ```bash
   /usr/bin/php /home/USERNAME/domains/tondomaine.com/public_html/laravel/artisan schedule:run >> /home/USERNAME/domains/tondomaine.com/public_html/laravel/storage/logs/cron.log 2>&1
   ```

5. **Sauvegardez** le cron job

6. **Vérifiez** que le cron est bien créé dans la liste des cron jobs

## 📋 Solution 2 : Configurer le cron via SSH (si vous avez accès)

Si vous avez accès SSH à votre serveur Hostinger :

### 1. Se connecter en SSH

Connectez-vous à votre serveur Hostinger via SSH avec vos identifiants.

### 2. Trouver le chemin de votre projet

Une fois connecté, exécutez :

```bash
pwd
```

Vous devriez voir quelque chose comme : `/home/u570136219/public_html`

### 3. Trouver le chemin de PHP

Exécutez :

```bash
which php
```

Ou :

```bash
whereis php
```

Sur Hostinger, le chemin est généralement : `/opt/alt/php82/usr/bin/php` ou `/usr/bin/php` (selon votre version PHP)

### 4. Vérifier si un cron existe déjà

```bash
crontab -l
```

Si vous voyez "no crontab for u570136219", c'est normal, il n'y a pas encore de cron configuré.

### 5. Éditer le crontab

```bash
crontab -e
```

Cela ouvrira un éditeur (souvent `nano` ou `vi`).

### 6. Ajouter la ligne du cron Laravel

Ajoutez cette ligne à la fin du fichier (remplacez les chemins par vos vrais chemins) :

```bash
* * * * * cd /home/u570136219/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Explication :**
- `* * * * *` : Exécute toutes les minutes
- `cd /home/u570136219/public_html` : Change vers le répertoire de votre projet Laravel
- `&&` : Exécute la commande suivante si la précédente réussit
- `/usr/bin/php artisan schedule:run` : Exécute le scheduler Laravel
- `>> /dev/null 2>&1` : Redirige les sorties pour éviter les emails

**Pour rediriger vers un fichier de log (recommandé pour le débogage) :**

```bash
* * * * * cd /home/u570136219/public_html && /usr/bin/php artisan schedule:run >> /home/u570136219/public_html/storage/logs/scheduler.log 2>&1
```

### 7. Sauvegarder et quitter

- **Si vous êtes dans `nano`** : `Ctrl+X`, puis `Y`, puis `Entrée`
- **Si vous êtes dans `vi`** : `:wq`, puis `Entrée`

### 8. Vérifier que le cron est bien configuré

```bash
crontab -l
```

Vous devriez voir votre ligne de cron.

### 9. Tester le scheduler manuellement

```bash
cd /home/u570136219/public_html
php artisan schedule:run
```

Vous devriez voir soit :
- `Running scheduled command: "seo:run-automations"` (si l'heure est arrivée)
- `No scheduled commands are ready to run.` (si l'heure n'est pas encore arrivée - c'est normal)

### 10. Vérifier les logs

Pour voir si le cron s'exécute automatiquement :

```bash
tail -f storage/logs/laravel.log
```

Ou si vous avez configuré un log dédié :

```bash
tail -f storage/logs/scheduler.log
```

Attendez 1-2 minutes et vous devriez voir des entrées dans les logs.

## 🔍 Vérification que le cron fonctionne

Après avoir configuré le cron, attendez quelques minutes puis :

1. Vérifiez les logs : `tail -n 50 storage/logs/laravel.log | grep -i "schedule\|seo"`
2. Utilisez le bouton "Tester le scheduler" dans l'interface admin
3. Vérifiez que des jobs sont créés dans la queue

## 📋 Solution 3 : Alternative via HTTP (si le cron ne fonctionne pas)

Si Hostinger ne lance pas le cron correctement, vous pouvez utiliser un service externe pour appeler une URL HTTP qui exécutera le scheduler.

### 1. Configurer la route sécurisée

Une route sécurisée est déjà disponible dans l'application : `/schedule/run?token=VOTRE_TOKEN`

**Pour générer un token sécurisé**, exécutez dans votre terminal :

```bash
php artisan tinker
```

Puis :

```php
\Illuminate\Support\Str::random(32)
```

Copiez le token généré.

### 2. Configurer le token dans les settings

Dans l'interface admin, allez dans les paramètres et ajoutez le token dans les settings (ou directement dans la base de données) :

```php
\App\Models\Setting::set('schedule_run_token', 'VOTRE_TOKEN_GENERE', 'string', 'seo');
```

### 3. Utiliser un service externe

Configurez un service externe comme :
- **cron-job.org** (gratuit)
- **UptimeRobot** (gratuit)
- **EasyCron** (payant)

Pour appeler cette URL toutes les minutes :

```
https://votredomaine.com/schedule/run?token=VOTRE_TOKEN_GENERE
```

### 4. Tester manuellement

Testez l'URL dans votre navigateur :

```
https://votredomaine.com/schedule/run?token=VOTRE_TOKEN_GENERE
```

Vous devriez voir : "Scheduler exécuté à [date]"

## ⚠️ Problèmes courants

### Le cron ne s'exécute pas

1. Vérifiez les permissions : `ls -la /home/u570136219/public_html/artisan`
2. Vérifiez que PHP est accessible : `/usr/bin/php -v` ou `/opt/alt/php82/usr/bin/php -v`
3. Vérifiez les logs système : `grep CRON /var/log/syslog` (si accessible)
4. **Vérifiez dans hPanel** que le cron job est bien créé et actif

### "Permission denied"

Assurez-vous d'être connecté avec le bon utilisateur (celui qui possède les fichiers du projet).

### Le scheduler dit "No scheduled commands are ready to run"

C'est **normal** si :
- L'heure configurée n'est pas encore arrivée
- L'automatisation est désactivée
- Aucune ville favorite n'est configurée

### Le cron via hPanel ne fonctionne pas

1. Vérifiez que le chemin PHP est correct (`/usr/bin/php`)
2. Vérifiez que le chemin du projet est correct
3. Vérifiez les logs : `storage/logs/cron.log` ou `storage/logs/laravel.log`
4. Essayez la solution alternative via HTTP (Solution 3)

## 📞 Support

Si vous avez des problèmes, vérifiez :
1. Les logs Laravel : `storage/logs/laravel.log`
2. Les logs du scheduler (si configuré) : `storage/logs/scheduler.log` ou `storage/logs/cron.log`
3. Les jobs en attente dans l'interface admin
4. Le test du scheduler dans l'interface admin (bouton "Tester le scheduler")


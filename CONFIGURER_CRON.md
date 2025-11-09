# ⚙️ Configuration du Cron Laravel pour Hostinger

## 🚨 IMPORTANT

Sans le cron configuré, le scheduler Laravel ne s'exécutera **JAMAIS** automatiquement. Les articles ne seront pas générés à l'heure configurée.

## 📋 Étapes pour configurer le cron sur Hostinger

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

Sur Hostinger, le chemin est généralement : `/opt/alt/php82/usr/bin/php` (ou similaire selon votre version PHP)

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
* * * * * cd /home/u570136219/public_html && /opt/alt/php82/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Explication :**
- `* * * * *` : Exécute toutes les minutes
- `cd /home/u570136219/public_html` : Change vers le répertoire de votre projet Laravel
- `&&` : Exécute la commande suivante si la précédente réussit
- `/opt/alt/php82/usr/bin/php artisan schedule:run` : Exécute le scheduler Laravel
- `>> /dev/null 2>&1` : Redirige les sorties pour éviter les emails

**Pour rediriger vers un fichier de log (recommandé pour le débogage) :**

```bash
* * * * * cd /home/u570136219/public_html && /opt/alt/php82/usr/bin/php artisan schedule:run >> /home/u570136219/public_html/storage/logs/scheduler.log 2>&1
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

## ⚠️ Problèmes courants

### Le cron ne s'exécute pas

1. Vérifiez les permissions : `ls -la /home/u570136219/public_html/artisan`
2. Vérifiez que PHP est accessible : `/opt/alt/php82/usr/bin/php -v`
3. Vérifiez les logs système : `grep CRON /var/log/syslog` (si accessible)

### "Permission denied"

Assurez-vous d'être connecté avec le bon utilisateur (celui qui possède les fichiers du projet).

### Le scheduler dit "No scheduled commands are ready to run"

C'est **normal** si :
- L'heure configurée n'est pas encore arrivée
- L'automatisation est désactivée
- Aucune ville favorite n'est configurée

## 📞 Support

Si vous avez des problèmes, vérifiez :
1. Les logs Laravel : `storage/logs/laravel.log`
2. Les logs du scheduler (si configuré) : `storage/logs/scheduler.log`
3. Les jobs en attente dans l'interface admin


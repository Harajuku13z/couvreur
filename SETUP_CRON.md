# Configuration du Cron pour l'Automatisation SEO

## Problème actuel
Le cron n'est pas configuré sur votre serveur. Les tâches planifiées ne s'exécuteront pas automatiquement.

## Solution : Configurer le cron

### 1. Ouvrir le crontab
```bash
crontab -e
```

### 2. Ajouter la ligne suivante
Remplacez `/path-to-your-project` par le chemin absolu vers votre projet (ex: `/home/u570136219/public_html`)

```bash
* * * * * cd /home/u570136219/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Vérifier que le cron est configuré
```bash
crontab -l
```

Vous devriez voir la ligne que vous venez d'ajouter.

## Vérifier que le scheduler fonctionne

### Tester manuellement
```bash
php artisan schedule:run
```

### Voir les tâches planifiées
```bash
php artisan schedule:list
```

## Important

- Le cron doit s'exécuter **chaque minute** (`* * * * *`)
- Le chemin doit être **absolu** (commence par `/`)
- Assurez-vous que PHP est accessible depuis le cron (utilisez le chemin complet si nécessaire : `/opt/alt/php82/usr/bin/php`)

## Exemple avec chemin PHP complet

Si votre PHP est dans `/opt/alt/php82/usr/bin/php`, utilisez :

```bash
* * * * * cd /home/u570136219/public_html && /opt/alt/php82/usr/bin/php artisan schedule:run >> /dev/null 2>&1
```


# Guide : Lancer le Worker de Queue en Continu

Le worker de queue doit tourner en continu pour traiter les jobs d'automatisation SEO.

## Méthode 1 : En arrière-plan avec nohup (Simple)

### Lancer le worker

```bash
cd /home/u570136219/public_html
nohup php artisan queue:work --queue=seo-automation --tries=3 --timeout=300 > storage/logs/queue.log 2>&1 &
```

### Vérifier qu'il tourne

```bash
ps aux | grep "queue:work"
```

Vous devriez voir un processus PHP qui tourne.

### Arrêter le worker

```bash
# Trouver le PID (Process ID)
ps aux | grep "queue:work" | grep -v grep

# Tuer le processus (remplacez PID par le numéro trouvé)
kill PID
```

### Voir les logs

```bash
tail -f storage/logs/queue.log
```

## Méthode 2 : Via Supervisor (Recommandé pour la production)

Supervisor redémarre automatiquement le worker s'il plante.

### 1. Vérifier si Supervisor est installé

```bash
which supervisorctl
```

Si ce n'est pas installé, demandez à Hostinger de l'installer ou utilisez la méthode 1.

### 2. Créer le fichier de configuration

```bash
nano /etc/supervisor/conf.d/laravel-worker.conf
```

Ou si vous n'avez pas les droits root, créez-le dans votre répertoire :

```bash
mkdir -p ~/supervisor
nano ~/supervisor/laravel-worker.conf
```

### 3. Contenu du fichier de configuration

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/u570136219/public_html/artisan queue:work --queue=seo-automation --tries=3 --timeout=300 --sleep=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=u570136219
numprocs=1
redirect_stderr=true
stdout_logfile=/home/u570136219/public_html/storage/logs/worker.log
stopwaitsecs=3600
```

### 4. Charger la configuration

```bash
# Si vous avez les droits root
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

# Si vous n'avez pas les droits root, contactez le support Hostinger
```

### 5. Commandes utiles

```bash
# Voir le statut
supervisorctl status

# Redémarrer le worker
supervisorctl restart laravel-worker:*

# Arrêter le worker
supervisorctl stop laravel-worker:*

# Voir les logs
tail -f storage/logs/worker.log
```

## Méthode 3 : Script de démarrage automatique (Alternative)

Créez un script qui vérifie si le worker tourne et le relance si nécessaire.

### 1. Créer le script

```bash
nano ~/start-queue-worker.sh
```

### 2. Contenu du script

```bash
#!/bin/bash

cd /home/u570136219/public_html

# Vérifier si le worker tourne déjà
if pgrep -f "queue:work.*seo-automation" > /dev/null; then
    echo "Worker déjà en cours d'exécution"
    exit 0
fi

# Lancer le worker
nohup php artisan queue:work --queue=seo-automation --tries=3 --timeout=300 > storage/logs/queue.log 2>&1 &

echo "Worker lancé avec PID: $!"
```

### 3. Rendre le script exécutable

```bash
chmod +x ~/start-queue-worker.sh
```

### 4. Lancer le script

```bash
~/start-queue-worker.sh
```

### 5. Ajouter au cron pour vérification périodique (optionnel)

```bash
crontab -e
```

Ajoutez cette ligne pour vérifier toutes les 5 minutes :

```
*/5 * * * * /home/u570136219/start-queue-worker.sh
```

## Méthode 4 : Via hPanel (si disponible)

Certains hébergeurs proposent une interface pour gérer les workers. Vérifiez dans votre hPanel si une option "Queue Workers" ou "Background Jobs" est disponible.

## Vérification que tout fonctionne

### 1. Vérifier que le worker tourne

```bash
ps aux | grep "queue:work" | grep -v grep
```

### 2. Vérifier les jobs en attente

Dans l'admin SEO, section "Jobs en attente", vous devriez voir les jobs être traités.

### 3. Vérifier les logs

```bash
tail -f storage/logs/laravel.log | grep "ProcessSeoCityJob"
```

## Dépannage

### Le worker ne démarre pas

1. Vérifiez les permissions :
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

2. Vérifiez les logs :
```bash
tail -f storage/logs/queue.log
tail -f storage/logs/laravel.log
```

### Le worker plante régulièrement

1. Augmentez le timeout :
```bash
php artisan queue:work --queue=seo-automation --tries=3 --timeout=600
```

2. Vérifiez la mémoire disponible :
```bash
free -h
```

### Les jobs restent en attente

1. Vérifiez que le worker tourne bien
2. Vérifiez la configuration de la queue dans `.env` :
```env
QUEUE_CONNECTION=database
```

3. Vérifiez que les tables de queue existent :
```bash
php artisan queue:table
php artisan migrate
```

## Recommandation pour Hostinger

Pour Hostinger, je recommande la **Méthode 1 (nohup)** car :
- Simple à mettre en place
- Pas besoin de droits root
- Fonctionne immédiatement

Si le worker plante souvent, contactez le support Hostinger pour installer Supervisor.


# Guide de Déploiement en Production

## Procédure pour récupérer les changements sans modifier le `.env`

### 1. Sur le serveur de production

```bash
# Aller dans le répertoire du projet
cd /chemin/vers/votre/projet

# Sauvegarder le .env actuel (par sécurité)
cp .env .env.backup

# Solution 1 : Stash les changements du .env, puis pull, puis restaurer
git stash push -m "Sauvegarde .env avant pull" .env
git pull origin main
git stash pop

# Si stash pop crée un conflit, restaurer depuis la sauvegarde :
# cp .env.backup .env

# Solution 2 : Ignorer les changements du .env (recommandé)
git update-index --assume-unchanged .env
git pull origin main

# Vérifier que .env n'a pas été modifié
diff .env .env.backup
# Si aucune différence, tout est OK
```

### 2. Si vous avez l'erreur "Your local changes to .env would be overwritten"

```bash
# Étape 1 : Sauvegarder le .env
cp .env .env.backup

# Étape 2 : Retirer le .env du tracking local (sur le serveur uniquement)
git reset HEAD .env

# Étape 3 : Dire à Git d'ignorer les changements futurs du .env
git update-index --assume-unchanged .env

# Étape 4 : Faire le pull
git pull origin main

# Étape 5 : Vérifier que le .env est intact
diff .env .env.backup
```

### 3. Commandes Laravel après le pull

```bash
# Installer/mettre à jour les dépendances
composer install --no-dev --optimize-autoloader

# Mettre à jour la base de données (si nouvelles migrations)
php artisan migrate --force

# Vider et recréer le cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimiser l'application
php artisan optimize
```

### 4. Si le `.env` est modifié par erreur

Si jamais le `.env` est écrasé lors d'un pull :

```bash
# Restaurer depuis la sauvegarde
cp .env.backup .env

# Recharger la configuration
php artisan config:clear
php artisan config:cache
```

### 5. Protection permanente (recommandé)

Pour empêcher Git de modifier le `.env` à chaque pull :

```bash
# Sur le serveur de production uniquement
git update-index --assume-unchanged .env
```

Cette commande doit être exécutée **une seule fois** sur le serveur. Elle persiste même après les pulls.

Pour annuler cette protection (si nécessaire) :
```bash
git update-index --no-assume-unchanged .env
```

## Notes importantes

- ✅ Le `.env` est maintenant dans `.gitignore` et n'est plus tracké par Git
- ✅ Les futurs `git pull` ne modifieront **jamais** le `.env` en production (après `--assume-unchanged`)
- ✅ Chaque serveur (dev, staging, production) doit avoir son propre `.env`
- ✅ Ne jamais commiter le `.env` avec des données sensibles
- ⚠️ La commande `git update-index --assume-unchanged` doit être exécutée sur chaque serveur de production

## Structure recommandée

```
.env.example          # Template avec variables sans valeurs sensibles
.env                  # Fichier local (ignoré par Git)
.env.backup           # Sauvegarde automatique (ignoré par Git)
.env.production       # Ignoré par Git
.env.local            # Ignoré par Git
```

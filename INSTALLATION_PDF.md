# Installation du package PDF sur le serveur

## Problème
L'erreur `Class "Barryvdh\DomPDF\Facade\Pdf" not found` indique que le package `barryvdh/laravel-dompdf` n'est pas installé ou que l'autoloader n'a pas été régénéré.

## Solution

### 1. Se connecter au serveur via SSH

### 2. Aller dans le dossier du projet
```bash
cd /home/u570136219/domains/normesrenovationbretagne.fr/public_html
```

### 3. Installer les dépendances Composer
```bash
composer install --no-dev --optimize-autoloader
```

### 4. Publier la configuration du package (si nécessaire)
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 5. Vérifier que le package est installé
```bash
composer show barryvdh/laravel-dompdf
```

### 6. Régénérer l'autoloader
```bash
composer dump-autoload --optimize
```

### 7. Vider les caches Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Vérification

Après installation, testez la génération PDF :
- Aller sur `/admin/devis/2/pdf`
- Le PDF devrait s'afficher sans erreur

## Note importante

Le code a été modifié pour utiliser `app('dompdf.wrapper')` au lieu de la facade `Pdf::`, ce qui est plus robuste et fonctionne même si la facade n'est pas enregistrée.


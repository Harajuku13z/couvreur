# Installation du package PDF sur le serveur

## Problème
L'erreur `Target class [dompdf.wrapper] does not exist` ou `Class "Dompdf\Dompdf" not found` indique que le package DomPDF n'est pas installé ou que l'autoloader n'a pas été régénéré.

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

**OU si le package n'est pas dans composer.json, l'installer directement :**
```bash
composer require barryvdh/laravel-dompdf --no-dev
```

### 4. Vérifier que le package est installé
```bash
composer show barryvdh/laravel-dompdf
composer show dompdf/dompdf
```

### 5. Régénérer l'autoloader
```bash
composer dump-autoload --optimize
```

### 6. Vider les caches Laravel
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

## Vérification

Après installation, testez la génération PDF :
- Aller sur `/admin/devis/2/pdf`
- Le PDF devrait s'afficher sans erreur

## Note importante

Le code utilise maintenant directement `new \Dompdf\Dompdf()` au lieu du wrapper Laravel, ce qui est plus robuste et fonctionne même si le service provider n'est pas enregistré. Cependant, le package `dompdf/dompdf` doit être installé (il est une dépendance de `barryvdh/laravel-dompdf`).

## Si l'erreur persiste

Vérifiez que le package est bien installé :
```bash
composer show | grep dompdf
```

Si rien n'apparaît, installez-le :
```bash
composer require dompdf/dompdf --no-dev
composer dump-autoload
```


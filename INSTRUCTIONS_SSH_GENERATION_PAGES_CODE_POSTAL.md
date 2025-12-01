# 📄 Instructions SSH : Génération automatique de pages avec code postal

## 🎯 Objectif

Générer automatiquement des copies de pages d'annonces avec :
- ✅ Code postal dans le titre
- ✅ Code postal dans le contenu
- ✅ Mots-clés dans les alt des images de réalisations
- ✅ Mots-clés invisibles mais visibles pour Google (SEO)

## 🚀 Commandes SSH

### 1. Connexion SSH au serveur

```bash
ssh votre-utilisateur@votre-serveur.com
```

### 2. Aller dans le répertoire de l'application

```bash
cd /chemin/vers/votre/application
```

### 3. Générer des pages pour un template et toutes les villes

```bash
# Remplacer X par l'ID du template d'annonce
php artisan generate:pages-postal-code --template=X
```

**Exemple :**
```bash
php artisan generate:pages-postal-code --template=1
```

### 4. Générer pour un template et une ville spécifique

```bash
# Remplacer X par l'ID du template et "nom-ville" par le slug de la ville
php artisan generate:pages-postal-code --template=X --city=nom-ville
```

**Exemple :**
```bash
php artisan generate:pages-postal-code --template=1 --city=chevigny-saint-sauveur
```

### 5. Générer pour TOUS les templates et TOUTES les villes actives

```bash
php artisan generate:pages-postal-code --all
```

**⚠️ Attention :** Cette commande peut générer beaucoup de pages. Utilisez-la avec précaution.

### 6. Forcer la recréation (supprime les pages existantes avant)

```bash
# Pour un template spécifique
php artisan generate:pages-postal-code --template=X --force

# Pour tous les templates et villes
php artisan generate:pages-postal-code --all --force
```

## 📋 Étapes détaillées

### Étape 1 : Vérifier les templates disponibles

```bash
# Se connecter au serveur
ssh votre-utilisateur@votre-serveur.com
cd /chemin/vers/votre/application

# Lister les templates (via tinker)
php artisan tinker
```

Dans tinker :
```php
\App\Models\AdTemplate::all(['id', 'service_name', 'keyword']);
exit
```

### Étape 2 : Vérifier les villes actives

Dans tinker :
```php
\App\Models\City::where('is_active', true)->orWhere('active', true)->get(['id', 'name', 'slug', 'postal_code']);
exit
```

### Étape 3 : Générer les pages

Une fois que vous avez les IDs des templates, exécutez :

```bash
# Pour un template spécifique
php artisan generate:pages-postal-code --template=1

# Vérifier les résultats
php artisan tinker
```

Dans tinker :
```php
\App\Models\Ad::where('status', 'published')->orderBy('created_at', 'desc')->limit(5)->get(['id', 'title', 'slug', 'city_id']);
exit
```

## 🔍 Vérification des résultats

### Vérifier les pages créées

```bash
php artisan tinker
```

Dans tinker :
```php
// Compter les pages créées aujourd'hui
\App\Models\Ad::whereDate('created_at', today())->count();

// Voir les dernières pages créées
\App\Models\Ad::with('city')->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'title', 'slug', 'meta_title', 'city_id']);

// Vérifier qu'une page contient bien le code postal dans le titre
\App\Models\Ad::where('meta_title', 'like', '%21800%')->first(['title', 'meta_title']);
exit
```

## 🛠️ Options disponibles

| Option | Description | Exemple |
|--------|-------------|---------|
| `--template=X` | ID du template à utiliser (obligatoire sauf avec `--all`) | `--template=1` |
| `--city=slug` | Slug de la ville ou ID (optionnel) | `--city=chevigny-saint-sauveur` |
| `--all` | Générer pour tous les templates et toutes les villes | `--all` |
| `--force` | Supprimer les pages existantes avant création | `--force` |

## ⚡ Commandes rapides complètes

### Génération complète pour tous les templates et villes

```bash
ssh votre-utilisateur@votre-serveur.com
cd /chemin/vers/votre/application
php artisan generate:pages-postal-code --all
php artisan cache:clear
```

### Génération pour un service spécifique sur toutes les villes

```bash
ssh votre-utilisateur@votre-serveur.com
cd /chemin/vers/votre/application
php artisan generate:pages-postal-code --template=1
php artisan cache:clear
```

## 🐛 Dépannage

### Erreur : "Template X non trouvé"

```bash
# Vérifier que le template existe
php artisan tinker
\App\Models\AdTemplate::find(X);
exit
```

### Erreur : "Ville 'X' non trouvée"

```bash
# Vérifier les villes disponibles
php artisan tinker
\App\Models\City::where('slug', 'X')->first();
exit
```

### Page déjà existante

Si une page existe déjà, vous avez deux options :

1. **Utiliser `--force` pour la recréer :**
   ```bash
   php artisan generate:pages-postal-code --template=X --city=Y --force
   ```

2. **Vérifier l'annonce existante :**
   ```bash
   php artisan tinker
   \App\Models\Ad::where('slug', 'keyword-ville')->first();
   exit
   ```

## 📊 Résultats attendus

Après exécution, vous devriez voir :

```
🚀 Génération de pages avec code postal...

📋 Génération pour le template 'Nom du Service' et 15 ville(s)...

  ✅ Page créée: Nom du Service 21800 (Chevigny-Saint-Sauveur - 21800)
  ✅ Page créée: Nom du Service 21000 (Dijon - 21000)
  ...

✅ 15 page(s) générée(s) au total
```

## 🔗 URLs générées

Les pages seront accessibles via :

```
https://votre-site.com/ads/keyword-nom-ville
```

Exemple :
- `https://votre-site.com/ads/ravalement-facade-974-cilaos`

## ⚠️ Notes importantes

1. **Performance :** La génération peut prendre du temps si vous générez beaucoup de pages. Utilisez `screen` ou `tmux` pour les longues opérations :

   ```bash
   screen -S generation
   php artisan generate:pages-postal-code --all
   # Appuyez sur Ctrl+A puis D pour détacher
   # Pour revenir : screen -r generation
   ```

2. **Cache :** Après génération, videz le cache :

   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

3. **Logs :** Les erreurs sont enregistrées dans `storage/logs/laravel.log` :

   ```bash
   tail -f storage/logs/laravel.log
   ```

## 📝 Exemple complet

```bash
# 1. Connexion SSH
ssh mon-utilisateur@mon-serveur.com

# 2. Aller dans le répertoire
cd /var/www/mon-site

# 3. Vérifier les templates
php artisan tinker
\App\Models\AdTemplate::all(['id', 'service_name']);
exit

# 4. Générer pour le template ID 1
php artisan generate:pages-postal-code --template=1

# 5. Vider le cache
php artisan cache:clear

# 6. Vérifier les résultats
php artisan tinker
\App\Models\Ad::where('status', 'published')->count();
exit
```

## 🎉 C'est tout !

Les pages sont maintenant générées avec :
- ✅ Code postal dans le titre
- ✅ Code postal dans le contenu
- ✅ Mots-clés optimisés dans les alt des images
- ✅ Mots-clés invisibles mais visibles pour Google (SEO)


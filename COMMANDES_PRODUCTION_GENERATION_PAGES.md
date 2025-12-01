# 🚀 Commandes Production : Génération de Pages avec Code Postal

## 📋 Prérequis

1. Accès SSH à votre serveur de production
2. Accès au répertoire de l'application Laravel
3. Permissions d'exécution des commandes artisan

---

## 🔐 Étape 1 : Connexion SSH

```bash
ssh votre-utilisateur@votre-serveur.com
```

Exemple :
```bash
ssh u570136219@fr-int-web1906.cluster020.hosting.ovh.net
```

---

## 📂 Étape 2 : Aller dans le répertoire de l'application

```bash
cd /chemin/vers/votre/application/public_html
```

Exemple (chez OVH) :
```bash
cd ~/public_html
```

ou

```bash
cd /home/u570136219/www/votre-site.com/public_html
```

---

## 🔄 Étape 3 : Mettre à jour le code (si nécessaire)

```bash
# Récupérer les dernières modifications depuis Git
git pull origin main

# Ou si vous êtes sur une autre branche
git pull origin votre-branche
```

---

## ⚡ Étape 4 : Générer/Mettre à jour les pages

### Option A : Mettre à jour TOUTES les pages existantes (RECOMMANDÉ)

Cette commande va mettre à jour toutes les pages existantes en ajoutant :
- ✅ Code postal dans le titre
- ✅ Code postal dans la description  
- ✅ Centaines de mots-clés invisibles par dizaines et centaines

```bash
php artisan generate:pages-postal-code --all --update
```

**Avantages :**
- ✅ Ne crée pas de doublons
- ✅ Met à jour les pages existantes avec le code postal
- ✅ Ajoute tous les mots-clés optimisés

### Option B : Générer uniquement les nouvelles pages

```bash
php artisan generate:pages-postal-code --all
```

Cette commande crée uniquement les pages qui n'existent pas encore.

### Option C : Pour un template spécifique

```bash
# Remplacer X par l'ID du template
php artisan generate:pages-postal-code --template=X --update
```

### Option D : Pour un template et une ville spécifique

```bash
php artisan generate:pages-postal-code --template=X --city=nom-ville --update
```

---

## 📊 Exemple complet de commande SSH

```bash
# 1. Connexion
ssh u570136219@fr-int-web1906.cluster020.hosting.ovh.net

# 2. Aller dans le répertoire
cd ~/public_html

# 3. Mettre à jour le code
git pull origin main

# 4. Mettre à jour toutes les pages existantes
php artisan generate:pages-postal-code --all --update

# 5. Vider le cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 🔍 Vérification des résultats

### Voir le nombre de pages mises à jour

```bash
php artisan tinker
```

Dans tinker :
```php
// Compter les pages publiées
\App\Models\Ad::where('status', 'published')->count();

// Voir les dernières pages modifiées
\App\Models\Ad::where('status', 'published')
    ->orderBy('updated_at', 'desc')
    ->limit(10)
    ->get(['id', 'title', 'meta_title', 'updated_at']);

// Vérifier qu'une page contient bien le code postal dans le titre
\App\Models\Ad::where('meta_title', 'like', '%974%')->first(['title', 'meta_title']);

exit
```

### Vérifier qu'une page contient les mots-clés cachés

```bash
# Voir le contenu HTML d'une page
php artisan tinker
```

```php
$ad = \App\Models\Ad::where('slug', 'votre-slug')->first();
echo substr($ad->content_html, -500); // Voir les 500 derniers caractères (mots-clés cachés)
exit
```

---

## ⏱️ Utilisation avec Screen/Tmux (pour longues opérations)

Si vous avez beaucoup de pages à générer, utilisez `screen` ou `tmux` pour éviter que la connexion SSH se coupe :

```bash
# Démarrer une session screen
screen -S generation-pages

# Lancer la commande
php artisan generate:pages-postal-code --all --update

# Détacher la session : Appuyez sur Ctrl+A puis D
# La commande continuera à tourner même si vous vous déconnectez

# Pour revenir à la session plus tard :
screen -r generation-pages

# Pour voir toutes les sessions actives :
screen -ls
```

---

## 📈 Temps estimé

- **Petit site** (50-100 pages) : 1-2 minutes
- **Site moyen** (200-500 pages) : 5-10 minutes  
- **Grand site** (500+ pages) : 15-30 minutes

Le temps dépend du nombre de :
- Templates d'annonces
- Villes actives
- Mots-clés à générer

---

## 🛠️ Commandes utiles

### Voir les templates disponibles

```bash
php artisan tinker
```

```php
\App\Models\AdTemplate::all(['id', 'service_name', 'keyword']);
exit
```

### Voir les villes actives

```php
\App\Models\City::where('is_active', true)
    ->orWhere('active', true)
    ->get(['id', 'name', 'slug', 'postal_code'])
    ->count();
exit
```

### Vérifier les logs en cas d'erreur

```bash
tail -f storage/logs/laravel.log
```

---

## ⚠️ Important

1. **Sauvegarde** : Faites une sauvegarde de la base de données avant d'exécuter avec `--force`
2. **Cache** : Videz toujours le cache après génération
3. **Temps** : Pour beaucoup de pages, utilisez `screen` ou `tmux`
4. **Test** : Testez d'abord sur un template spécifique avant de faire `--all`

---

## 🎯 Commandes rapides (copier-coller)

```bash
# Connexion + Mise à jour + Génération
ssh votre-utilisateur@votre-serveur.com && cd ~/public_html && git pull origin main && php artisan generate:pages-postal-code --all --update && php artisan cache:clear && echo "✅ Terminé !"
```

---

## 📝 Résultat attendu

Après exécution, vous devriez voir quelque chose comme :

```
🚀 Génération de pages avec code postal...

📋 Génération pour 13 template(s) et 37 ville(s)...

  ✅ Page mise à jour: Toiture Bac Acier 97411 (Les Avirons - 97411)
  ✅ Page mise à jour: Toiture Zinc 97412 (Bras Panon - 97412)
  ✅ Page mise à jour: Toiture Tôle Ondulée 97413 (Entre-Deux - 97413)
  ...

✅ 481 page(s) générée(s)/mise(s) à jour au total
```

---

## 🆘 En cas de problème

### Erreur : "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Erreur : "Memory limit exceeded"
```bash
# Augmenter la mémoire PHP temporairement
php -d memory_limit=512M artisan generate:pages-postal-code --all --update
```

### Erreur : "Timeout"
Utilisez `screen` ou `tmux` pour éviter les timeouts de connexion SSH.

---

## ✅ Checklist finale

- [ ] Code mis à jour avec `git pull`
- [ ] Commandes exécutées avec `--update`
- [ ] Cache vidé avec `php artisan cache:clear`
- [ ] Résultats vérifiés dans la base de données
- [ ] Pages testées sur le site en production

---

**🎉 C'est tout ! Vos pages sont maintenant optimisées avec code postal et centaines de mots-clés pour le SEO !**


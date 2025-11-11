# Éditeur d'Articles avec Gestion d'Images SEO

## Fonctionnalités

### 1. Éditeur WYSIWYG (TinyMCE)
- Éditeur visuel pour créer et modifier les articles
- Formatage de texte (gras, italique, listes, etc.)
- Insertion de liens et d'images
- Mode code pour éditer le HTML directement

### 2. Upload d'Images avec Métadonnées SEO
Lors de l'upload d'une image via l'éditeur, un formulaire modal s'ouvre pour saisir :
- **Texte alternatif (Alt)** : Obligatoire, important pour le SEO et l'accessibilité
- **Mots-clés** : Mots-clés associés à l'image (séparés par des virgules)
- **Titre** : Titre optionnel de l'image
- **Description** : Description optionnelle de l'image

### 3. Stockage des Métadonnées
Toutes les images uploadées sont enregistrées dans la table `article_images` avec :
- Chemin de l'image
- Texte alternatif (alt)
- Mots-clés
- Dimensions (largeur, hauteur)
- Taille du fichier
- Type MIME
- Relation avec l'article

## Configuration TinyMCE

**Important** : Pour utiliser TinyMCE, vous devez obtenir une clé API gratuite sur [TinyMCE Cloud](https://www.tiny.cloud/auth/signup/).

1. Créez un compte sur https://www.tiny.cloud
2. Obtenez votre clé API gratuite
3. Remplacez `no-api-key` dans les fichiers `create.blade.php` et `edit.blade.php` par votre clé API :

```javascript
<script src="https://cdn.tiny.cloud/1/VOTRE_CLE_API/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
```

## Utilisation

### Créer un Article
1. Allez dans **Blog & SEO > Articles > Créer**
2. Remplissez le titre et les métadonnées
3. Utilisez l'éditeur pour rédiger votre contenu
4. Pour ajouter une image :
   - Cliquez sur le bouton "Image" dans la barre d'outils
   - Sélectionnez votre image
   - Remplissez le formulaire avec les métadonnées SEO
   - L'image sera automatiquement insérée avec son alt text

### Modifier un Article
1. Allez dans **Blog & SEO > Articles**
2. Cliquez sur "Modifier" pour l'article souhaité
3. Utilisez l'éditeur pour modifier le contenu
4. Les images peuvent être ajoutées de la même manière

## API Endpoints

### Upload d'Image
```
POST /admin/articles/upload-image
```
Paramètres :
- `image` : Fichier image (requis)
- `alt_text` : Texte alternatif (requis)
- `keywords` : Mots-clés (optionnel)
- `title` : Titre (optionnel)
- `description` : Description (optionnel)
- `article_id` : ID de l'article (optionnel, sera lié après création)

### Mettre à jour les Métadonnées
```
PUT /admin/articles/images/{imageId}/metadata
```
Paramètres :
- `alt_text` : Texte alternatif
- `keywords` : Mots-clés
- `title` : Titre
- `description` : Description

### Lister les Images d'un Article
```
GET /admin/articles/{articleId}/images
```

## Base de Données

### Table `article_images`
- `id` : ID de l'image
- `article_id` : ID de l'article (nullable)
- `image_path` : Chemin de l'image
- `alt_text` : Texte alternatif
- `keywords` : Mots-clés
- `title` : Titre
- `description` : Description
- `width` : Largeur
- `height` : Hauteur
- `file_size` : Taille du fichier
- `mime_type` : Type MIME
- `created_at` / `updated_at` : Timestamps

## Migration

Pour créer la table `article_images`, exécutez :
```bash
php artisan migrate
```

## Notes Importantes

1. **Alt Text** : Toujours remplir le texte alternatif pour le SEO et l'accessibilité
2. **Mots-clés** : Utilisez des mots-clés pertinents pour améliorer le référencement des images
3. **Liaison automatique** : Les images uploadées avant la création de l'article seront automatiquement liées à l'article lors de sa création
4. **Format des images** : Formats supportés : JPEG, PNG, JPG, GIF, WEBP (max 10MB)


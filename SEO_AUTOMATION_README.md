# 🤖 Système d'Automatisation SEO

## 📋 Vue d'ensemble

Ce système génère automatiquement **1 article SEO optimisé par jour** pour chaque ville marquée comme favorite (`is_favorite = true`) dans la table `cities`.

## 🎯 Fonctionnalités

- ✅ Récupération des tendances locales via SerpAPI (Google Trends)
- ✅ Analyse des requêtes associées et des concurrents
- ✅ Génération d'articles optimisés via GPT (ChatGPT/Groq)
- ✅ Publication automatique dans la base de données
- ✅ Indexation automatique via Google Indexing API
- ✅ Journalisation complète dans `seo_automations`
- ✅ Interface d'administration pour suivi et actions manuelles

## 📦 Installation

### 1. Migrations

```bash
php artisan migrate
```

Cela créera :
- La table `seo_automations` (historique des automations)
- Le champ `city_id` dans la table `articles`

### 2. Configuration

Assurez-vous que les clés API suivantes sont configurées dans les Settings :

- **SerpAPI** : `serp_api_key` (déjà utilisé pour les avis)
- **ChatGPT/Groq** : `chatgpt_api_key` et/ou `groq_api_key` (déjà configuré)
- **Google Indexing** : Credentials Google Search Console (déjà configuré)

### 3. Marquer des villes comme favorites

Dans l'interface admin, allez dans **Villes** et activez `is_favorite` pour les villes que vous souhaitez cibler.

## 🚀 Utilisation

### Exécution automatique (recommandé)

Le système s'exécute automatiquement **chaque jour à 4h du matin** via le scheduler Laravel.

**Important** : Assurez-vous que le cron Laravel est configuré sur votre serveur :

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Exécution manuelle

#### Pour toutes les villes favorites :

```bash
php artisan seo:run-automations
```

#### Pour une ville spécifique :

```bash
php artisan seo:run-automations --city_id=1
```

### Queue Worker

Les jobs sont dispatchés dans la queue `seo-automation`. Pour les traiter :

```bash
php artisan queue:work --queue=seo-automation --sleep=3 --tries=3
```

**En production**, utilisez un process manager comme Supervisor pour maintenir le worker actif.

## 📊 Interface d'administration

Accédez à **Blog & SEO > Automatisation SEO** dans le menu admin pour :

- Voir l'historique de toutes les automations
- Consulter les statistiques (total, en attente, publiés, indexés, échoués)
- Relancer une automation échouée
- Voir les détails de chaque automation (mot-clé, statut, URL de l'article, erreurs)

## 🔄 Flux d'exécution

1. **Scheduler** exécute `seo:run-automations` à 4h du matin
2. La commande récupère toutes les villes favorites
3. Pour chaque ville, un `ProcessSeoCityJob` est dispatché dans la queue
4. Le job exécute `SeoAutomationManager::runForCity()` qui :
   - Récupère les tendances locales (SerpAPI)
   - Sélectionne un mot-clé non utilisé récemment (14 derniers jours)
   - Récupère les requêtes associées et analyse les concurrents
   - Génère l'article via GPT
   - Crée l'article dans la base de données
   - Indexe l'URL via Google Indexing API
   - Enregistre le log dans `seo_automations`

## 📝 Structure des données

### Table `seo_automations`

- `city_id` : ID de la ville
- `keyword` : Mot-clé utilisé
- `status` : `pending`, `generated`, `published`, `indexed`, `failed`
- `article_id` : ID de l'article créé
- `article_url` : URL publique de l'article
- `metadata` : JSON avec données GPT, requêtes associées, concurrents
- `error_message` : Message d'erreur si échec

### Articles générés

Les articles sont créés avec :
- `title` : Titre généré par GPT
- `slug` : Slug unique (titre + nom de la ville)
- `content_html` : Contenu HTML optimisé SEO
- `meta_description` : Meta description (max 155 caractères)
- `meta_keywords` : 5 mots-clés secondaires
- `focus_keyword` : Mot-clé principal
- `status` : `published`
- `city_id` : ID de la ville
- `published_at` : Date de publication

## ⚙️ Configuration avancée

### Rate Limiting

Les jobs sont dispatchés avec un délai échelonné (15 secondes entre chaque ville) pour éviter les rate limits des APIs.

### Sélection des mots-clés

Le système évite de réutiliser les mêmes mots-clés pour une même ville dans les **14 derniers jours**.

### Gestion des erreurs

- Les erreurs sont loggées dans `seo_automations.error_message`
- Les automations échouées peuvent être relancées depuis l'interface admin
- Les jobs ont 3 tentatives (`tries = 3`)

## 🐛 Dépannage

### Aucun article généré

1. Vérifiez que des villes sont marquées comme favorites
2. Vérifiez les logs : `storage/logs/laravel.log`
3. Vérifiez que les clés API sont configurées
4. Vérifiez que le queue worker tourne

### Erreurs SerpAPI

- Vérifiez que `serp_api_key` est valide dans les Settings
- Vérifiez les quotas SerpAPI

### Erreurs GPT

- Vérifiez que `chatgpt_api_key` ou `groq_api_key` est configuré
- Vérifiez les quotas OpenAI/Groq

### Erreurs d'indexation

- Vérifiez que Google Search Console est configuré
- Vérifiez que le compte de service a les permissions nécessaires

## 📈 Monitoring

Consultez les logs Laravel pour suivre l'exécution :

```bash
tail -f storage/logs/laravel.log | grep SeoAutomation
```

## 🔐 Sécurité

- Les routes admin sont protégées par le middleware `admin.auth`
- Les clés API sont stockées dans les Settings (base de données)
- Les jobs sont exécutés en queue (pas de blocage de l'application)

## 📚 Fichiers créés

- `app/Services/SerpApiService.php` : Service SerpAPI
- `app/Services/GptSeoGenerator.php` : Service génération GPT
- `app/Services/GoogleIndexingService.php` : Wrapper Google Indexing
- `app/Services/SeoAutomationManager.php` : Orchestrateur principal
- `app/Jobs/ProcessSeoCityJob.php` : Job pour traiter une ville
- `app/Console/Commands/RunSeoAutomations.php` : Commande Artisan
- `app/Http/Controllers/Admin/SeoAutomationController.php` : Controller admin
- `app/Models/SeoAutomation.php` : Modèle Eloquent
- `resources/views/admin/seo_automation/index.blade.php` : Vue admin
- `database/migrations/*_create_seo_automations_table.php` : Migration
- `database/migrations/*_add_city_id_to_articles_table.php` : Migration

## 🎉 C'est tout !

Le système est maintenant opérationnel. Les articles seront générés automatiquement chaque jour pour vos villes favorites.


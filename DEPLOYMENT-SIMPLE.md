# 🚀 Déploiement Simple - Système de Templates

## 📋 Ce que vous devez faire en production

### 1️⃣ **Après git pull, exécuter :**
```bash
php deploy-templates.php
```

### 2️⃣ **Ou utiliser le script complet :**
```bash
./deploy-production.sh
```

## ✅ C'est tout !

Le script va automatiquement :
- ✅ Créer la table `ad_templates`
- ✅ Ajouter la colonne `template_id` dans `ads`
- ✅ Marquer les migrations comme exécutées
- ✅ Nettoyer le cache
- ✅ Vérifier que tout fonctionne

## 🌐 Accès

Une fois déployé, allez sur :
**`https://votre-site.com/admin/ads/templates`**

## 🔄 Déploiement Automatique

J'ai aussi créé un hook Git qui s'exécute automatiquement après `git pull` si des fichiers de templates sont modifiés.

## 🆘 En cas de problème

Si le script ne fonctionne pas, exécutez manuellement :
```bash
php artisan tinker
```

Puis dans tinker :
```php
Schema::create('ad_templates', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('service_name');
    $table->string('service_slug');
    $table->longText('content_html');
    $table->text('short_description');
    $table->text('long_description');
    $table->string('icon', 50)->default('fas fa-tools');
    $table->string('meta_title', 160);
    $table->text('meta_description');
    $table->text('meta_keywords');
    $table->string('og_title', 160);
    $table->text('og_description');
    $table->string('twitter_title', 160);
    $table->text('twitter_description');
    $table->json('ai_prompt_used')->nullable();
    $table->json('ai_response_data')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('usage_count')->default(0);
    $table->timestamps();
    $table->index(['service_slug', 'is_active']);
    $table->index('service_name');
});

Schema::table('ads', function ($table) {
    $table->foreignId('template_id')->nullable()->after('city_id')->constrained('ad_templates')->onDelete('set null');
    $table->index('template_id');
});
```

---

**🎉 C'est tout ! Votre système de templates sera opérationnel.**

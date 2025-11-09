# Vérification du Cron pour l'Automatisation SEO

## Vérifier que le cron est configuré

Sur votre serveur, vérifiez que le cron Laravel est configuré :

```bash
crontab -l
```

Vous devriez voir :
```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Tester manuellement le scheduler

Pour tester si le scheduler fonctionne :

```bash
php artisan schedule:run
```

## Tester la commande SEO directement

Pour tester la génération d'articles directement (sans attendre le cron) :

```bash
php artisan seo:run-automations
```

Puis exécuter le worker de queue :

```bash
php artisan queue:work --queue=seo-automation
```

## Vérifier les logs

Les logs sont dans `storage/logs/laravel.log`. Cherchez les entrées avec "SeoAutomationManager" ou "ProcessSeoCityJob".

## Configuration

- **Heure de génération** : Configurable dans `/admin/seo-automation` (par défaut 04:00)
- **Nombre d'articles par ville** : Configurable dans `/admin/seo-automation` (par défaut 1)
- **Activation/Désactivation** : Bouton dans `/admin/seo-automation`

## Important

Le scheduler Laravel doit être exécuté **chaque minute** via cron pour que les tâches planifiées fonctionnent.


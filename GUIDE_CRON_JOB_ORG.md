# 📋 Guide : Configurer cron-job.org pour exécuter le scheduler Laravel

## 🎯 Objectif

Utiliser le service gratuit **cron-job.org** pour appeler votre route HTTP `/schedule/run` toutes les minutes, remplaçant ainsi le cron système si celui-ci ne fonctionne pas.

## 📝 Étapes détaillées

### 1. Obtenir votre URL et token

1. Connectez-vous à votre interface admin : `/admin/seo-automation`
2. Dans la section **"Route HTTP Alternative"**, cliquez sur **"Afficher le token et l'URL"**
3. Copiez l'URL complète qui ressemble à :
   ```
   https://votredomaine.com/schedule/run?token=VOTRE_TOKEN_SECRET
   ```

### 2. Créer un compte sur cron-job.org

1. Allez sur [https://cron-job.org](https://cron-job.org)
2. Cliquez sur **"Sign up"** (gratuit)
3. Créez un compte avec votre email
4. Confirmez votre email

### 3. Créer un nouveau cron job

1. Une fois connecté, cliquez sur **"Create cronjob"**

2. Remplissez le formulaire :
   - **Title** : `Laravel Scheduler` (ou un nom de votre choix)
   - **Address (URL)** : Collez l'URL que vous avez copiée
     ```
     https://votredomaine.com/schedule/run?token=VOTRE_TOKEN_SECRET
     ```
   - **Schedule** : Sélectionnez **"Every minute"** (toutes les minutes)
   - **Notifications** : Optionnel (pour recevoir des alertes en cas d'erreur)

3. Cliquez sur **"Create cronjob"**

### 4. Vérifier que le cron job fonctionne

1. Attendez 1-2 minutes
2. Dans cron-job.org, allez dans **"Cronjobs"** → votre cron job
3. Vérifiez l'onglet **"Execution history"** pour voir les appels
4. Les appels doivent être en vert (succès) avec un code HTTP 200

### 5. Vérifier dans votre application

1. Allez dans l'interface admin : `/admin/seo-automation`
2. Cliquez sur **"Tester la route HTTP"** pour vérifier que tout fonctionne
3. Vérifiez les logs Laravel : `storage/logs/laravel.log`
   - Vous devriez voir des entrées : `Schedule exécuté via HTTP`

## 🔒 Sécurité

- **Gardez votre token secret** : Ne partagez jamais l'URL complète avec le token
- **Régénérez le token** si vous pensez qu'il a été compromis
- Le token est stocké dans la base de données et peut être régénéré à tout moment

## ⚙️ Configuration avancée

### Changer la fréquence

Par défaut, le cron s'exécute toutes les minutes. Vous pouvez changer la fréquence dans cron-job.org :
- **Every minute** : Recommandé (comme le cron système)
- **Every 5 minutes** : Moins fréquent, mais peut manquer l'heure exacte configurée

### Notifications

Configurez les notifications dans cron-job.org pour être alerté si :
- Le cron job échoue
- Le site est inaccessible
- Le code HTTP n'est pas 200

## 🐛 Dépannage

### Le cron job ne s'exécute pas

1. Vérifiez que l'URL est correcte dans cron-job.org
2. Testez l'URL manuellement dans votre navigateur
3. Vérifiez que le token est correct
4. Vérifiez les logs dans cron-job.org (onglet "Execution history")

### Erreur 401 (Unauthorized)

- Le token est invalide ou manquant
- Régénérez le token et mettez à jour l'URL dans cron-job.org

### Erreur 500 (Server Error)

- Vérifiez les logs Laravel : `storage/logs/laravel.log`
- Vérifiez que le scheduler Laravel fonctionne : `php artisan schedule:run`

## 📊 Alternatives à cron-job.org

Si cron-job.org ne vous convient pas, vous pouvez utiliser :

- **UptimeRobot** : [https://uptimerobot.com](https://uptimerobot.com) (gratuit, 50 monitors)
- **EasyCron** : [https://www.easycron.com](https://www.easycron.com) (payant, plus de fonctionnalités)
- **Cronitor** : [https://cronitor.io](https://cronitor.io) (gratuit jusqu'à 5 jobs)

Tous ces services fonctionnent de la même manière : configurez l'URL avec le token et la fréquence.

## ✅ Vérification finale

Une fois configuré, vous devriez voir :

1. ✅ Des appels réussis dans cron-job.org (code 200)
2. ✅ Des entrées dans les logs Laravel : `Schedule exécuté via HTTP`
3. ✅ Le scheduler s'exécute automatiquement à l'heure configurée
4. ✅ Les articles sont générés automatiquement

## 💡 Astuce

Vous pouvez tester manuellement la route en cliquant sur **"Tester la route HTTP"** dans l'interface admin pour vérifier que tout fonctionne avant de configurer le service externe.


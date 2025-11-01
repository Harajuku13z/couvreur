# Guide pour afficher les logs Laravel via SSH

## Commandes principales

### 1. Voir les dernières lignes du log (les plus récentes)
```bash
tail -n 100 storage/logs/laravel.log
```
Affiche les 100 dernières lignes du fichier de log.

### 2. Voir les logs en temps réel (recommandé)
```bash
tail -f storage/logs/laravel.log
```
Affiche les nouvelles lignes de log au fur et à mesure qu'elles sont écrites.
Appuyez sur `Ctrl+C` pour arrêter.

### 3. Voir les logs avec recherche pour les templates IA
```bash
tail -f storage/logs/laravel.log | grep -i "template\|ia\|chatgpt\|groq\|génération"
```
Affiche uniquement les lignes contenant "template", "ia", "chatgpt", "groq" ou "génération".

### 4. Voir les erreurs uniquement
```bash
tail -f storage/logs/laravel.log | grep -i "error\|exception\|failed\|échoué"
```

### 5. Voir les 200 dernières lignes puis suivre en temps réel
```bash
tail -n 200 -f storage/logs/laravel.log
```

### 6. Rechercher des logs spécifiques aujourd'hui
```bash
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | tail -n 50
```

### 7. Voir les logs avec coloration (si disponible)
```bash
tail -f storage/logs/laravel.log | grep --color=always -E "ERROR|WARNING|INFO|template|IA|ChatGPT|Groq"
```

## Commandes pour diagnostiquer l'erreur de génération de template

### Voir tous les logs de génération de template IA
```bash
grep -A 20 "DÉBUT GÉNÉRATION IA TEMPLATE" storage/logs/laravel.log | tail -n 100
```

### Voir les erreurs critiques
```bash
grep -B 5 -A 20 "ERREUR CRITIQUE\|IMPOSSIBLE de générer" storage/logs/laravel.log | tail -n 100
```

### Voir les résultats des tests d'API
```bash
grep -A 10 "TEST PRÉALABLE\|Test Groq\|Test ChatGPT" storage/logs/laravel.log | tail -n 50
```

### Voir les messages d'erreur des APIs
```bash
grep -A 10 "Erreur API\|error_message\|Échec" storage/logs/laravel.log | tail -n 100
```

## Astuce : Combiner plusieurs commandes

### Voir les logs en temps réel et filtrer les erreurs
```bash
tail -f storage/logs/laravel.log | grep --line-buffered -E "ERROR|CRITIQUE|Échec|error"
```

### Voir uniquement les logs d'aujourd'hui en temps réel
```bash
tail -f storage/logs/laravel.log | grep --line-buffered "$(date +%Y-%m-%d)"
```

## Nettoyer les logs (attention!)

### Vider le fichier de log
```bash
> storage/logs/laravel.log
```
⚠️ Cette commande supprime tout le contenu du fichier de log.

### Archiver les anciens logs
```bash
mv storage/logs/laravel.log storage/logs/laravel.log.$(date +%Y%m%d_%H%M%S)
touch storage/logs/laravel.log
```

## Commandes utiles supplémentaires

### Compter les erreurs
```bash
grep -i "error\|exception" storage/logs/laravel.log | wc -l
```

### Voir la taille du fichier de log
```bash
ls -lh storage/logs/laravel.log
```

### Voir les 10 dernières erreurs
```bash
grep -i "error\|exception" storage/logs/laravel.log | tail -n 10
```


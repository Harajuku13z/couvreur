# 🔍 Vérifier un appel en base de données

## Problème : Appel tracké (ID 5) mais pas affiché

## Vérifications à faire en SSH

### 1. Vérifier que l'appel existe en base

```bash
php artisan tinker
```

Puis dans tinker :

```php
// Voir l'appel ID 5
$call = \App\Models\PhoneCall::find(5);
if ($call) {
    echo "ID: " . $call->id . "\n";
    echo "Phone: " . $call->phone_number . "\n";
    echo "Source: " . $call->source_page . "\n";
    echo "Is Bot: " . ($call->is_bot ? 'true' : 'false') . "\n";
    echo "Clicked At: " . $call->clicked_at . "\n";
} else {
    echo "Appel ID 5 non trouvé\n";
}

// Voir tous les appels
$allCalls = \App\Models\PhoneCall::orderBy('id', 'desc')->take(10)->get(['id', 'phone_number', 'source_page', 'is_bot', 'clicked_at']);
foreach ($allCalls as $c) {
    echo "ID: {$c->id}, Phone: {$c->phone_number}, Bot: " . ($c->is_bot ? 'Y' : 'N') . ", Date: {$c->clicked_at}\n";
}

exit
```

### 2. Vérifier si la colonne is_bot existe

```bash
php artisan tinker
```

```php
// Vérifier la colonne
echo \Schema::hasColumn('phone_calls', 'is_bot') ? "Colonne is_bot existe\n" : "Colonne is_bot N'EXISTE PAS\n";

// Voir la structure de la table
$columns = \Schema::getColumnListing('phone_calls');
print_r($columns);

exit
```

### 3. Vérifier les logs pour voir ce qui se passe

```bash
# Voir les derniers logs de tracking
tail -n 50 storage/logs/laravel.log | grep -E "📞|✅|❌|🤖|📊"

# Voir les logs de l'appel ID 5
grep "id.*5\|ID.*5" storage/logs/laravel.log | tail -n 20
```

### 4. Vérifier les appels filtrés par bots

```bash
php artisan tinker
```

```php
// Compter tous les appels
$total = \App\Models\PhoneCall::count();
echo "Total appels: $total\n";

// Compter les bots (si colonne existe)
if (\Schema::hasColumn('phone_calls', 'is_bot')) {
    $bots = \App\Models\PhoneCall::where('is_bot', true)->count();
    $humans = \App\Models\PhoneCall::where('is_bot', false)->count();
    echo "Appels bots: $bots\n";
    echo "Appels humains: $humans\n";
}

// Voir les appels bots
if (\Schema::hasColumn('phone_calls', 'is_bot')) {
    $botCalls = \App\Models\PhoneCall::where('is_bot', true)->orderBy('id', 'desc')->take(5)->get(['id', 'phone_number', 'source_page']);
    echo "Derniers appels bots:\n";
    foreach ($botCalls as $c) {
        echo "  ID: {$c->id}, Phone: {$c->phone_number}\n";
    }
}

exit
```

### 5. Tester la requête exacte utilisée par l'admin

```bash
php artisan tinker
```

```php
// Simuler la requête de l'admin (sans bots)
$query = \App\Models\PhoneCall::with('submission')
    ->orderBy('clicked_at', 'desc')
    ->orderBy('id', 'desc');

if (\Schema::hasColumn('phone_calls', 'is_bot')) {
    $query->where('is_bot', false);
}

$calls = $query->take(5)->get(['id', 'phone_number', 'source_page', 'is_bot', 'clicked_at']);
echo "Appels affichés dans l'admin (5 premiers):\n";
foreach ($calls as $c) {
    echo "ID: {$c->id}, Phone: {$c->phone_number}, Bot: " . ($c->is_bot ? 'Y' : 'N') . "\n";
}

// Vérifier si l'appel ID 5 est dans cette liste
$call5 = $calls->firstWhere('id', 5);
if ($call5) {
    echo "\n✅ Appel ID 5 est dans la liste\n";
} else {
    echo "\n❌ Appel ID 5 N'EST PAS dans la liste (peut-être détecté comme bot?)\n";
    
    // Vérifier directement
    $call5Direct = \App\Models\PhoneCall::find(5);
    if ($call5Direct) {
        echo "Appel ID 5 trouvé directement:\n";
        echo "  is_bot: " . ($call5Direct->is_bot ? 'true' : 'false') . "\n";
        echo "  clicked_at: " . $call5Direct->clicked_at . "\n";
    }
}

exit
```

## Solutions selon le diagnostic

### Solution 1 : L'appel est détecté comme bot

Si `is_bot = true` pour l'appel ID 5 :

1. **Cocher "Inclure les bots"** dans l'interface admin
2. Ou modifier l'appel manuellement :

```bash
php artisan tinker
```

```php
$call = \App\Models\PhoneCall::find(5);
if ($call) {
    $call->is_bot = false;
    $call->save();
    echo "✅ Appel ID 5 marqué comme non-bot\n";
}
exit
```

### Solution 2 : La colonne is_bot n'existe pas

Exécuter la migration :

```bash
php artisan migrate
```

### Solution 3 : Problème de tri ou pagination

Les appels sont maintenant triés par `clicked_at DESC, id DESC`. Si l'appel n'apparaît pas :

- Vérifier que `clicked_at` n'est pas null
- Vérifier la pagination (peut-être sur la page 2)

### Solution 4 : Force refresh de la page

Après le test, la page devrait se recharger automatiquement avec un timestamp pour éviter le cache.

## Commandes rapides

```bash
# Voir l'appel ID 5
php artisan tinker --execute="\$call = \App\Models\PhoneCall::find(5); if(\$call) { echo 'ID: ' . \$call->id . ', Bot: ' . (\$call->is_bot ? 'true' : 'false') . ', Date: ' . \$call->clicked_at . PHP_EOL; } else { echo 'Non trouvé' . PHP_EOL; }"

# Compter tous les appels
php artisan tinker --execute="echo 'Total: ' . \App\Models\PhoneCall::count() . PHP_EOL;"
```


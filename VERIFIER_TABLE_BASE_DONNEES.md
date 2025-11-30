# 🔍 Vérifier la table de base de données utilisée

## Diagnostic : Quelle table est réellement utilisée ?

### 1. Vérifier le nom de la table dans le modèle

En SSH :

```bash
php artisan tinker
```

```php
// Vérifier le nom de la table utilisé par le modèle
$model = new \App\Models\PhoneCall();
echo "Table utilisée par le modèle: " . $model->getTable() . "\n";

// Devrait afficher : phone_calls
exit
```

### 2. Vérifier si la table existe vraiment

```bash
php artisan tinker
```

```php
// Vérifier l'existence de la table
echo \Schema::hasTable('phone_calls') ? "✅ Table phone_calls existe\n" : "❌ Table phone_calls N'EXISTE PAS\n";

// Lister toutes les tables qui contiennent "phone"
$tables = \DB::select("SHOW TABLES");
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos(strtolower($tableName), 'phone') !== false) {
        echo "Table trouvée: $tableName\n";
    }
}

exit
```

### 3. Vérifier directement en SQL

```bash
php artisan tinker
```

```php
// Compter directement avec SQL brut
$count = \DB::table('phone_calls')->count();
echo "Nombre d'appels dans phone_calls: $count\n";

// Voir les 5 derniers appels
$calls = \DB::table('phone_calls')
    ->orderBy('id', 'desc')
    ->take(5)
    ->get(['id', 'phone_number', 'source_page', 'clicked_at']);
    
foreach ($calls as $call) {
    echo "ID: {$call->id}, Phone: {$call->phone_number}, Source: {$call->source_page}\n";
}

exit
```

### 4. Comparer avec le modèle Eloquent

```bash
php artisan tinker
```

```php
// Compter avec le modèle
$modelCount = \App\Models\PhoneCall::count();
echo "Avec le modèle PhoneCall: $modelCount\n";

// Compter avec DB::table
$tableCount = \DB::table('phone_calls')->count();
echo "Avec DB::table('phone_calls'): $tableCount\n";

// Si les nombres sont différents, il y a un problème
if ($modelCount !== $tableCount) {
    echo "⚠️ PROBLÈME: Les comptes ne correspondent pas!\n";
} else {
    echo "✅ Les comptes correspondent\n";
}

exit
```

### 5. Vérifier la structure de la table

```bash
php artisan tinker
```

```php
// Voir la structure de la table
$columns = \Schema::getColumnListing('phone_calls');
echo "Colonnes dans phone_calls:\n";
foreach ($columns as $col) {
    echo "  - $col\n";
}

// Vérifier si is_bot existe
if (in_array('is_bot', $columns)) {
    echo "\n✅ Colonne is_bot existe\n";
} else {
    echo "\n❌ Colonne is_bot N'EXISTE PAS\n";
}

exit
```

### 6. Vérifier toutes les données

```bash
php artisan tinker
```

```php
// Voir TOUS les appels (sans filtre)
$allCalls = \DB::table('phone_calls')
    ->orderBy('id', 'desc')
    ->get(['id', 'phone_number', 'source_page', 'clicked_at', 'is_bot']);
    
echo "Total d'appels: " . $allCalls->count() . "\n\n";
foreach ($allCalls as $call) {
    $bot = isset($call->is_bot) && $call->is_bot ? 'BOT' : 'HUMAN';
    echo "ID: {$call->id}, Phone: {$call->phone_number}, Source: {$call->source_page}, Type: $bot\n";
}

exit
```

## Problèmes possibles et solutions

### Problème 1 : Table n'existe pas

Si `\Schema::hasTable('phone_calls')` retourne `false` :

```bash
# Exécuter les migrations
php artisan migrate
```

### Problème 2 : Nom de table incorrect

Si le modèle utilise une autre table (peu probable) :

Vérifier dans `app/Models/PhoneCall.php` :
- La propriété `protected $table = 'phone_calls';` doit être présente
- Ou absente (Laravel utilisera automatiquement `phone_calls`)

### Problème 3 : Base de données différente

Si plusieurs bases de données sont configurées :

```bash
php artisan tinker
```

```php
// Voir la base de données actuelle
echo "Base de données actuelle: " . \DB::connection()->getDatabaseName() . "\n";

// Vérifier le fichier .env
// DB_DATABASE devrait pointer vers la bonne base
exit
```

### Problème 4 : Préfixe de table

Si les tables ont un préfixe :

```bash
php artisan tinker
```

```php
// Vérifier le préfixe
$prefix = \DB::connection()->getTablePrefix();
echo "Préfixe de table: '$prefix'\n";

// La table serait alors: {$prefix}phone_calls
exit
```

## Commandes rapides de diagnostic

```bash
# Vérifier tout en une commande
php artisan tinker --execute="
\$table = (new \App\Models\PhoneCall())->getTable();
\$exists = \Schema::hasTable('phone_calls');
\$count = \App\Models\PhoneCall::count();
echo \"Table: \$table, Existe: \" . (\$exists ? 'Oui' : 'Non') . \", Count: \$count\n\";
"
```

## Vérification après correction

Après avoir vérifié, tester à nouveau :

1. ✅ La table `phone_calls` existe
2. ✅ Le modèle utilise bien `phone_calls`
3. ✅ Les appels sont bien dans cette table
4. ✅ La page admin récupère bien depuis cette table


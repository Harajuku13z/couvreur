<?php
/**
 * Page de test pour vérifier l'accessibilité du logo BIMI
 * Accès : https://normesrenovationbretagne.fr/test-bimi-logo.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Logo BIMI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        img {
            max-width: 200px;
            border: 2px solid #ddd;
            padding: 10px;
            background: white;
        }
    </style>
</head>
<body>
    <h1>🔍 Test Logo BIMI</h1>
    
    <?php
    $logoPath = __DIR__ . '/logo/logo.svg';
    $logoUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/logo/logo.svg';
    $logoExists = file_exists($logoPath);
    $logoReadable = $logoExists && is_readable($logoPath);
    $logoSize = $logoExists ? filesize($logoPath) : 0;
    $logoPerms = $logoExists ? substr(sprintf('%o', fileperms($logoPath)), -4) : 'N/A';
    ?>
    
    <div class="card">
        <h2>📁 Fichier</h2>
        <p><strong>Chemin :</strong> <code><?php echo $logoPath; ?></code></p>
        <p>
            <strong>Existe :</strong> 
            <?php if ($logoExists): ?>
                <span class="success">✅ Oui</span>
            <?php else: ?>
                <span class="error">❌ Non</span>
            <?php endif; ?>
        </p>
        <p>
            <strong>Lisible :</strong> 
            <?php if ($logoReadable): ?>
                <span class="success">✅ Oui</span>
            <?php else: ?>
                <span class="error">❌ Non</span>
            <?php endif; ?>
        </p>
        <p><strong>Taille :</strong> <?php echo number_format($logoSize); ?> octets</p>
        <p><strong>Permissions :</strong> <code><?php echo $logoPerms; ?></code></p>
    </div>
    
    <div class="card">
        <h2>🌐 URL</h2>
        <p><strong>URL complète :</strong> <code><?php echo $logoUrl; ?></code></p>
        <p><a href="<?php echo $logoUrl; ?>" target="_blank" class="info">🔗 Ouvrir le logo dans un nouvel onglet</a></p>
    </div>
    
    <div class="card">
        <h2>🖼️ Aperçu</h2>
        <?php if ($logoExists && $logoReadable): ?>
            <img src="/logo/logo.svg" alt="Logo BIMI" onerror="this.style.border='2px solid red'; this.alt='Erreur de chargement';">
            <p class="info">Si l'image ne s'affiche pas, il y a un problème d'accessibilité publique.</p>
        <?php else: ?>
            <p class="error">❌ Le logo n'existe pas ou n'est pas lisible.</p>
        <?php endif; ?>
    </div>
    
    <div class="card">
        <h2>📋 Configuration DNS BIMI</h2>
        <p>Pour que le logo s'affiche dans Gmail, ajoutez cet enregistrement DNS TXT :</p>
        <code style="display: block; padding: 10px; background: #f3f4f6; margin: 10px 0;">
            default._bimi.normesrenovationbretagne.fr. TXT "v=BIMI1; l=<?php echo $logoUrl; ?>;"
        </code>
        <p class="warning">⚠️ Note : Gmail nécessite également un certificat VMC pour afficher le logo.</p>
    </div>
    
    <div class="card">
        <h2>✅ Checklist</h2>
        <ul>
            <li><?php echo $logoExists ? '✅' : '❌'; ?> Le fichier logo.svg existe</li>
            <li><?php echo $logoReadable ? '✅' : '❌'; ?> Le fichier est lisible</li>
            <li><?php echo $logoSize > 0 ? '✅' : '❌'; ?> Le fichier a une taille valide</li>
            <li>❓ L'URL est accessible publiquement (testez le lien ci-dessus)</li>
            <li>❓ Le DNS BIMI est configuré</li>
            <li>❓ Le certificat VMC est configuré (pour Gmail)</li>
        </ul>
    </div>
</body>
</html>


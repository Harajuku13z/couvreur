<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Font Awesome Icons</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Test Font Awesome Icons</h1>
        
        <!-- Test 1: Icônes basiques -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-xl font-bold mb-4">Test 1: Icônes basiques</h2>
            <div class="space-y-2">
                <p><i class="fas fa-check text-green-600"></i> Icône check verte</p>
                <p><i class="fas fa-star text-yellow-500"></i> Icône étoile jaune</p>
                <p><i class="fas fa-home text-blue-600"></i> Icône maison bleue</p>
                <p><i class="fas fa-tools text-gray-600"></i> Icône outils grise</p>
            </div>
        </div>
        
        <!-- Test 2: Liste avec icônes (comme dans les services) -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-xl font-bold mb-4">Test 2: Liste avec icônes (style services)</h2>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
                    <span><strong>Réparation des fissures et imperfections</strong> - Diagnostic précis et traitement adapté pour restaurer l'intégrité structurelle de votre facade</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
                    <span><strong>Nettoyage et traitement anti-mousse</strong> - Élimination complète des salissures et application de produits hydrofuges pour une protection durable</span>
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
                    <span><strong>Rénovation de peinture extérieure</strong> - Application de peintures haute qualité résistantes aux intempéries et aux UV</span>
                </li>
            </ul>
        </div>
        
        <!-- Test 3: Informations pratiques -->
        <div class="bg-gray-50 p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-xl font-bold mb-4">Test 3: Informations pratiques</h2>
            <ul class="space-y-2 text-sm">
                <li class="flex items-center">
                    <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
                    <span>Financement possible pour les travaux de facade avec nos partenaires bancaires</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
                    <span>Garantie de 10 ans sur nos interventions de facade et matériaux utilisés</span>
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
                    <span>Délais d'exécution rapides et respectés pour votre tranquillité d'esprit</span>
                </li>
            </ul>
        </div>
        
        <!-- Test 4: Vérification du chargement de Font Awesome -->
        <div class="bg-blue-50 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-bold mb-4">Test 4: Vérification Font Awesome</h2>
            <div id="fontawesome-test">
                <p>Si vous voyez des icônes ci-dessus, Font Awesome fonctionne correctement.</p>
                <p>Si vous voyez des carrés ou du texte à la place des icônes, il y a un problème de chargement.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Test JavaScript pour vérifier Font Awesome
        document.addEventListener('DOMContentLoaded', function() {
            const testElement = document.getElementById('fontawesome-test');
            const icon = document.createElement('i');
            icon.className = 'fas fa-check';
            testElement.appendChild(icon);
            testElement.innerHTML += ' <-- Icône générée par JavaScript';
        });
    </script>
</body>
</html>

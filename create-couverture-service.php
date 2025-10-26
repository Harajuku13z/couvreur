<?php

// Script pour créer un service "couverture" complet
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;

echo "Création du service 'couverture' complet\n";
echo "=======================================\n\n";

// Récupérer les services existants
$servicesData = Setting::get('services', '[]');
$services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);

if (!is_array($services)) {
    $services = [];
}

// Vérifier si le service couverture existe déjà
$couvertureExists = false;
foreach ($services as $service) {
    if (isset($service['slug']) && $service['slug'] === 'couverture') {
        $couvertureExists = true;
        break;
    }
}

if ($couvertureExists) {
    echo "❌ Le service 'couverture' existe déjà\n";
    exit(1);
}

// Créer le service couverture complet
$couvertureService = [
    'id' => 'couverture_' . time(),
    'name' => 'Couverture',
    'slug' => 'couverture',
    'short_description' => 'Service professionnel de couverture par Sauser Couverture à Chantilly et dans l\'Oise',
    'description' => '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de couverture à Chantilly, une expertise reconnue dans l\'Oise. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de couverture durables et esthétiques, adaptés aux spécificités climatiques locales.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de couverture pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation de toiture, garantissant des résultats durables et performants pour votre habitation.</p>
      <p class="text-lg leading-relaxed">Approche personnalisée pour chaque projet de couverture, satisfaction garantie. De l\'audit initial à la finition, notre équipe d\'artisans qualifiés assure un suivi rigoureux et respecte les délais d\'exécution convenus avec nos clients.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez Sauser Couverture, nous garantissons la satisfaction totale de nos clients à Chantilly et dans toute la région de l\'Oise. Chaque intervention de couverture est réalisée selon les normes professionnelles les plus strictes et les réglementations en vigueur.</p>
      <p class="leading-relaxed">Utilisation de matériaux durables et techniques modernes pour votre toiture. Nous privilégions les produits écologiques et performants, garantissant une longévité exceptionnelle et une esthétique soignée pour votre habitation, tout en respectant l\'environnement.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations Couverture</h3>
    <ul class="space-y-3">
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réparation de toiture</strong> - Diagnostic précis et traitement adapté pour restaurer l\'intégrité de votre toiture, avec intervention rapide et efficace</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Rénovation complète de toiture</strong> - Remplacement intégral avec matériaux de qualité et techniques modernes, garantissant une performance optimale</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Pose de tuiles et ardoises</strong> - Installation professionnelle selon les normes en vigueur, avec choix de matériaux adaptés à votre région</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Réfection de charpente</strong> - Renforcement et réparation des structures porteuses, assurant la solidité et la sécurité de votre toiture</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Installation de gouttières</strong> - Pose et réparation de systèmes d\'évacuation des eaux, optimisant la gestion des eaux pluviales</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Isolation de toiture</strong> - Amélioration de la performance énergétique avec des matériaux isolants performants et durables</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Zinguerie et étanchéité</strong> - Pose de zinc et traitement d\'étanchéité pour une protection optimale contre les intempéries</span>
      </li>
      <li class="flex items-start">
        <i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>
        <span><strong>Urgences toiture</strong> - Intervention rapide 24h/24 pour les réparations d\'urgence, minimisant les dégâts et les risques</span>
      </li>
    </ul>
    
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>
      <p class="leading-relaxed">Réputation locale solide pour les travaux de couverture à Chantilly et dans l\'Oise. Forts de plus de 15 ans d\'expérience, nous avons réalisé des centaines de projets de couverture avec succès. Notre connaissance approfondie des spécificités climatiques locales nous permet d\'adapter nos techniques et matériaux pour garantir des résultats durables et esthétiques, tout en respectant votre budget et vos délais.</p>
    </div>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences climatiques locales pour chaque projet de couverture à Chantilly. L\'Oise présente des défis spécifiques : humidité, variations de température, pollution urbaine. Notre équipe maîtrise parfaitement ces contraintes et adapte ses interventions en conséquence. Nous utilisons des matériaux testés et approuvés pour résister aux conditions climatiques picardes, garantissant ainsi la longévité de vos travaux de couverture et votre tranquillité d\'esprit.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit et personnalisé pour vos travaux de couverture. Notre expert se déplace à Chantilly pour évaluer votre projet et vous proposer la solution la plus adaptée à vos besoins et à votre budget, avec des conseils personnalisés.</p>
      <a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour les travaux de couverture avec nos partenaires bancaires</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de 10 ans sur nos interventions de couverture et matériaux utilisés</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Délais d\'exécution rapides et respectés pour votre tranquillité d\'esprit</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Conseils personnalisés pour l\'entretien et la durabilité de votre toiture</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Équipe qualifiée et professionnelle à votre service pour toute demande</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Respect des normes environnementales et réglementations en vigueur</span>
        </li>
      </ul>
    </div>
  </div>
</div>',
    'icon' => 'fas fa-tools',
    'featured_image' => null,
    'is_featured' => true,
    'is_menu' => true,
    'is_visible' => true,
    'meta_title' => 'Couverture - Sauser Couverture',
    'meta_description' => 'Service professionnel de couverture à Chantilly. Travaux de toiture par des professionnels qualifiés. Devis gratuit, intervention rapide, qualité garantie.',
    'meta_keywords' => 'couverture, Chantilly, toiture, rénovation, réparation, devis gratuit',
    'og_title' => 'Couverture - Sauser Couverture',
    'og_description' => 'Service professionnel de couverture à Chantilly. Travaux de toiture par des professionnels qualifiés. Devis gratuit, intervention rapide, qualité garantie.',
    'og_image' => null,
    'twitter_title' => 'Couverture - Sauser Couverture',
    'twitter_description' => 'Service professionnel de couverture à Chantilly. Travaux de toiture par des professionnels qualifiés. Devis gratuit, intervention rapide, qualité garantie.',
    'created_at' => now()->toISOString(),
    'updated_at' => now()->toISOString(),
];

// Ajouter le service couverture
$services[] = $couvertureService;

// Sauvegarder
Setting::set('services', $services, 'json');

echo "✅ Service 'couverture' créé avec succès !\n";
echo "ID: " . $couvertureService['id'] . "\n";
echo "Nom: " . $couvertureService['name'] . "\n";
echo "Slug: " . $couvertureService['slug'] . "\n";
echo "Description: " . strlen($couvertureService['description']) . " caractères\n\n";

echo "Vous pouvez maintenant accéder à :\n";
echo "- Service couverture: /services/couverture\n\n";

echo "✅ Service 'couverture' créé !\n";

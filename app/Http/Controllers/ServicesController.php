<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AiService;

class ServicesController extends Controller
{
    /**
     * Afficher la liste des services (admin)
     */
    public function index()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        return view('admin.services.index', compact('services'));
    }

    /**
     * Afficher tous les services (page publique)
     */
    public function publicIndex()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        // Filtrer les services visibles
        $visibleServices = collect($services)->filter(function($service) {
            return isset($service['is_visible']) ? $service['is_visible'] : true;
        });
        
        // Set current page for SEO
        $currentPage = 'services';
        
        return view('services.index', compact('visibleServices', 'currentPage'));
    }

    /**
     * Afficher le formulaire de création de service
     */
    public function create()
    {
        return view('admin.services.create');
    }

    /**
     * Enregistrer un nouveau service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_menu' => 'nullable|boolean',
            'ai_prompt' => 'nullable|string|max:2000',
        ]);

        // Générer le slug
        $slug = Str::slug($validated['name']);
        
        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Générer automatiquement TOUT le contenu avec l'IA
        $aiContent = $this->generateCompleteServiceContent(
            $validated['name'], 
            $validated['short_description'],
            $companyInfo,
            $validated['ai_prompt'] ?? null
        );

        // Gérer l'upload d'image de mise en avant
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'), 'featured');
        }

        // Option pour ajouter des prestations automatiques (désactivé par défaut pour éviter la duplication)
        $addAutomaticPrestations = false; // Changez à true si vous voulez forcer l'ajout de prestations
        
        if ($addAutomaticPrestations) {
            // Générer les prestations avec icônes et les intégrer dans la description HTML
            $prestations = $this->generatePrestationsWithIcons($validated['name']);
            $prestationsHtml = $this->generatePrestationsHtml($prestations);
            $enhancedDescription = $aiContent['description'] . $prestationsHtml;
        } else {
            // Utiliser uniquement la description de l'IA (recommandé)
            $enhancedDescription = $aiContent['description'];
        }

        // Créer le service avec TOUT le contenu généré par l'IA
        $service = [
            'id' => time() . '_' . rand(1000, 9999),
            'name' => $validated['name'],
            'slug' => $slug,
            'short_description' => $aiContent['short_description'],
            'description' => $enhancedDescription, // Description avec prestations intégrées
            'icon' => $aiContent['icon'],
            'featured_image' => $featuredImagePath,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_menu' => $validated['is_menu'] ?? false,
            'is_visible' => true,
            'meta_title' => $aiContent['meta_title'],
            'meta_description' => $aiContent['meta_description'],
            'meta_keywords' => $aiContent['meta_keywords'],
            'og_title' => $aiContent['og_title'],
            'og_description' => $aiContent['og_description'],
            'og_image' => $featuredImagePath, // Utilise la même image par défaut
            'twitter_title' => $aiContent['twitter_title'],
            'twitter_description' => $aiContent['twitter_description'],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Sauvegarder dans les settings
        $this->saveService($service);

        return redirect()->route('services.admin.index')
            ->with('success', 'Service créé avec succès avec contenu généré par l\'IA');
    }

    /**
     * Afficher le formulaire d'édition d'un service
     */
    public function edit($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('id', $id);
        
        if (!$service) {
            return redirect()->route('services.admin.index')
                ->with('error', 'Service non trouvé');
        }
        
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Mettre à jour un service
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_menu' => 'nullable|boolean',
            'ai_prompt' => 'nullable|string|max:2000',
        ]);

        // Récupérer les informations de l'entreprise
        $companyInfo = $this->getCompanyInfo();
        
        // Générer automatiquement TOUT le contenu avec l'IA
        $aiContent = $this->generateCompleteServiceContent(
            $validated['name'], 
            $validated['short_description'],
            $companyInfo,
            $validated['ai_prompt'] ?? null
        );

        // Gérer l'upload d'image de mise en avant
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $this->handleImageUpload($request->file('featured_image'), 'featured');
        }

        // Mettre à jour le service
        $this->updateService($id, [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'short_description' => $aiContent['short_description'],
            'description' => $aiContent['description'],
            'icon' => $aiContent['icon'],
            'featured_image' => $featuredImagePath ?: $this->getServiceImage($id, 'featured_image'),
            'is_featured' => $validated['is_featured'] ?? false,
            'is_menu' => $validated['is_menu'] ?? false,
            'meta_title' => $aiContent['meta_title'],
            'meta_description' => $aiContent['meta_description'],
            'meta_keywords' => $aiContent['meta_keywords'],
            'og_title' => $aiContent['og_title'],
            'og_description' => $aiContent['og_description'],
            'og_image' => $featuredImagePath ?: $this->getServiceImage($id, 'og_image'),
            'twitter_title' => $aiContent['twitter_title'],
            'twitter_description' => $aiContent['twitter_description'],
            'updated_at' => now()->toISOString(),
        ]);

        return redirect()->route('services.admin.index')
            ->with('success', 'Service mis à jour avec succès avec contenu généré par l\'IA');
    }

    /**
     * Afficher un service (page publique)
     */
    public function show($slug)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('slug', $slug);
        
        if (!$service) {
            abort(404);
        }
        
        // Récupérer les services liés (autres services)
        $relatedServices = collect($services)->filter(function($s) use ($service) {
            return $s['id'] !== $service['id'] && (isset($s['is_visible']) ? $s['is_visible'] : true);
        })->take(3);
        
        // Variables pour le SEO centralisé
        $currentPage = 'services';
        $pageTitle = $service['meta_title'] ?? $service['name'] . ' - ' . setting('company_name', 'Sauser Couverture');
        $pageDescription = $service['meta_description'] ?? $service['short_description'] ?? 'Découvrez nos services de ' . $service['name'] . '. Devis gratuit, intervention rapide, qualité garantie.';
        $pageImage = $service['featured_image'] ?? $service['og_image'] ?? null; // null pour utiliser l'image par défaut du SeoHelper
        $pageType = 'website';
        
        return view('services.show', compact('service', 'relatedServices', 'currentPage', 'pageTitle', 'pageDescription', 'pageImage', 'pageType'));
    }

    /**
     * Supprimer un service
     */
    public function destroy($id)
    {
        \Log::info("Attempting to delete service with ID: {$id}");
        
        // Vérifier que le service existe avant de le supprimer
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        \Log::info("Current services count: " . count($services));
        \Log::info("Services data: " . json_encode($services));
        
        $serviceExists = false;
        $serviceName = '';
        if (is_array($services)) {
            foreach ($services as $service) {
                \Log::info("Checking service ID: " . ($service['id'] ?? 'NULL') . " against: {$id}");
                if (isset($service['id']) && $service['id'] == $id) {
                    $serviceExists = true;
                    $serviceName = $service['name'] ?? 'Unknown';
                    \Log::info("Service found: {$serviceName}");
                    break;
                }
            }
        }
        
        if (!$serviceExists) {
            \Log::warning("Service with ID {$id} not found");
            return redirect()->route('services.admin.index')
                ->with('error', 'Service non trouvé (ID: ' . $id . ')');
        }
        
        \Log::info("Deleting service: {$serviceName} (ID: {$id})");
        $this->deleteService($id);
        
        return redirect()->route('services.admin.index')
            ->with('success', 'Service "' . $serviceName . '" supprimé avec succès');
    }

    /**
     * Régénérer le contenu d'un service avec l'IA
     */
    public function regenerate(Request $request, $id)
    {
        try {
            $servicesData = Setting::get('services', '[]');
            $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
            
            $service = collect($services)->firstWhere('id', $id);
            
            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service non trouvé'
                ], 404);
            }

            // Récupérer les informations de l'entreprise
            $companyInfo = $this->getCompanyInfo();
            
            // Générer automatiquement TOUT le contenu avec l'IA
            $aiContent = $this->generateCompleteServiceContent(
                $service['name'], 
                $service['short_description'],
                $companyInfo,
                $request->input('ai_prompt')
            );

            // Mettre à jour le service avec le nouveau contenu
            $this->updateService($id, [
                'short_description' => $aiContent['short_description'],
                'description' => $aiContent['description'],
                'icon' => $aiContent['icon'],
                'meta_title' => $aiContent['meta_title'],
                'meta_description' => $aiContent['meta_description'],
                'meta_keywords' => $aiContent['meta_keywords'],
                'og_title' => $aiContent['og_title'],
                'og_description' => $aiContent['og_description'],
                'og_image' => null, // Forcer l'utilisation de l'image par défaut
                'twitter_title' => $aiContent['twitter_title'],
                'twitter_description' => $aiContent['twitter_description'],
                'updated_at' => now()->toISOString(),
            ]);

            // Si c'est une requête AJAX, retourner du JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contenu régénéré avec succès par l\'IA',
                    'content' => $aiContent
                ]);
            }
            
            // Sinon, rediriger avec un message de succès
            return redirect()->route('services.admin.index')
                ->with('success', 'Contenu régénéré avec succès par l\'IA');

        } catch (\Exception $e) {
            Log::error('Erreur régénération service: ' . $e->getMessage());
            
            // Si c'est une requête AJAX, retourner du JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la régénération: ' . $e->getMessage()
                ], 500);
            }
            
            // Sinon, rediriger avec un message d'erreur
            return redirect()->route('services.admin.index')
                ->with('error', 'Erreur lors de la régénération: ' . $e->getMessage());
        }
    }

    /**
     * Générer TOUT le contenu du service avec l'IA
     */
    private function generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo, $customPrompt = null)
    {
        try {
            // Prompt amélioré avec 10 champs selon vos spécifications
            $prompt = "Tu es un expert en rédaction web pour les services de rénovation.

INFORMATIONS SUR LE SERVICE:
- Type de service : {$serviceName}
- Description actuelle : {$shortDescription}
- Objectif : créer un contenu web professionnel, engageant et optimisé pour le SEO, adapté à ce type de service.

TÂCHE:
Génère un contenu web complet comprenant :

1. Une description courte (120-140 caractères) accrocheuse et SEO.
2. Une longue description générale expliquant le service et les bénéfices pour le client avec un lien 'En savoir plus'.
3. Une icône Font Awesome adaptée au service.
4. Un titre SEO optimisé (50-60 caractères).
5. Une description SEO (150-160 caractères).
6. Des mots-clés pertinents séparés par des virgules.
7. Un contenu HTML complet structuré professionnellement, avec exactement **10 champs** :
   - **Champ 1 :** Introduction courte et longue
   - **Champ 2 :** Engagement / garanties
   - **Champ 3 :** Prestations / services (exactement 10 prestations spécifiques au service)
   - **Champ 4 :** Pourquoi choisir ce service
   - **Champ 5 :** Expertise / expérience
   - **Champ 6 :** CTA : demande de devis
   - **Champ 7 :** Informations pratiques
   - **Champ 8 :** FAQ (3 questions-réponses minimum)
   - **Champ 9 :** Financement / aides disponibles
   - **Champ 10 :** Partage social

STRUCTURE HTML OBLIGATOIRE (utilise exactement cette structure):
<div class=\"grid md:grid-cols-2 gap-8\">

  <!-- Colonne gauche : Contenu principal -->
  <div class=\"space-y-6\">

    <!-- Champ 1 : Introduction / description courte + longue -->
    <div class=\"space-y-4\">
      <p class=\"text-lg leading-relaxed\">[Description courte du service {$serviceName} en 250 caractères maximum]</p>
      <p class=\"text-lg leading-relaxed\">[Longue description détaillée du service {$serviceName}, bénéfices, explications techniques, avantages, matériaux utilisés]</p>
    </div>

    <!-- Champ 2 : Engagement / garanties -->
    <div class=\"bg-blue-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">[Titre de l'engagement ou garantie pour {$serviceName}]</h3>
      <p class=\"leading-relaxed mb-3\">[Description de l'engagement pour {$serviceName}]</p>
      <p class=\"leading-relaxed\">[Détails techniques ou matériaux utilisés pour {$serviceName}]</p>
    </div>

    <!-- Champ 3 : Prestations / services -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Nos Prestations {$serviceName}</h3>
    <ul class=\"space-y-3\">
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 1 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 2 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 3 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 4 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 5 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 6 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 7 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 8 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 9 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
      <li class=\"flex items-start\">
        <i class=\"fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0\"></i>
        <span><strong>[Prestation 10 technique et spécifique à {$serviceName} uniquement]</strong> - [Description technique détaillée de cette prestation précise pour {$serviceName}]</span>
      </li>
    </ul>

    <!-- Champ 8 : FAQ -->
    <div class=\"bg-gray-50 p-6 rounded-lg mt-6\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">FAQ {$serviceName}</h4>
      <div class=\"space-y-2\">
        <p><strong>Q1 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q2 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
        <p><strong>Q3 : [Question fréquente sur {$serviceName}]</strong></p>
        <p>A : [Réponse détaillée]</p>
      </div>
    </div>

  </div>

  <!-- Colonne droite : Informations complémentaires -->
  <div class=\"space-y-6\">

    <!-- Champ 4 : Pourquoi choisir ce service -->
    <div class=\"bg-green-50 p-6 rounded-lg\">
      <h3 class=\"text-xl font-bold text-gray-900 mb-3\">Pourquoi choisir {$serviceName}</h3>
      <p class=\"leading-relaxed\">[Points forts et avantages du service {$serviceName}]</p>
    </div>

    <!-- Champ 5 : Expertise / expérience -->
    <h3 class=\"text-2xl font-bold text-gray-900 mb-4\">Notre Expertise {$serviceName}</h3>
    <p class=\"leading-relaxed\">[Description de l'expertise et expérience en {$serviceName}]</p>

    <!-- Champ 9 : Financement / aides disponibles -->
    <div class=\"bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Financement et aides</h4>
      <p>[Informations sur les aides financières et options de financement pour {$serviceName}]</p>
    </div>

    <!-- Champ 6 : CTA - demande de devis -->
    <div class=\"bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600\">
      <h4 class=\"text-xl font-bold text-gray-900 mb-3\">Besoin d'un devis ?</h4>
      <p class=\"mb-4\">Contactez-nous pour un devis gratuit pour {$serviceName}.</p>
      <a href=\"[FORM_URL]\" class=\"inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300\">Demande de devis</a>
    </div>

    <!-- Champ 7 : Informations pratiques -->
    <div class=\"bg-gray-50 p-6 rounded-lg\">
      <h4 class=\"text-lg font-bold text-gray-900 mb-3\">Informations Pratiques</h4>
      <ul class=\"space-y-2 text-sm\">
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 1 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 2 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 3 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 4 pour {$serviceName}]</span></li>
        <li class=\"flex items-center\"><i class=\"fas fa-check text-green-600 mr-3 flex-shrink-0\"></i><span>[Avantage 5 pour {$serviceName}]</span></li>
      </ul>
    </div>

    <!-- Champ 10 : Partage social -->
    <div class=\"mt-8 pt-6 border-t border-gray-200\">
      <div class=\"text-center\">
        <h4 class=\"text-lg font-semibold text-gray-800 mb-4\">Partager ce service</h4>
        <div class=\"flex justify-center items-center space-x-4\">
          <a href=\"https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-facebook-f text-lg\"></i>
            <span class=\"font-medium\">Facebook</span>
          </a>
          <a href=\"https://wa.me/?text=[TITRE] - [URL]\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fab fa-whatsapp text-lg\"></i>
            <span class=\"font-medium\">WhatsApp</span>
          </a>
          <a href=\"mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]\" class=\"bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1\">
            <i class=\"fas fa-envelope text-lg\"></i>
            <span class=\"font-medium\">Email</span>
          </a>
        </div>
      </div>
    </div>

  </div>

</div>

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU 100% PERSONNALISÉ OBLIGATOIRE ⚠️⚠️⚠️:

🚫 INTERDICTIONS ABSOLUES:
- INTERDIT d'utiliser des prestations génériques comme \\\"Réparation et maintenance\\\", \\\"Rénovation complète\\\", \\\"Installation professionnelle\\\", \\\"Conseils personnalisés\\\"
- INTERDIT de copier/coller du contenu d'autres services
- INTERDIT d'utiliser des templates pré-faits
- INTERDIT de générer du contenu vague ou général

✅ OBLIGATIONS ABSOLUES:
- Chaque prestation DOIT être TECHNIQUE et SPÉCIFIQUE UNIQUEMENT à {$serviceName}
- Pour {$serviceName}, génère des prestations VRAIMENT adaptées (ex: si c'est \\\"Désamiantage\\\" → \\\"Dépollution amiante\\\", \\\"Retrait sécurisé amiante\\\", \\\"Confinement amiante\\\", etc.)
- Pour {$serviceName}, utilise le vocabulaire TECHNIQUE du domaine
- Chaque description de prestation doit expliquer QUOI, COMMENT et POURQUOI spécifiquement pour {$serviceName}
- Les 10 prestations doivent être DIFFÉRENTES et COMPLÉMENTAIRES pour {$serviceName}

EXEMPLES DE PRESTATIONS SPÉCIFIQUES (à adapter à {$serviceName}):
- Si {$serviceName} = \\\"Désamiantage\\\" → \\\"Dépollution amiante\\\", \\\"Diagnostic amiante avant travaux\\\", \\\"Retrait amiante sous confinement\\\", \\\"Gestion déchets amiante\\\", etc.
- Si {$serviceName} = \\\"Traitement humidité\\\" → \\\"Diagnostic humidité par imagerie thermique\\\", \\\"Injection résine anti-humidité\\\", \\\"Installation VMC double flux\\\", \\\"Traitement remontées capillaires\\\", etc.
- Si {$serviceName} = \\\"Élagage\\\" → \\\"Élagage raisonné\\\", \\\"Taille de formation\\\", \\\"Haubanage\\\", \\\"Abattage sécurisé\\\", etc.

INSTRUCTIONS TECHNIQUES:
- Le contenu doit être UNIQUE, professionnel et engageant.
- Utilise un vocabulaire technique adapté à {$serviceName} spécifiquement.
- Inclue les aides financières et options de financement possibles.
- Fournis les informations pratiques, conseils et suivi après travaux.
- Ne mentionne jamais le nom de l'entreprise ni la localisation.
- Garde la structure HTML exacte.
- Génère des prestations détaillées avec descriptions explicites.
- REMPLACE [FORM_URL] par l'URL du formulaire de devis
- GÉNÈRE exactement 10 prestations TECHNIQUES et SPÉCIFIQUES à {$serviceName} UNIQUEMENT
- Chaque prestation doit avoir un NOM TECHNIQUE précis (pas générique) pour {$serviceName}
- Chaque prestation doit avoir une DESCRIPTION TECHNIQUE détaillée expliquant la méthode pour {$serviceName}
- UTILISE le vocabulaire technique professionnel du domaine de {$serviceName}
- OBLIGATOIRE : Génère une LONGUE DESCRIPTION détaillée (minimum 500 caractères) expliquant {$serviceName}
- La longue description doit expliquer les bénéfices, techniques utilisées, matériaux, avantages spécifiques à {$serviceName}
- NE PAS inclure de bouton \"En savoir plus\" ou lien externe dans le contenu
- Le contenu dans 'description' doit être COMPLET (minimum 1500 mots) et TOTALEMENT UNIQUE pour {$serviceName}
- Les prestations doivent refléter la RÉALITÉ technique du métier {$serviceName}

RÉPONSE FORMAT JSON:
{
  \"description\": \"[HTML complet avec structure exacte]\",
  \"short_description\": \"[Description courte 120-140 caractères]\",
  \"long_description\": \"[Longue description détaillée du service, bénéfices, techniques, matériaux, avantages - minimum 300 caractères]\",
  \"icon\": \"fas fa-[icône appropriée]\",
  \"meta_title\": \"[Titre SEO 50-60 caractères]\",
  \"meta_description\": \"[Description SEO 150-160 caractères]\",
  \"og_title\": \"[Titre Open Graph 50-60 caractères]\",
  \"og_description\": \"[Description Open Graph 150-160 caractères]\",
  \"twitter_title\": \"[Titre Twitter 50-60 caractères]\",
  \"twitter_description\": \"[Description Twitter 150-160 caractères]\",
  \"meta_keywords\": \"[Mots-clés pertinents séparés par virgules]\"
}

Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

            Log::info('=== DÉBUT GÉNÉRATION IA SERVICE ===', [
                'service_name' => $serviceName,
                'short_description' => $shortDescription,
                'prompt_length' => strlen($prompt),
                'chatgpt_enabled' => setting('chatgpt_enabled', true),
                'chatgpt_api_key_exists' => !empty(setting('chatgpt_api_key')),
                'groq_api_key_exists' => !empty(setting('groq_api_key', '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'))
            ]);
            
            // Ajouter un identifiant unique pour forcer la génération unique
            $uniqueId = uniqid();
            $timestamp = now()->toIso8601String();
            
            // Instructions ultra-spécifiques pour éviter le contenu générique
            $specificityInstructions = "\n\n🚫🚫🚫 INTERDICTIONS ABSOLUES - NE PAS UTILISER CES PRESTATIONS 🚫🚫🚫:
- INTERDIT: 'Réparation et maintenance'
- INTERDIT: 'Rénovation complète'
- INTERDIT: 'Installation professionnelle'
- INTERDIT: 'Conseils personnalisés'
- INTERDIT: Toute prestation générique ou vague

✅✅✅ EXIGENCES ABSOLUES POUR {$serviceName} ✅✅✅:
- Chaque prestation DOIT avoir un NOM TECHNIQUE précis du domaine de {$serviceName}
- Chaque prestation DOIT expliquer la MÉTHODE et la TECHNIQUE utilisée
- Les prestations doivent être SPÉCIFIQUES au métier de {$serviceName}

EXEMPLES CONCRETS POUR {$serviceName}:
";
            
            // Ajouter des exemples selon le type de service
            $serviceNameLower = mb_strtolower($serviceName);
            if (strpos($serviceNameLower, 'désamiantage') !== false || strpos($serviceNameLower, 'amiante') !== false) {
                $specificityInstructions .= "- Bon: 'Dépollution amiante', 'Retrait amiante sous confinement', 'Gestion déchets amiante'\n- Mauvais: 'Réparation et maintenance', 'Installation professionnelle'\n";
            } elseif (strpos($serviceNameLower, 'humidité') !== false || strpos($serviceNameLower, 'ventilation') !== false) {
                $specificityInstructions .= "- Bon: 'Diagnostic humidité par imagerie thermique', 'Injection résine anti-humidité', 'Installation VMC double flux'\n- Mauvais: 'Réparation et maintenance', 'Conseils personnalisés'\n";
            } else {
                $specificityInstructions .= "- Pour {$serviceName}, utilise le vocabulaire TECHNIQUE et les prestations RÉELLES du métier\n- Recherche les termes techniques professionnels spécifiques à {$serviceName}\n- Évite TOUT ce qui pourrait s'appliquer à n'importe quel autre service\n";
            }
            
            $specificityInstructions .= "\nID unique: {$uniqueId} | Timestamp: {$timestamp}
Le contenu DOIT être UNIQUE et DIFFÉRENT de toute génération précédente pour {$serviceName}.";
            
            $promptWithUniqueness = $prompt . $specificityInstructions;
            
            // Utiliser le service AI avec fallback automatique
            $systemMessage = "Tu es un expert technique en {$serviceName} avec une connaissance approfondie du domaine. Tu crées du contenu professionnel, technique et optimisé SEO. CRITIQUE ABSOLUE: Chaque prestation DOIT être TECHNIQUE, SPÉCIFIQUE et utiliser le vocabulaire professionnel du métier de {$serviceName}. INTERDIT d'utiliser des prestations génériques ou des templates pré-faits. Chaque description de prestation doit expliquer QUOI (technique précise), COMMENT (méthode utilisée) et POURQUOI (bénéfice spécifique pour {$serviceName}).";
            $result = AiService::callAI($promptWithUniqueness, $systemMessage, [
                'max_tokens' => 4000,
                'temperature' => 0.9  // Augmenté pour plus de créativité et variété
            ]);
            
            Log::info('Résultat appel AiService', [
                'service_name' => $serviceName,
                'has_result' => !is_null($result),
                'has_content' => isset($result['content']),
                'provider' => $result['provider'] ?? 'none',
                'content_length' => isset($result['content']) ? strlen($result['content']) : 0
            ]);
            
            if ($result && isset($result['content'])) {
                Log::info('Réponse IA reçue', [
                    'service_name' => $serviceName,
                    'provider' => $result['provider'],
                    'content_length' => strlen($result['content']),
                    'content_preview' => substr($result['content'], 0, 500)
                ]);
                
                // Parser le JSON de manière plus robuste
                $aiData = $this->parseAIResponse($result['content']);
                
                if ($aiData && is_array($aiData)) {
                    // Vérifier que le contenu contient bien le nom du service
                    $descriptionContainsService = isset($aiData['description']) && stripos($aiData['description'], $serviceName) !== false;
                    $isGeneric = isset($aiData['description']) && (
                        stripos($aiData['description'], 'Service professionnel') !== false && 
                        strlen($aiData['description']) < 500
                    );
                    
                    // Vérifier la présence de prestations génériques interdites
                    $genericPrestations = [
                        'Réparation et maintenance',
                        'Rénovation complète',
                        'Installation professionnelle',
                        'Conseils personnalisés',
                        'Accompagnement dans vos choix',
                        'Diagnostic précis et traitement adapté',
                        'Remplacement intégral avec matériaux de qualité',
                        'Pose selon les normes en vigueur'
                    ];
                    
                    $containsGenericPrestations = false;
                    $descriptionHtml = $aiData['description'] ?? '';
                    foreach ($genericPrestations as $generic) {
                        if (stripos($descriptionHtml, $generic) !== false) {
                            $containsGenericPrestations = true;
                            Log::warning('Prestation générique détectée dans le contenu IA', [
                                'service_name' => $serviceName,
                                'generic_prestation' => $generic
                            ]);
                            break;
                        }
                    }
                    
                    if ($descriptionContainsService && !$isGeneric && !$containsGenericPrestations) {
                        Log::info('Contenu IA validé et personnalisé', ['service_name' => $serviceName]);
                    // Valider et nettoyer les données
                    return $this->validateAndCleanAIData($aiData, $serviceName, $shortDescription, $companyInfo);
                    } else {
                        Log::warning('Contenu IA rejeté - générique ou contient prestations interdites', [
                            'service_name' => $serviceName,
                            'contains_service' => $descriptionContainsService,
                            'is_generic' => $isGeneric,
                            'contains_generic_prestations' => $containsGenericPrestations
                        ]);
                }
            } else {
                    Log::warning('Échec parsing JSON de la réponse IA', [
                        'service_name' => $serviceName,
                        'content_preview' => substr($result['content'], 0, 300)
                    ]);
                }
            } else {
                Log::error('Aucune réponse de l\'IA', [
                    'service_name' => $serviceName,
                    'result' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur génération IA: ' . $e->getMessage(), [
                'service_name' => $serviceName,
                'error' => $e->getTraceAsString()
            ]);
        }
        
        // Fallback amélioré en cas d'échec
        return $this->generateEnhancedFallbackContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Parser la réponse IA de manière robuste
     */
    private function parseAIResponse($content)
    {
        // Nettoyer le contenu
        $content = trim($content);
        
        // Si le contenu semble être directement du HTML (pas de JSON), créer un JSON avec
        if (strpos($content, '<div') !== false && strpos($content, '{') === false) {
            Log::info('Contenu HTML direct détecté, création de structure JSON');
            // Extraire une description courte du HTML
            $plainText = strip_tags($content);
            $shortDesc = Str::limit($plainText, 140);
            $metaDesc = Str::limit($plainText, 160);
            
            return [
                'description' => $content,
                'short_description' => $shortDesc,
                'long_description' => Str::limit($plainText, 500),
                'icon' => 'fas fa-tools',
                'meta_title' => '',
                'meta_description' => $metaDesc,
                'og_title' => '',
                'og_description' => $metaDesc,
                'twitter_title' => '',
                'twitter_description' => $metaDesc,
                'meta_keywords' => ''
            ];
        }
        
        // Chercher le JSON dans différentes positions (amélioré pour capturer plus de cas)
        $jsonPatterns = [
            '/```json\s*(\{[\s\S]*?\})\s*```/s',  // JSON dans code block avec json
            '/```\s*(\{[\s\S]*?\})\s*```/s',  // JSON dans code block sans json
            '/\{[\s\S]*\"description\"[\s\S]*\}/s',  // JSON avec description
            '/\{.*\}/s',  // Pattern général (en dernier)
        ];
        
        foreach ($jsonPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $jsonString = $matches[1] ?? $matches[0];
                // Nettoyer le JSON
                $jsonString = trim($jsonString);
                $data = json_decode($jsonString, true);
                
                if ($data && is_array($data) && !empty($data)) {
                    Log::info('JSON parsé avec succès', [
                        'keys' => array_keys($data)
                    ]);
                    return $data;
                } else {
                    Log::warning('Échec décodage JSON', [
                        'json_error' => json_last_error_msg(),
                        'json_preview' => substr($jsonString, 0, 300)
                    ]);
                }
            }
        }
        
        // Essayer de parser directement
        $data = json_decode($content, true);
        if ($data && is_array($data) && !empty($data)) {
            Log::info('JSON parsé directement');
            return $data;
        }
        
        // Dernière tentative : chercher juste le HTML dans la description
        if (preg_match('/"description"\s*:\s*"([^"]*(?:\\.[^"]*)*)"/s', $content, $matches)) {
            Log::info('Extraction description HTML depuis JSON malformé');
            $htmlContent = str_replace('\\"', '"', $matches[1]);
            $htmlContent = str_replace('\\n', "\n", $htmlContent);
            
            $plainText = strip_tags($htmlContent);
            return [
                'description' => $htmlContent,
                'short_description' => Str::limit($plainText, 140),
                'long_description' => Str::limit($plainText, 500),
                'icon' => 'fas fa-tools',
                'meta_title' => '',
                'meta_description' => Str::limit($plainText, 160),
                'og_title' => '',
                'og_description' => Str::limit($plainText, 160),
                'twitter_title' => '',
                'twitter_description' => Str::limit($plainText, 160),
                'meta_keywords' => ''
            ];
        }
        
        Log::warning('Impossible de parser la réponse IA', [
            'content_preview' => substr($content, 0, 500),
            'json_error' => json_last_error_msg()
        ]);
        
        return null;
    }

    /**
     * Valider et nettoyer les données IA
     */
    private function validateAndCleanAIData($aiData, $serviceName, $shortDescription, $companyInfo)
    {
        // Fonction pour nettoyer et limiter la longueur
        $cleanText = function($text, $maxLength = null) {
            $text = trim(strip_tags($text));
            if ($maxLength && strlen($text) > $maxLength) {
                $text = substr($text, 0, $maxLength - 3) . '...';
            }
            return $text;
        };

        // Fonction pour générer des mots-clés pertinents
        $generateKeywords = function($serviceName, $companyInfo) {
            $baseKeywords = [
                strtolower($serviceName),
                $companyInfo['company_city'],
                $companyInfo['company_region'],
                'devis gratuit',
                'professionnel',
                'qualité'
            ];
            
            // Ajouter des mots-clés spécifiques selon le service
            $serviceKeywords = [
                'toiture' => ['couverture', 'réparation', 'rénovation', 'charpente'],
                'façade' => ['ravalement', 'peinture', 'enduit', 'isolation'],
                'isolation' => ['thermique', 'phonique', 'économies', 'énergie'],
                'gouttières' => ['zinguerie', 'évacuation', 'eaux pluviales'],
                'couvreur' => ['artisan', 'expert', 'intervention', 'urgence']
            ];
            
            $serviceLower = strtolower($serviceName);
            foreach ($serviceKeywords as $key => $keywords) {
                if (str_contains($serviceLower, $key)) {
                    $baseKeywords = array_merge($baseKeywords, $keywords);
                }
            }
            
            return implode(', ', array_unique($baseKeywords));
        };

        // Remplacer [FORM_URL] par l'URL correcte du formulaire
        $description = $aiData['description'] ?? $this->generateDefaultDescription($serviceName, $companyInfo);
        $siteUrl = setting('site_url', config('app.url'));
        if (!str_starts_with($siteUrl, 'http')) {
            $siteUrl = 'https://' . $siteUrl;
        }
        $formUrl = $siteUrl . '/form/propertyType';
        $description = str_replace('[FORM_URL]', $formUrl, $description);

        // S'assurer que short_description est bien générée par l'IA, pas juste le fallback
        $aiShortDescription = $aiData['short_description'] ?? null;
        if (!$aiShortDescription || strlen(trim($aiShortDescription)) < 20) {
            // Si l'IA n'a pas fourni de description courte, générer une depuis le HTML
            $plainFromHtml = strip_tags($description);
            $aiShortDescription = Str::limit($plainFromHtml, 140);
            Log::info('Description courte générée depuis HTML', ['service_name' => $serviceName]);
        }

        return [
            'description' => $description,
            'short_description' => $cleanText($aiShortDescription, 140),
            'long_description' => $cleanText($aiData['long_description'] ?? strip_tags($description), 500),
            'icon' => $aiData['icon'] ?? $this->getServiceIcon($serviceName),
            'meta_title' => $cleanText($aiData['meta_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'], 60),
            'meta_description' => $cleanText($aiData['meta_description'] ?? $shortDescription, 160),
            'og_title' => $cleanText($aiData['og_title'] ?? $aiData['meta_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'], 60),
            'og_description' => $cleanText($aiData['og_description'] ?? $aiData['meta_description'] ?? $shortDescription, 160),
            'twitter_title' => $cleanText($aiData['twitter_title'] ?? $aiData['meta_title'] ?? $serviceName . ' - ' . $companyInfo['company_name'], 60),
            'twitter_description' => $cleanText($aiData['twitter_description'] ?? $aiData['meta_description'] ?? $shortDescription, 160),
            'meta_keywords' => $aiData['meta_keywords'] ?? $generateKeywords($serviceName, $companyInfo)
        ];
    }

    /**
     * Générer des prestations spécifiques selon le type de service
     */
    private function generateSpecificPrestationsForService($serviceName)
    {
        $serviceNameLower = mb_strtolower($serviceName);
        $prestations = [];
        
        // Désamiantage
        if (strpos($serviceNameLower, 'désamiantage') !== false || strpos($serviceNameLower, 'amiante') !== false) {
            $prestations = [
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Diagnostic amiante avant travaux</strong> - Recherche et identification de tous les matériaux contenant de l\'amiante selon la réglementation</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Dépollution amiante</strong> - Retrait sécurisé et conforme des matériaux amiantés avec confinement négatif</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Confinement amiante</strong> - Mise en place d\'un sas de décontamination et d\'une zone de confinement étanche</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Retrait amiante sous confinement</strong> - Désamiantage avec techniques de retrait sous pression négative</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Gestion déchets amiante</strong> - Évacuation et traçabilité complète vers centre d\'enfouissement agréé</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Désamiantage flocage</strong> - Retrait des flocages amiantés au plafond selon normes NF X 46-020</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Désamiantage calorifugeage</strong> - Dépollution des canalisations et équipements calorifugés amiantés</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Réhabilitation post-désamiantage</strong> - Restauration complète après travaux avec matériaux conformes</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Contrôle de reprise</strong> - Mesures d\'empoussièrement et attestation de fin de travaux délivrée</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Formation sécurité amiante</strong> - Sensibilisation des occupants aux risques et aux mesures préventives</span></li>'
            ];
        }
        // Humidité et ventilation
        elseif (strpos($serviceNameLower, 'humidité') !== false || strpos($serviceNameLower, 'ventilation') !== false || strpos($serviceNameLower, 'vmc') !== false) {
            $prestations = [
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Diagnostic humidité par imagerie thermique</strong> - Détection précise des zones humides avec caméra infrarouge et hygrométrie</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Injection résine anti-humidité</strong> - Traitement des remontées capillaires par injection de résine polymère hydrofuge</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Installation VMC double flux</strong> - Mise en place de ventilation mécanique contrôlée avec récupération de chaleur</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Traitement remontées capillaires</strong> - Barrière étanche contre les remontées d\'eau par injection en nappe ou par drainage</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Assèchement des murs</strong> - Évacuation de l\'humidité avec système de drainage et de ventilation</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Ventilation naturelle assistée</strong> - Installation d\'aérateurs et grilles d\'aération pour renouvellement d\'air optimal</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Traitement condensation</strong> - Résolution des problèmes de condensation avec isolation thermique et ventilation adaptée</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Enduit d\'étanchéité</strong> - Application d\'enduits hydrofuges et respirants pour protection durable des murs</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Déshumidification de caves</strong> - Installation de déshumidificateurs et traitement des parois enterrées</span></li>',
                '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Maintenance ventilation</strong> - Entretien régulier des systèmes VMC, nettoyage des bouches et filtres</span></li>'
            ];
        }
        // Sinon, utiliser une approche intelligente basée sur des mots-clés
        else {
            // Pour chaque service, générer des prestations en analysant le nom
            $keywords = explode(' ', $serviceName);
            $prestations = $this->generatePrestationsFromKeywords($serviceName, $keywords);
        }
        
        // Si aucune prestation n'a été générée, utiliser un fallback minimal mais spécifique
        if (empty($prestations)) {
            return '<li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Service professionnel ' . htmlspecialchars($serviceName) . '</strong> - Intervention adaptée à vos besoins spécifiques</span></li>';
        }
        
        return implode("\n      ", $prestations);
    }
    
    /**
     * Générer des prestations à partir de mots-clés du service
     */
    private function generatePrestationsFromKeywords($serviceName, $keywords)
    {
        // Cette fonction sera améliorée par l'IA, mais pour l'instant retourne un contenu spécifique basé sur les mots-clés
        // L'IA devrait générer ce contenu de manière dynamique
        return [];
    }

    /**
     * Générer une description par défaut de qualité
     */
    private function generateDefaultDescription($serviceName, $companyInfo)
    {
        return '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">Service professionnel de ' . $serviceName . ' à ' . $companyInfo['company_city'] . ', une expertise reconnue dans ' . $companyInfo['company_region'] . '. Notre entreprise spécialisée intervient sur tous types de bâtiments pour des travaux de ' . $serviceName . ' durables et esthétiques.</p>
      <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $serviceName . ' pour une rénovation de qualité supérieure. Nous maîtrisons les techniques modernes de pose, de réparation et de rénovation, garantissant des résultats durables et performants.</p>
      <p class="text-lg leading-relaxed">Approche personnalisée pour chaque projet de ' . $serviceName . ', satisfaction garantie. De l\'audit initial à la finition, notre équipe d\'artisans qualifiés assure un suivi rigoureux.</p>
    </div>
    
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
      <p class="leading-relaxed mb-3">Chez ' . $companyInfo['company_name'] . ', nous garantissons la satisfaction totale de nos clients.</p>
      <p class="leading-relaxed">Utilisation de matériaux durables et techniques modernes pour votre ' . $serviceName . '.</p>
    </div>
    
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $serviceName . '</h3>
    <ul class="space-y-3">
      ' . $this->generateSpecificPrestationsForService($serviceName) . '
    </ul>
    
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi Choisir Notre Entreprise</h3>
      <p class="leading-relaxed">Réputation locale solide pour les travaux de ' . $serviceName . ' à ' . $companyInfo['company_city'] . '.</p>
    </div>
  </div>
  
  <div class="space-y-6">
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
    <p class="leading-relaxed">Une connaissance approfondie des exigences locales pour chaque projet de ' . $serviceName . ' à ' . $companyInfo['company_city'] . '.</p>
    
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un Devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit pour vos ' . $serviceName . '.</p>
      <a href="https://www.jd-renovation-service.fr/form/propertyType" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Financement possible pour vos travaux</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Garantie de qualité sur nos interventions</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Délais d\'exécution respectés</span>
        </li>
        <li class="flex items-center">
          <i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>
          <span>Équipe qualifiée et professionnelle</span>
        </li>
      </ul>
    </div>
  </div>
</div>';
    }

    /**
     * Obtenir l'icône appropriée pour le service
     */
    private function getServiceIcon($serviceName)
    {
        $serviceIcons = [
            'toiture' => 'fas fa-home',
            'couverture' => 'fas fa-home',
            'façade' => 'fas fa-building',
            'facade' => 'fas fa-building',
            'isolation' => 'fas fa-thermometer-half',
            'gouttières' => 'fas fa-tint',
            'gouttieres' => 'fas fa-tint',
            'couvreur' => 'fas fa-tools',
            'charpente' => 'fas fa-hammer',
            'zinguerie' => 'fas fa-wrench'
        ];
        
        $serviceLower = strtolower($serviceName);
        foreach ($serviceIcons as $key => $icon) {
            if (str_contains($serviceLower, $key)) {
                return $icon;
            }
        }
        
        return 'fas fa-tools';
    }

    /**
     * Contenu de fallback amélioré en cas d'échec de l'IA
     */
    private function generateEnhancedFallbackContent($serviceName, $shortDescription, $companyInfo)
    {
        return [
            'description' => $this->generateDefaultDescription($serviceName, $companyInfo),
            'short_description' => $shortDescription ?: 'Service professionnel de ' . $serviceName . ' par ' . $companyInfo['company_name'],
            'icon' => $this->getServiceIcon($serviceName),
            'meta_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'meta_description' => $shortDescription ?: 'Service professionnel de ' . $serviceName . ' à ' . $companyInfo['company_city'] . '. Devis gratuit et intervention rapide.',
            'og_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'og_description' => $shortDescription ?: 'Service professionnel de ' . $serviceName . ' à ' . $companyInfo['company_city'] . '. Devis gratuit et intervention rapide.',
            'twitter_title' => $serviceName . ' - ' . $companyInfo['company_name'],
            'twitter_description' => $shortDescription ?: 'Service professionnel de ' . $serviceName . ' à ' . $companyInfo['company_city'] . '. Devis gratuit et intervention rapide.',
            'meta_keywords' => strtolower($serviceName) . ', ' . $companyInfo['company_city'] . ', ' . $companyInfo['company_region'] . ', devis gratuit, professionnel, qualité'
        ];
    }

    /**
     * Ancien contenu de fallback (gardé pour compatibilité)
     */
    private function generateFallbackContent($serviceName, $shortDescription, $companyInfo)
    {
        return $this->generateEnhancedFallbackContent($serviceName, $shortDescription, $companyInfo);
    }

    /**
     * Récupérer les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return [
            'company_name' => setting('company_name', 'Notre Entreprise'),
            'company_city' => setting('company_city', ''),
            'company_region' => setting('company_region', ''),
            'company_phone' => setting('company_phone', ''),
            'company_email' => setting('company_email', ''),
            'company_address' => setting('company_address', ''),
        ];
    }

    /**
     * Gérer l'upload d'image
     */
    private function handleImageUpload($file, $type)
    {
        $filename = 'service_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        // Utiliser la même logique que le portfolio : public_path
        $uploadPath = public_path('uploads/services');
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                throw new \Exception("Failed to create upload directory: {$uploadPath}");
            }
        }
        
        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($uploadPath)) {
            throw new \Exception("Upload directory is not writable: {$uploadPath}");
        }
        
        $file->move($uploadPath, $filename);
        return 'uploads/services/' . $filename;
    }

    /**
     * Sauvegarder un service
     */
    private function saveService($service)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $services[] = $service;
        Setting::set('services', $services, 'json');
    }

    /**
     * Mettre à jour un service
     */
    private function updateService($id, $data)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        $found = false;
        foreach ($services as $index => $service) {
            if (isset($service['id']) && $service['id'] == $id) {
                $services[$index] = array_merge($service, $data);
                $found = true;
                \Log::info("Service updated: " . $service['name'] . " (ID: " . $service['id'] . ")");
                break;
            }
        }
        
        if (!$found) {
            \Log::warning("Service with ID {$id} not found for update");
        }
        
        Setting::set('services', $services, 'json');
    }

    /**
     * Supprimer un service
     */
    private function deleteService($id)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            $services = [];
        }
        
        // Filtrer les services pour supprimer celui avec l'ID correspondant
        $filteredServices = [];
        $found = false;
        foreach ($services as $service) {
            if (isset($service['id']) && $service['id'] == $id) {
                $found = true;
                \Log::info("Service found for deletion: " . $service['name'] . " (ID: " . $service['id'] . ")");
                // Ne pas ajouter ce service (le supprimer)
            } else {
                $filteredServices[] = $service;
            }
        }
        
        if (!$found) {
            \Log::warning("Service with ID {$id} not found for deletion");
        }
        
        // Sauvegarder la liste mise à jour
        Setting::set('services', $filteredServices, 'json');
        
        \Log::info("Service deleted with ID: {$id}. Remaining services: " . count($filteredServices));
    }

    /**
     * Récupérer l'image d'un service
     */
    private function getServiceImage($id, $type)
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('id', $id);
        return $service[$type] ?? null;
    }

    /**
     * Générer automatiquement les prestations avec icônes selon le type de service
     */
    private function generatePrestationsWithIcons($serviceName)
    {
        $serviceName = strtolower($serviceName);
        
        // Mapping des types de services vers leurs prestations spécifiques
        $prestationsMap = [
            'nettoyage' => [
                ['icon' => 'fas fa-hands', 'title' => 'Enlèvement manuel des mousses et débris'],
                ['icon' => 'fas fa-spray-can', 'title' => 'Nettoyage haute pression contrôlé'],
                ['icon' => 'fas fa-flask', 'title' => 'Application de traitement anti-mousse professionnel'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Traitement hydrofuge pour imperméabilisation'],
                ['icon' => 'fas fa-tools', 'title' => 'Inspection et réparation de tuiles endommagées'],
                ['icon' => 'fas fa-water', 'title' => 'Débouchage des gouttières'],
                ['icon' => 'fas fa-sun', 'title' => 'Protection durable contre les UV'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils d\'entretien personnalisé']
            ],
            'toiture' => [
                ['icon' => 'fas fa-hands', 'title' => 'Enlèvement manuel des mousses et débris'],
                ['icon' => 'fas fa-spray-can', 'title' => 'Nettoyage haute pression contrôlé'],
                ['icon' => 'fas fa-flask', 'title' => 'Application de traitement anti-mousse professionnel'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Traitement hydrofuge pour imperméabilisation'],
                ['icon' => 'fas fa-tools', 'title' => 'Inspection et réparation de tuiles endommagées'],
                ['icon' => 'fas fa-water', 'title' => 'Débouchage des gouttières'],
                ['icon' => 'fas fa-sun', 'title' => 'Protection durable contre les UV'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils d\'entretien personnalisé']
            ],
            'isolation' => [
                ['icon' => 'fas fa-home', 'title' => 'Diagnostic thermique complet'],
                ['icon' => 'fas fa-thermometer-half', 'title' => 'Mesure de l\'efficacité énergétique'],
                ['icon' => 'fas fa-layer-group', 'title' => 'Pose d\'isolants haute performance'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Étanchéité à l\'air'],
                ['icon' => 'fas fa-tools', 'title' => 'Rénovation des combles'],
                ['icon' => 'fas fa-leaf', 'title' => 'Isolants écologiques'],
                ['icon' => 'fas fa-chart-line', 'title' => 'Certification énergétique'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils d\'économie d\'énergie']
            ],
            'façade' => [
                ['icon' => 'fas fa-paint-brush', 'title' => 'Nettoyage et préparation des surfaces'],
                ['icon' => 'fas fa-spray-can', 'title' => 'Application de peinture haute qualité'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Traitement anti-humidité'],
                ['icon' => 'fas fa-tools', 'title' => 'Réparation des fissures'],
                ['icon' => 'fas fa-palette', 'title' => 'Choix des couleurs personnalisées'],
                ['icon' => 'fas fa-sun', 'title' => 'Protection contre les intempéries'],
                ['icon' => 'fas fa-check-circle', 'title' => 'Contrôle qualité final'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils d\'entretien']
            ],
            'réparation' => [
                ['icon' => 'fas fa-search', 'title' => 'Diagnostic des dommages'],
                ['icon' => 'fas fa-tools', 'title' => 'Réparation des tuiles cassées'],
                ['icon' => 'fas fa-hammer', 'title' => 'Remplacement des éléments défectueux'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Renforcement de la structure'],
                ['icon' => 'fas fa-water', 'title' => 'Étanchéité des fuites'],
                ['icon' => 'fas fa-check-circle', 'title' => 'Tests d\'étanchéité'],
                ['icon' => 'fas fa-chart-line', 'title' => 'Rapport de réparation'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils de prévention']
            ],
            'élagage' => [
                ['icon' => 'fas fa-tree', 'title' => 'Élagage précis pour assurer la santé de vos arbres'],
                ['icon' => 'fas fa-cut', 'title' => 'Étêtage professionnel pour contrôler la croissance'],
                ['icon' => 'fas fa-exclamation-triangle', 'title' => 'Abattage sécurisé des arbres menaçants'],
                ['icon' => 'fas fa-scissors', 'title' => 'Taille spécifique pour préserver l\'esthétique'],
                ['icon' => 'fas fa-truck', 'title' => 'Évacuation des déchets verts après intervention'],
                ['icon' => 'fas fa-leaf', 'title' => 'Conseils d\'entretien pour maintenir la santé'],
                ['icon' => 'fas fa-shield-alt', 'title' => 'Intervention sécurisée sur arbres dangereux'],
                ['icon' => 'fas fa-lightbulb', 'title' => 'Approche personnalisée pour vos besoins']
            ]
        ];
        
        // Déterminer le type de service basé sur les mots-clés
        $serviceType = 'nettoyage'; // Par défaut
        
        if (strpos($serviceName, 'élagage') !== false || strpos($serviceName, 'étêtage') !== false || strpos($serviceName, 'abattage') !== false || strpos($serviceName, 'arbres') !== false) {
            $serviceType = 'élagage';
        } elseif (strpos($serviceName, 'isolation') !== false || strpos($serviceName, 'isoler') !== false) {
            $serviceType = 'isolation';
        } elseif (strpos($serviceName, 'façade') !== false || strpos($serviceName, 'peinture') !== false) {
            $serviceType = 'façade';
        } elseif (strpos($serviceName, 'réparation') !== false || strpos($serviceName, 'réparer') !== false) {
            $serviceType = 'réparation';
        } elseif (strpos($serviceName, 'toiture') !== false || strpos($serviceName, 'nettoyage') !== false) {
            $serviceType = 'toiture';
        }
        
        return $prestationsMap[$serviceType] ?? $prestationsMap['nettoyage'];
    }

    /**
     * Générer le HTML des prestations avec icônes
     */
    private function generatePrestationsHtml($prestations)
    {
        $html = '<div class="prestations-section mt-8">';
        $html .= '<h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">';
        $html .= '<i class="fas fa-list-check text-blue-600 mr-3"></i>';
        $html .= 'Nos Prestations</h3>';
        $html .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        
        foreach ($prestations as $prestation) {
            $html .= '<div class="flex items-center p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500 hover:bg-blue-50 transition-colors">';
            $html .= '<div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white mr-4">';
            $html .= '<i class="' . $prestation['icon'] . '"></i>';
            $html .= '</div>';
            $html .= '<span class="font-medium text-gray-900">' . $prestation['title'] . '</span>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Vérifier si la description contient déjà des prestations
     */
    private function hasExistingPrestations($description)
    {
        // Mots-clés qui indiquent la présence de prestations
        $prestationKeywords = [
            'Nos Prestations',
            'prestations',
            'services inclus',
            'inclus dans',
            'comprend',
            'Diagnostic complet',
            'Enlèvement des',
            'Traitement anti-mousse',
            'Application d\'un hydrofuge',
            'Nettoyage des gouttières',
            'Vérification des tuiles',
            'Garantie de satisfaction'
        ];
        
        $description = strtolower($description);
        
        foreach ($prestationKeywords as $keyword) {
            if (strpos($description, strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Améliorer les prestations existantes en ajoutant des icônes
     */
    private function enhanceExistingPrestations($description, $serviceName)
    {
        // Mapping des prestations vers leurs icônes (plus flexible)
        $prestationIcons = [
            'Diagnostic complet' => 'fas fa-search',
            'Enlèvement des mousses' => 'fas fa-hands',
            'Enlèvement manuel' => 'fas fa-hands',
            'Traitement anti-mousse' => 'fas fa-flask',
            'Application d\'un hydrofuge' => 'fas fa-shield-alt',
            'Traitement hydrofuge' => 'fas fa-shield-alt',
            'Nettoyage des gouttières' => 'fas fa-water',
            'Débouchage des gouttières' => 'fas fa-water',
            'Vérification des tuiles' => 'fas fa-tools',
            'Inspection et réparation' => 'fas fa-tools',
            'Garantie de satisfaction' => 'fas fa-check-circle',
            'Devis gratuit' => 'fas fa-calculator',
            'Protection durable' => 'fas fa-sun',
            'Conseils d\'entretien' => 'fas fa-lightbulb'
        ];
        
        $enhancedDescription = $description;
        
        // Ajouter des icônes aux prestations existantes (plus flexible)
        foreach ($prestationIcons as $prestation => $icon) {
            // Recherche plus flexible pour différents formats
            $patterns = [
                '/\*\s*' . preg_quote($prestation, '/') . '/i',
                '/•\s*' . preg_quote($prestation, '/') . '/i',
                '/-\s*' . preg_quote($prestation, '/') . '/i',
                '/<li[^>]*>' . preg_quote($prestation, '/') . '/i'
            ];
            
            $replacement = '<i class="' . $icon . ' text-blue-600 mr-2"></i>' . $prestation;
            
            foreach ($patterns as $pattern) {
                $enhancedDescription = preg_replace($pattern, $replacement, $enhancedDescription);
            }
        }
        
        return $enhancedDescription;
    }

    /**
     * Nettoyer les services existants en supprimant les prestations automatiques dupliquées
     */
    public function cleanExistingServices()
    {
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        if (!is_array($services)) {
            return response()->json(['success' => false, 'message' => 'Aucun service trouvé']);
        }
        
        $cleanedServices = [];
        $cleanedCount = 0;
        
        foreach ($services as $service) {
            if (isset($service['description'])) {
                // Supprimer les sections de prestations automatiques ajoutées
                $cleanedDescription = $this->removeAutomaticPrestations($service['description']);
                
                if ($cleanedDescription !== $service['description']) {
                    $service['description'] = $cleanedDescription;
                    $cleanedCount++;
                }
            }
            
            $cleanedServices[] = $service;
        }
        
        // Sauvegarder les services nettoyés
        Setting::set('services', $cleanedServices, 'json');
        
        return response()->json([
            'success' => true, 
            'message' => "Services nettoyés avec succès. {$cleanedCount} services modifiés.",
            'cleaned_count' => $cleanedCount
        ]);
    }

    /**
     * Supprimer les prestations automatiques de la description
     */
    private function removeAutomaticPrestations($description)
    {
        // Patterns pour détecter et supprimer les prestations automatiques
        $patterns = [
            // Supprimer les sections de prestations automatiques
            '/<div class="prestations-section[^>]*>.*?<\/div>/s',
            '/<h3[^>]*>.*?Nos Prestations.*?<\/h3>/i',
            '/<div class="grid grid-cols-1 md:grid-cols-2 gap-4">.*?<\/div>/s',
            // Supprimer les prestations avec icônes automatiques
            '/<div class="flex items-center p-4 bg-gray-50[^>]*>.*?<\/div>/s',
        ];
        
        $cleanedDescription = $description;
        
        foreach ($patterns as $pattern) {
            $cleanedDescription = preg_replace($pattern, '', $cleanedDescription);
        }
        
        // Nettoyer les espaces multiples
        $cleanedDescription = preg_replace('/\s+/', ' ', $cleanedDescription);
        $cleanedDescription = trim($cleanedDescription);
        
        return $cleanedDescription;
    }
}
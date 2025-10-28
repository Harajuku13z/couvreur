<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdTemplate;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdTemplateController extends Controller
{
    /**
     * Afficher la liste des templates
     */
    public function index()
    {
        $templates = AdTemplate::withCount('ads')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.ads.templates.index', compact('templates'));
    }

    /**
     * Afficher un template spécifique
     */
    public function show(AdTemplate $template)
    {
        $template->load('ads.city');
        
        return view('admin.ads.templates.show', compact('template'));
    }

    /**
     * Créer un template à partir d'un service
     */
    public function createFromService(Request $request)
    {
        $request->validate([
            'service_slug' => 'required|string',
            'ai_prompt' => 'nullable|string|max:5000',
        ]);

        $serviceSlug = $request->input('service_slug');
        
        // Récupérer les services depuis les settings
        $servicesData = Setting::get('services', '[]');
        $services = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
        
        $service = collect($services)->firstWhere('slug', $serviceSlug);
        
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service non trouvé'
            ], 404);
        }

        try {
            // Générer le contenu via IA
            $aiContent = $this->generateTemplateContent($service, $request->input('ai_prompt'));
            
            // Créer le template
            $template = AdTemplate::create([
                'name' => $service['name'],
                'service_name' => $service['name'],
                'service_slug' => $service['slug'],
                'content_html' => $aiContent['description'],
                'short_description' => $aiContent['short_description'],
                'long_description' => $aiContent['long_description'],
                'icon' => $aiContent['icon'],
                'meta_title' => $aiContent['meta_title'],
                'meta_description' => $aiContent['meta_description'],
                'meta_keywords' => $aiContent['meta_keywords'],
                'og_title' => $aiContent['og_title'],
                'og_description' => $aiContent['og_description'],
                'twitter_title' => $aiContent['twitter_title'],
                'twitter_description' => $aiContent['twitter_description'],
                'ai_prompt_used' => $request->input('ai_prompt'),
                'ai_response_data' => $aiContent,
            ]);

            return response()->json([
                'success' => true,
                'template_id' => $template->id,
                'message' => 'Template créé avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur création template', [
                'service' => $service['name'],
                'error' => $e->getMessage()
            ]);

            // Essayer de créer un template avec du contenu de fallback
            try {
                $fallbackContent = $this->generateFallbackTemplateContent($service);
                
                $template = AdTemplate::create([
                    'name' => $service['name'],
                    'service_name' => $service['name'],
                    'service_slug' => $service['slug'],
                    'content_html' => $fallbackContent['description'],
                    'short_description' => $fallbackContent['short_description'],
                    'long_description' => $fallbackContent['long_description'],
                    'icon' => $fallbackContent['icon'],
                    'meta_title' => $fallbackContent['meta_title'],
                    'meta_description' => $fallbackContent['meta_description'],
                    'meta_keywords' => $fallbackContent['meta_keywords'],
                    'og_title' => $fallbackContent['og_title'],
                    'og_description' => $fallbackContent['og_description'],
                    'twitter_title' => $fallbackContent['twitter_title'],
                    'twitter_description' => $fallbackContent['twitter_description'],
                    'ai_prompt_used' => $request->input('ai_prompt'),
                    'ai_response_data' => ['fallback' => true, 'error' => $e->getMessage()],
                ]);

                return response()->json([
                    'success' => true,
                    'template_id' => $template->id,
                    'message' => 'Template créé avec du contenu de fallback (API indisponible)',
                    'warning' => 'L\'API OpenAI n\'était pas disponible. Le template a été créé avec du contenu par défaut.'
                ]);
                
            } catch (\Exception $fallbackError) {
                Log::error('Erreur création template fallback', [
                    'service' => $service['name'],
                    'error' => $fallbackError->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du template: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Générer des annonces à partir d'un template pour plusieurs villes
     */
    public function generateAdsFromTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:ad_templates,id',
            'city_ids' => 'required|array|min:1',
            'city_ids.*' => 'required|integer|exists:cities,id',
        ]);

        $template = AdTemplate::findOrFail($request->input('template_id'));
        $cityIds = $request->input('city_ids');
        $cities = City::whereIn('id', $cityIds)->get();

        $createdAds = 0;
        $skippedAds = 0;
        $errors = [];

        foreach ($cities as $city) {
            try {
                // Vérifier si une annonce existe déjà pour cette combinaison
                $existingAd = \App\Models\Ad::where('template_id', $template->id)
                    ->where('city_id', $city->id)
                    ->first();

                if ($existingAd) {
                    $skippedAds++;
                    continue;
                }

                // Obtenir le contenu et les métadonnées pour cette ville
                $contentForCity = $template->getContentForCity($city);
                $metaForCity = $template->getMetaForCity($city);

                // Créer l'annonce
                $ad = \App\Models\Ad::create([
                    'title' => $template->service_name . ' à ' . $city->name,
                    'keyword' => $template->service_name,
                    'city_id' => $city->id,
                    'template_id' => $template->id,
                    'slug' => Str::slug($template->service_name . '-' . $city->name),
                    'status' => 'published',
                    'published_at' => now(),
                    'meta_title' => $metaForCity['meta_title'],
                    'meta_description' => $metaForCity['meta_description'],
                    'meta_keywords' => $metaForCity['meta_keywords'],
                    'content_html' => $contentForCity,
                    'content_json' => json_encode([
                        'template_id' => $template->id,
                        'city' => $city->toArray(),
                        'generated_at' => now()->toISOString()
                    ])
                ]);

                $createdAds++;
                
                // Incrémenter le compteur d'utilisation du template
                $template->incrementUsage();

            } catch (\Exception $e) {
                $errors[] = [
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ];
                Log::error('Erreur création annonce depuis template', [
                    'template_id' => $template->id,
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'created' => $createdAds,
            'skipped' => $skippedAds,
            'errors' => $errors,
            'message' => "Génération terminée : {$createdAds} annonces créées, {$skippedAds} ignorées"
        ]);
    }

    /**
     * Générer le contenu du template via IA
     */
    private function generateTemplateContent($service, $aiPrompt = null)
    {
        $apiKey = setting('openai_api_key') ?: setting('chatgpt_api_key');
        
        if (!$apiKey) {
            throw new \Exception('Clé API OpenAI manquante');
        }

        // Construire le prompt pour le template (sans ville spécifique)
        $prompt = $this->buildTemplatePrompt($service['name'], $aiPrompt);
        
        $response = \Illuminate\Support\Facades\Http::timeout(120) // 2 minutes timeout
            ->retry(3, 2000) // 3 tentatives avec 2 secondes d'attente
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 3000, // Réduit pour éviter les timeouts
                'temperature' => 0.7
            ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur API OpenAI: ' . $response->status());
        }

        $data = $response->json();
        $aiContent = $data['choices'][0]['message']['content'] ?? '';
        
        // Valider et nettoyer le contenu
        return $this->validateAndCleanAIData($aiContent, $service['name']);
    }

    /**
     * Construire le prompt pour un template (sans ville spécifique)
     */
    private function buildTemplatePrompt($serviceName, $aiPrompt = null)
    {
        $basePrompt = "Crée un template d'annonce pour le service {$serviceName}.

SERVICE: {$serviceName}

GÉNÈRE UN JSON AVEC CES CHAMPS:

{
  \"description\": \"<div class='grid md:grid-cols-2 gap-8'><div class='space-y-6'><div class='space-y-4'><p class='text-lg leading-relaxed'>Service professionnel de {$serviceName} à [VILLE], une expertise reconnue dans [RÉGION].</p><p class='text-lg leading-relaxed'>Spécialistes en travaux de {$serviceName} pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p></div><div class='bg-blue-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Notre Engagement Qualité</h3><p class='leading-relaxed mb-3'>Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p><p class='leading-relaxed'>Chaque intervention de {$serviceName} est réalisée selon les normes professionnelles les plus strictes.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Nos Prestations {$serviceName}</h3><ul class='space-y-3'><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Diagnostic et évaluation</strong> - Analyse complète de vos besoins en {$serviceName}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Intervention d'urgence</strong> - Service rapide 24h/7j pour {$serviceName}</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Maintenance préventive</strong> - Entretien régulier pour éviter les problèmes</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Réparation spécialisée</strong> - Correction des dysfonctionnements</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Installation complète</strong> - Pose selon les normes en vigueur</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Rénovation totale</strong> - Remplacement intégral avec matériaux de qualité</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Conseil personnalisé</strong> - Recommandations adaptées à votre situation</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Suivi post-intervention</strong> - Accompagnement après travaux</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Formation utilisateur</strong> - Apprentissage des bonnes pratiques</span></li><li class='flex items-start'><i class='fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0'></i><span><strong>Garantie étendue</strong> - Protection supplémentaire sur nos interventions</span></li></ul><div class='bg-gray-50 p-6 rounded-lg mt-6'><h4 class='text-xl font-bold text-gray-900 mb-3'>FAQ</h4><div class='space-y-2'><p><strong>Q1: Combien coûte un service de {$serviceName} à [VILLE]?</strong></p><p>A: Le prix dépend de la complexité et de l'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p><p><strong>Q2: Quel est le délai d'intervention pour {$serviceName}?</strong></p><p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l'urgence de votre demande.</p><p><strong>Q3: Proposez-vous une garantie sur vos services de {$serviceName}?</strong></p><p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p></div></div></div><div class='space-y-6'><div class='bg-green-50 p-6 rounded-lg'><h3 class='text-xl font-bold text-gray-900 mb-3'>Pourquoi choisir ce service</h3><p class='leading-relaxed'>Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d'adapter nos services en conséquence.</p></div><h3 class='text-2xl font-bold text-gray-900 mb-4'>Notre Expertise Locale</h3><p class='leading-relaxed'>Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en {$serviceName}.</p><div class='bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Financement et aides</h4><p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de {$serviceName}.</p></div><div class='bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600'><h4 class='text-xl font-bold text-gray-900 mb-3'>Besoin d'un devis?</h4><p class='mb-4'>Contactez-nous pour un devis gratuit pour {$serviceName} à [VILLE].</p><a href='[FORM_URL]' class='inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300'>Demande de devis</a></div><div class='bg-gray-50 p-6 rounded-lg'><h4 class='text-lg font-bold text-gray-900 mb-3'>Informations Pratiques</h4><ul class='space-y-2 text-sm'><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Devis gratuit et sans engagement</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Intervention rapide sur [VILLE]</span></li><li class='flex items-center'><i class='fas fa-check text-green-600 mr-3 flex-shrink-0'></i><span>Garantie sur tous nos travaux</span></li></ul></div><div class='mt-8 pt-6 border-t border-gray-200'><div class='text-center'><h4 class='text-lg font-semibold text-gray-800 mb-4'>Partager ce service</h4><div class='flex justify-center items-center space-x-4'><a href='https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]' target='_blank' rel='noopener noreferrer' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-facebook-f text-lg'></i><span class='font-medium'>Facebook</span></a><a href='https://wa.me/?text=[TITRE] - [URL]' target='_blank' rel='noopener noreferrer' class='bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fab fa-whatsapp text-lg'></i><span class='font-medium'>WhatsApp</span></a><a href='mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]' class='bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1'><i class='fas fa-envelope text-lg'></i><span class='font-medium'>Email</span></a></div></div></div></div>\",
  \"short_description\": \"Service professionnel de {$serviceName} à [VILLE] - Devis gratuit et intervention rapide\",
  \"long_description\": \"Notre entreprise spécialisée en {$serviceName} intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.\",
  \"icon\": \"fas fa-tools\",
  \"meta_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"meta_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"og_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"og_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"twitter_title\": \"{$serviceName} à [VILLE] - Service professionnel\",
  \"twitter_description\": \"Service professionnel de {$serviceName} à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.\",
  \"meta_keywords\": \"{$serviceName}, [VILLE], [RÉGION], service professionnel, devis gratuit\"
}

IMPORTANT: 
- Réponds UNIQUEMENT avec le JSON ci-dessus
- Ne pas ajouter de texte avant ou après
- Ne pas modifier la structure
- Commence directement par { et termine par }
- Copie exactement le JSON fourni
- Utilise [VILLE], [RÉGION], [DÉPARTEMENT] comme placeholders pour les variables dynamiques";

        if ($aiPrompt) {
            $basePrompt .= "\n\nINSTRUCTIONS PERSONNALISÉES SUPPLÉMENTAIRES:\n" . $aiPrompt;
        }

        return $basePrompt;
    }

    /**
     * Valider et nettoyer les données IA
     */
    private function validateAndCleanAIData($aiContent, $serviceName)
    {
        try {
            // Nettoyer le contenu
            $cleanContent = $this->cleanHtmlContent($aiContent);
            
            // Extraire le JSON
            $jsonContent = $this->extractJsonFromContent($cleanContent);
            
            if (empty($jsonContent)) {
                throw new \Exception('Aucun JSON valide trouvé dans le contenu');
            }
            
            // Parser le JSON
            $aiData = json_decode($jsonContent, true);
            
            if (!$aiData) {
                // Tentative de correction
                $correctedContent = $this->attemptJsonCorrection($jsonContent);
                $aiData = json_decode($correctedContent, true);
                
                if (!$aiData) {
                    throw new \Exception('JSON invalide après correction');
                }
            }
            
            if (!isset($aiData['description'])) {
                throw new \Exception('Champ description manquant dans les données IA');
            }
            
            return $aiData;
            
        } catch (\Exception $e) {
            Log::error('Erreur validation données IA template', [
                'service' => $serviceName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Nettoyer le contenu HTML généré par l'IA
     */
    private function cleanHtmlContent($content)
    {
        // Supprimer les balises markdown
        $content = preg_replace('/```json\s*/', '', $content);
        $content = preg_replace('/```\s*$/', '', $content);
        $content = preg_replace('/```html\s*/', '', $content);
        
        // Nettoyer les caractères d'échappement
        $content = str_replace(['\"', '\\n', '\\t'], ['"', "\n", "\t"], $content);
        
        return trim($content);
    }

    /**
     * Extraire le JSON du contenu
     */
    private function extractJsonFromContent($content)
    {
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        
        if ($firstBrace === false || $lastBrace === false || $firstBrace >= $lastBrace) {
            return '';
        }
        
        $jsonContent = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        
        if (json_decode($jsonContent, true) !== null) {
            return $jsonContent;
        }
        
        return '';
    }

    /**
     * Tenter de corriger un JSON malformé
     */
    private function attemptJsonCorrection($content)
    {
        // Supprimer les caractères de contrôle
        $content = preg_replace('/[\x00-\x1F\x7F]/', '', $content);
        
        // Corriger les apostrophes non échappées
        $content = preg_replace_callback('/"([^"]*\'[^"]*)"/', function($matches) {
            $string = $matches[1];
            $string = str_replace("'", "\\'", $string);
            return '"' . $string . '"';
        }, $content);
        
        // Supprimer les virgules en trop
        $content = preg_replace('/,(\s*[}\]])/', '$1', $content);
        
        return $content;
    }

    /**
     * Générer un contenu de fallback pour un template
     */
    private function generateFallbackTemplateContent($service)
    {
        $serviceName = $service['name'];
        $serviceSlug = $service['slug'];
        
        // Contenu HTML de fallback avec la même structure que l'IA
        $contentHtml = '<div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-6">
                <div class="space-y-4">
                    <p class="text-lg leading-relaxed">Service professionnel de ' . $serviceName . ' à [VILLE], une expertise reconnue dans [RÉGION].</p>
                    <p class="text-lg leading-relaxed">Spécialistes en travaux de ' . $serviceName . ' pour une qualité supérieure. Nous maîtrisons les techniques modernes garantissant des résultats durables.</p>
                </div>
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notre Engagement Qualité</h3>
                    <p class="leading-relaxed mb-3">Nous garantissons la satisfaction totale de nos clients à [VILLE] et dans toute la région de [RÉGION].</p>
                    <p class="leading-relaxed">Chaque intervention de ' . $serviceName . ' est réalisée selon les normes professionnelles les plus strictes.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations ' . $serviceName . '</h3>
                <ul class="space-y-3">
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Diagnostic et évaluation</strong> - Analyse complète de vos besoins en ' . $serviceName . '</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Intervention d\'urgence</strong> - Service rapide 24h/7j pour ' . $serviceName . '</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Maintenance préventive</strong> - Entretien régulier pour éviter les problèmes</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Réparation spécialisée</strong> - Correction des dysfonctionnements</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Installation complète</strong> - Pose selon les normes en vigueur</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Rénovation totale</strong> - Remplacement intégral avec matériaux de qualité</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Conseil personnalisé</strong> - Recommandations adaptées à votre situation</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Suivi post-intervention</strong> - Accompagnement après travaux</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Formation utilisateur</strong> - Apprentissage des bonnes pratiques</span></li>
                    <li class="flex items-start"><i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i><span><strong>Garantie étendue</strong> - Protection supplémentaire sur nos interventions</span></li>
                </ul>
                <div class="bg-gray-50 p-6 rounded-lg mt-6">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ</h4>
                    <div class="space-y-2">
                        <p><strong>Q1: Combien coûte un service de ' . $serviceName . ' à [VILLE]?</strong></p>
                        <p>A: Le prix dépend de la complexité et de l\'ampleur des travaux. Nous proposons des devis gratuits et personnalisés.</p>
                        <p><strong>Q2: Quel est le délai d\'intervention pour ' . $serviceName . '?</strong></p>
                        <p>A: Nous nous engageons à intervenir rapidement, généralement sous 24-48h selon l\'urgence de votre demande.</p>
                        <p><strong>Q3: Proposez-vous une garantie sur vos services de ' . $serviceName . '?</strong></p>
                        <p>A: Oui, tous nos travaux sont garantis selon les normes professionnelles en vigueur.</p>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi choisir ce service</h3>
                    <p class="leading-relaxed">Notre expertise locale à [VILLE] nous permet de comprendre les spécificités de votre région et d\'adapter nos services en conséquence.</p>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Notre Expertise Locale</h3>
                <p class="leading-relaxed">Depuis plusieurs années, nous intervenons sur [VILLE] et sa région, développant une connaissance approfondie des besoins locaux en ' . $serviceName . '.</p>
                <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Financement et aides</h4>
                    <p>Nous vous accompagnons dans vos démarches pour bénéficier des aides financières disponibles pour vos travaux de ' . $serviceName . '.</p>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
                    <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un devis?</h4>
                    <p class="mb-4">Contactez-nous pour un devis gratuit pour ' . $serviceName . ' à [VILLE].</p>
                    <a href="[FORM_URL]" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Devis gratuit et sans engagement</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Intervention rapide sur [VILLE]</span></li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i><span>Garantie sur tous nos travaux</span></li>
                    </ul>
                </div>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Partager ce service</h4>
                        <div class="flex justify-center items-center space-x-4">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=[URL]&quote=[TITRE]" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-facebook-f text-lg"></i>
                                <span class="font-medium">Facebook</span>
                            </a>
                            <a href="https://wa.me/?text=[TITRE] - [URL]" target="_blank" rel="noopener noreferrer" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fab fa-whatsapp text-lg"></i>
                                <span class="font-medium">WhatsApp</span>
                            </a>
                            <a href="mailto:?subject=[TITRE]&body=Je vous partage ce service intéressant : [URL]" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-full transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i class="fas fa-envelope text-lg"></i>
                                <span class="font-medium">Email</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        return [
            'description' => $contentHtml,
            'short_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE] - Devis gratuit et intervention rapide',
            'long_description' => 'Notre entreprise spécialisée en ' . $serviceName . ' intervient sur [VILLE] et dans toute la région de [RÉGION]. Nous proposons des services complets incluant diagnostic, réparation, installation et maintenance. Notre équipe d\'experts maîtrise les techniques les plus modernes pour garantir des résultats durables et performants. Nous nous adaptons aux spécificités climatiques locales et respectons toutes les normes professionnelles en vigueur.',
            'icon' => 'fas fa-tools',
            'meta_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'meta_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'meta_keywords' => $serviceName . ', [VILLE], [RÉGION], service professionnel, devis gratuit',
            'og_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'og_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
            'twitter_title' => $serviceName . ' à [VILLE] - Service professionnel',
            'twitter_description' => 'Service professionnel de ' . $serviceName . ' à [VILLE]. Devis gratuit, intervention rapide, garantie sur tous nos travaux.',
        ];
    }

    /**
     * Obtenir la liste des villes pour la génération d'annonces
     */
    public function getCities()
    {
        $cities = City::select('id', 'name', 'region', 'department')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }

    /**
     * Basculer le statut d'un template
     */
    public function toggleStatus(Request $request, AdTemplate $template)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $template->update([
            'is_active' => $request->input('is_active')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Statut du template mis à jour'
        ]);
    }
}
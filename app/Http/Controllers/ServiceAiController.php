<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ServiceAiController extends Controller
{
    public function form()
    {
        return view('admin.services.ai');
    }

    public function test(Request $request)
    {
        $model = $request->get('model', 'llama-3.1-8b-instant');
        $ok = false; 
        $status = null; 
        $body = null; 
        $error = null;

        try {
            if (!env('GROQ_API_KEY')) {
                return back()->with('error', 'GROQ_API_KEY manquant dans .env');
            }
            
            $resp = Http::withToken(env('GROQ_API_KEY'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model ?: 'llama-3.1-8b-instant',
                    'messages' => [['role' => 'user', 'content' => 'Réponds: OK']],
                    'max_tokens' => 5
                ]);
                
            $status = $resp->status();
            $ok = $resp->ok();
            $body = substr($resp->body(), 0, 200);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $msg = $ok ? 'Connexion IA: OK (' . $status . ')' : 'Connexion IA: ECHEC' . ($status ? ' (' . $status . ')' : '');
        return back()->with($ok ? 'status' : 'error', $msg . ($body ? ' Réponse: ' . $body : '') . ($error ? ' Erreur: ' . $error : ''));
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'service_names' => 'required|string',
            'category' => 'nullable|string|max:120',
            'language' => 'nullable|string|max:10',
            'custom_prompt' => 'nullable|string|max:2000',
            'provider' => 'nullable|in:groq',
            'model' => 'nullable|string|max:255',
        ]);

        $serviceNames = collect(preg_split("/\r?\n/", trim($data['service_names'])))->filter();
        $category = $data['category'] ?? 'Services de Couverture';
        $language = $data['language'] ?? 'fr';
        $customPrompt = trim($data['custom_prompt'] ?? '');
        $model = $data['model'] ?? 'llama-3.1-8b-instant';

        $created = 0;
        $existingServices = json_decode(Setting::get('services', '[]'), true) ?: [];

        foreach ($serviceNames as $serviceName) {
            $slug = Str::slug($serviceName);
            
            // Vérifier si le service existe déjà
            $exists = collect($existingServices)->contains(function ($service) use ($slug) {
                return isset($service['slug']) && $service['slug'] === $slug;
            });
            
            if ($exists) {
                continue; // skip existing
            }

            $companyName = setting('company_name', 'Notre Entreprise');
            $companyCity = setting('company_city', '');
            $companyDept = setting('company_region', '');
            
            $system = "Tu es un expert en rédaction web pour services de rénovation/couverture en France. Tu génères UNIQUEMENT du JSON valide, sans texte avant ou après.";
            
            $template = '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">[description_courte]</p>
      <p class="text-lg leading-relaxed">[description_longue]</p>
    </div>
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">[titre_garantie]</h3>
      <p class="leading-relaxed mb-3">[texte_garantie]</p>
    </div>
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations [service]</h3>
    <ul class="space-y-3">[prestations_liste]</ul>
    <div class="bg-gray-50 p-6 rounded-lg mt-6">
      <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ [service]</h4>
      <div class="space-y-2">[faq_liste]</div>
    </div>
  </div>
  <div class="space-y-6">
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi choisir [service] avec [entreprise]</h3>
      <p class="leading-relaxed">[pourquoi_choisir]</p>
    </div>
    <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Financement et aides</h4>
      <p>[financement_aides]</p>
    </div>
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit pour [service].</p>
      <a href="[FORM_URL]" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">[infos_pratiques_liste]</ul>
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
            
            $user = ($customPrompt ? ($customPrompt . "\n\n") : '') . "Service: {$serviceName}
Entreprise: {$companyName}
Ville: {$companyCity}
Département: {$companyDept}
Langue: {$language}

Crée un JSON avec les données suivantes pour remplir ce template HTML:

{
  \"description_courte\": \"Description courte du {$serviceName} incluant {$companyCity} et le département (150-200 caractères)\",
  \"description_longue\": \"Description longue et détaillée du {$serviceName} avec bénéfices, techniques, matériaux (minimum 300 caractères)\",
  \"titre_garantie\": \"Titre de l'engagement ou garantie\",
  \"texte_garantie\": \"Description des garanties, normes de qualité, chantier rendu propre, etc.\",
  \"prestations\": [
    {\"titre\": \"Prestation 1 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 2 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 3 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 4 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 5 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 6 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 7 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 8 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 9 technique\", \"description\": \"Description en une phrase\"},
    {\"titre\": \"Prestation 10 technique\", \"description\": \"Description en une phrase\"}
  ],
  \"faq\": [
    {\"question\": \"Question 1\", \"reponse\": \"Réponse détaillée\"},
    {\"question\": \"Question 2\", \"reponse\": \"Réponse détaillée\"},
    {\"question\": \"Question 3\", \"reponse\": \"Réponse détaillée\"},
    {\"question\": \"Question 4\", \"reponse\": \"Réponse détaillée\"}
  ],
  \"pourquoi_choisir\": \"Avantages de travailler avec nous pour ce service et parler de notre expertise\",
  \"financement_aides\": \"Parler des aides disponibles en France selon le service\",
  \"infos_pratiques\": [
    \"Info pratique 1\",
    \"Info pratique 2\",
    \"Info pratique 3\",
    \"Info pratique 4\",
    \"Info pratique 5\"
  ],
  \"short_description\": \"Description courte SEO 120-140 caractères\",
  \"meta_title\": \"Titre SEO 50-60 caractères\",
  \"meta_description\": \"Description SEO 150-160 caractères\",
  \"meta_keywords\": \"service, service professionnel, expert service, entreprise service, artisan service, service certifié, rénovation, réparation, installation, intervention rapide, devis gratuit, qualité garantie, techniques modernes, normes professionnelles\"
}

IMPORTANT:
- Les prestations DOIVENT être techniques et spécifiques au {$serviceName}
- Utilise le vocabulaire professionnel du métier
- Le champ meta_keywords DOIT contenir AU MINIMUM 15-20 mots-clés pertinents, incluant:
  * Le nom du service et ses variations
  * Des termes techniques spécifiques au métier
  * Des mots-clés d'action (rénovation, réparation, installation, entretien, etc.)
  * Des termes de qualité (professionnel, expert, certifié, qualifié, etc.)
  * Des termes commerciaux (devis gratuit, intervention rapide, garantie, etc.)
  * Des matériaux ou techniques spécifiques au service
- Réponds UNIQUEMENT avec le JSON valide, sans texte avant ou après.";

            try {
                if (empty(env('GROQ_API_KEY'))) {
                    return back()->with('error', "Veuillez définir GROQ_API_KEY dans le fichier .env");
                }
                
                // Pour Groq on-demand: limiter max_tokens pour laisser de la place aux tokens d'entrée
                // Estimation: ~1 token = 4 caractères, limite TPM = 6000
                $estimatedInputTokens = (int)((strlen($system) + strlen($user)) / 4);
                $maxTokens = max(1000, min(3000, 5500 - $estimatedInputTokens)); // Laisser marge de sécurité
                
                $response = Http::withToken(env('GROQ_API_KEY'))
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $model ?: 'llama-3.1-8b-instant',
                        'messages' => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user', 'content' => $user],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => $maxTokens,
                    ]);
                    
                $content = $response->ok() ? data_get($response->json(), 'choices.0.message.content') : null;

                if (!$content) {
                    continue;
                }

                // Parser le JSON de la réponse IA
                $jsonData = $this->parseJsonResponse($content);
                
                if (!$jsonData) {
                    \Log::warning('Impossible de parser le JSON pour le service: ' . $serviceName);
                    continue;
                }

                // Remplir le template HTML avec les données JSON
                $htmlContent = $this->fillTemplate($template, $jsonData, $serviceName, $companyName);

                // Générer un slug unique
                $baseSlug = Str::slug($serviceName);
                $slug = $baseSlug;
                $counter = 1;
                
                while (collect($existingServices)->contains(function ($service) use ($slug) {
                    return isset($service['slug']) && $service['slug'] === $slug;
                })) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                // Créer le service
                $newService = [
                    'id' => time() . rand(1000, 9999),
                    'name' => $serviceName,
                    'slug' => $slug,
                    'short_description' => $jsonData['short_description'] ?? Str::limit(strip_tags($htmlContent), 200),
                    'description' => $htmlContent,
                    'category' => $category,
                    'is_visible' => true,
                    'is_featured' => false,
                    'is_menu' => false,
                    'featured_image' => '',
                    'gallery' => [],
                    'pricing' => [
                        'starting_from' => '',
                        'unit' => 'm²',
                        'note' => 'Devis gratuit sur mesure'
                    ],
                    'features' => [],
                    'benefits' => [],
                    'process' => [],
                    'faq' => $jsonData['faq'] ?? [],
                    'meta_title' => $jsonData['meta_title'] ?? $serviceName . ' - ' . $companyName,
                    'meta_description' => $jsonData['meta_description'] ?? Str::limit(strip_tags($htmlContent), 155),
                    'meta_keywords' => $jsonData['meta_keywords'] ?? 'devis gratuit, couvreur professionnel, ' . Str::slug($serviceName, ', ') . ', artisan couvreur, travaux de couverture, entreprise de couverture, spécialiste toiture, rénovation toiture, réparation urgence, devis personnalisé, garantie travaux, matériaux de qualité, expertise technique, intervention 24h/24, couvreur qualifié, travaux de rénovation, isolation toiture, étanchéité toiture',
                    'og_title' => $jsonData['meta_title'] ?? $serviceName . ' - ' . $companyName,
                    'og_description' => $jsonData['meta_description'] ?? Str::limit(strip_tags($htmlContent), 155),
                    'og_image' => null, // null pour utiliser l'image par défaut du SeoHelper
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ];

                $existingServices[] = $newService;
                $created++;

            } catch (\Throwable $e) {
                // Ignore and continue to next service
                \Log::error('Erreur génération service IA: ' . $e->getMessage());
            }
        }

        // Sauvegarder tous les services
        if ($created > 0) {
            Setting::set('services', json_encode($existingServices), 'json', 'services');
            Setting::clearCache();
        }

        return redirect()->route('admin.services.index')->with('status', "$created service(s) généré(s) par IA.");
    }

    /**
     * Parser le JSON de la réponse IA
     */
    private function parseJsonResponse($content)
    {
        // Nettoyer le contenu pour extraire uniquement le JSON
        $content = trim($content);
        
        // Chercher le début du JSON
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        
        if ($jsonStart === false || $jsonEnd === false) {
            \Log::warning('JSON non trouvé dans la réponse IA', ['content_preview' => substr($content, 0, 500)]);
            return null;
        }
        
        $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
        $data = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Erreur parsing JSON', [
                'error' => json_last_error_msg(),
                'json_preview' => substr($jsonString, 0, 500)
            ]);
            return null;
        }
        
        return $data;
    }

    /**
     * Remplir le template HTML avec les données JSON
     */
    private function fillTemplate($template, $data, $serviceName, $companyName)
    {
        $siteUrl = setting('site_url', config('app.url'));
        if (!str_starts_with($siteUrl, 'http')) {
            $siteUrl = 'https://' . $siteUrl;
        }
        $serviceUrl = $siteUrl . '/services/' . Str::slug($serviceName);
        $formUrl = $siteUrl . '/form/propertyType';
        
        // Générer la liste des prestations
        $prestationsHtml = '';
        if (isset($data['prestations']) && is_array($data['prestations'])) {
            foreach ($data['prestations'] as $prestation) {
                $titre = htmlspecialchars($prestation['titre'] ?? '', ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($prestation['description'] ?? '', ENT_QUOTES, 'UTF-8');
                $prestationsHtml .= '<li class="flex items-start">' .
                    '<i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>' .
                    '<span><strong>' . $titre . '</strong> - ' . $description . '</span>' .
                    '</li>';
            }
        }
        
        // Générer la liste FAQ
        $faqHtml = '';
        if (isset($data['faq']) && is_array($data['faq'])) {
            foreach ($data['faq'] as $faq) {
                $question = htmlspecialchars($faq['question'] ?? '', ENT_QUOTES, 'UTF-8');
                $reponse = htmlspecialchars($faq['reponse'] ?? '', ENT_QUOTES, 'UTF-8');
                $faqHtml .= '<p><strong>' . $question . '</strong></p>' .
                    '<p>' . $reponse . '</p>';
            }
        }
        
        // Générer la liste des infos pratiques
        $infosPratiquesHtml = '';
        if (isset($data['infos_pratiques']) && is_array($data['infos_pratiques'])) {
            foreach ($data['infos_pratiques'] as $info) {
                $infoEscaped = htmlspecialchars($info, ENT_QUOTES, 'UTF-8');
                $infosPratiquesHtml .= '<li class="flex items-center">' .
                    '<i class="fas fa-check text-green-600 mr-3 flex-shrink-0"></i>' .
                    '<span>' . $infoEscaped . '</span>' .
                    '</li>';
            }
        }
        
        // Remplacer tous les placeholders dans le template
        $html = str_replace('[description_courte]', htmlspecialchars($data['description_courte'] ?? '', ENT_QUOTES, 'UTF-8'), $template);
        $html = str_replace('[description_longue]', htmlspecialchars($data['description_longue'] ?? '', ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[titre_garantie]', htmlspecialchars($data['titre_garantie'] ?? 'Garantie de satisfaction', ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[texte_garantie]', htmlspecialchars($data['texte_garantie'] ?? '', ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[prestations_liste]', $prestationsHtml, $html);
        $html = str_replace('[faq_liste]', $faqHtml, $html);
        $html = str_replace('[service]', htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[entreprise]', htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[pourquoi_choisir]', htmlspecialchars($data['pourquoi_choisir'] ?? '', ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[financement_aides]', htmlspecialchars($data['financement_aides'] ?? '', ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[infos_pratiques_liste]', $infosPratiquesHtml, $html);
        $html = str_replace('[URL]', htmlspecialchars($serviceUrl, ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[TITRE]', htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('[FORM_URL]', htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8'), $html);
        // Remplacer les liens hardcodés vers le formulaire (fallback pour anciens templates)
        $html = str_replace('/devis-gratuit', htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8'), $html);
        $html = str_replace('href="/form', 'href="' . htmlspecialchars($formUrl, ENT_QUOTES, 'UTF-8'), $html);
        
        return $html;
    }
}

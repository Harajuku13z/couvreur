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
            'force_regenerate' => 'nullable|boolean',
        ]);

        $serviceNames = collect(preg_split("/\r?\n/", trim($data['service_names'])))->filter();
        $category = $data['category'] ?? 'Services de Couverture';
        $language = $data['language'] ?? 'fr';
        $customPrompt = trim($data['custom_prompt'] ?? '');
        $model = $data['model'] ?? 'llama-3.1-8b-instant';

        $created = 0;
        $updated = 0;
        $existingServices = json_decode(Setting::get('services', '[]'), true) ?: [];
        $forceRegenerate = $request->has('force_regenerate') && $request->input('force_regenerate') == '1';

        foreach ($serviceNames as $serviceName) {
            $slug = Str::slug($serviceName);
            
            // Vérifier si le service existe déjà
            $existingServiceIndex = null;
            $existingService = null;
            foreach ($existingServices as $index => $service) {
                if (isset($service['slug']) && $service['slug'] === $slug) {
                    $existingServiceIndex = $index;
                    $existingService = $service;
                    break;
                }
            }
            
            if ($existingService && !$forceRegenerate) {
                \Log::info('Service déjà existant, skip (utilisez force_regenerate=1 pour régénérer)', [
                    'service' => $serviceName,
                    'slug' => $slug
                ]);
                continue; // skip existing
            }

            \Log::info('Génération IA pour service', [
                'service' => $serviceName,
                'slug' => $slug,
                'force_regenerate' => $forceRegenerate && $existingService ? true : false
            ]);

            $companyName = setting('company_name', 'Notre Entreprise');
            $companyCity = setting('company_city', '');
            $companyDept = setting('company_region', '');
            
            // Récupérer les informations pratiques depuis les settings
            $companyAddress = setting('company_address', '');
            $companyPhone = setting('company_phone', '');
            $companyEmail = setting('company_email', '');
            $companyHours = setting('company_hours', '');
            
            // Message système fort pour forcer la personnalisation
            $system = "Tu es un expert technique spécialisé en {$serviceName} avec une connaissance approfondie du domaine de la rénovation et couverture en France. 

CRITIQUE ABSOLUE: 
- Chaque contenu DOIT être UNIQUE, TECHNIQUE et SPÉCIFIQUE à {$serviceName}
- INTERDIT d'utiliser des prestations génériques comme 'Nettoyage', 'Réparation', 'Remplacement'
- INTERDIT de copier du contenu générique ou répétitif
- INTERDIT d'utiliser des phrases vides comme 'Service professionnel' sans détails
- Tu DOIS générer du contenu vraiment personnalisé avec des détails techniques concrets
- Adapte TOUT spécifiquement au service {$serviceName} avec le vocabulaire professionnel exact du métier

Tu génères UNIQUEMENT du JSON valide, sans texte avant ou après.";
            
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
      <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ du [service]</h4>
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
            
            // Construire la liste des infos pratiques dynamiquement pour le prompt JSON
            $infosPratiquesList = [];
            if ($companyAddress) {
                $infosPratiquesList[] = "Adresse : {$companyAddress}";
            }
            if ($companyPhone) {
                $infosPratiquesList[] = "Téléphone : {$companyPhone}";
            }
            if ($companyEmail) {
                $infosPratiquesList[] = "Email : {$companyEmail}";
            }
            if ($companyHours) {
                $infosPratiquesList[] = "Horaires de travail : {$companyHours}";
            }
            if ($companyName) {
                $infosPratiquesList[] = "Société : {$companyName}";
            }
            $infosPratiquesPrompt = "Informations Pratiques à utiliser EXACTEMENT:\n" . implode("\n", $infosPratiquesList);
            
            // Construire le tableau JSON pour infos_pratiques
            $infosPratiquesJson = [];
            if ($companyAddress) {
                $infosPratiquesJson[] = '"Adresse : ' . addslashes($companyAddress) . '"';
            }
            if ($companyPhone) {
                $infosPratiquesJson[] = '"Téléphone : ' . addslashes($companyPhone) . '"';
            }
            if ($companyEmail) {
                $infosPratiquesJson[] = '"Email : ' . addslashes($companyEmail) . '"';
            }
            if ($companyHours) {
                $infosPratiquesJson[] = '"Horaires de travail : ' . addslashes($companyHours) . '"';
            }
            $infosPratiquesJson[] = '"Société : ' . addslashes($companyName) . '"';
            $infosPratiquesJsonString = implode(",\n    ", $infosPratiquesJson);
            
            // Générer des exemples de prestations selon le type de service
            $serviceLower = strtolower($serviceName);
            $prestationsExamples = "";
            if (strpos($serviceLower, 'toiture') !== false || strpos($serviceLower, 'couverture') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Réparation de tuiles cassées, Remplacement de faîtage, Traitement hydrofuge, Réfection de zinguerie, Réparation de solin, Remplacement d'ardoises, Réparation de charpente, Isolation des combles, Réfection de gouttières, Traitement anti-moisissure";
            } elseif (strpos($serviceLower, 'isolation') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Isolation des combles perdus, Isolation sous rampants, Isolation des murs par l'intérieur, Isolation des murs par l'extérieur, Isolation des sols, Traitement des ponts thermiques, Pose de VMC double flux, Calorifugeage";
            } elseif (strpos($serviceLower, 'façade') !== false || strpos($serviceLower, 'ravalement') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Ravalement de façade, Application d'enduit monocouche, Peinture façade, Nettoyage haute pression, Réfection de parement, Réparation de fissures, Traitement anti-humidité, Isolation thermique par l'extérieur";
            } else {
                $prestationsExamples = "Génère 10 prestations techniques spécifiques au {$serviceName} avec le vocabulaire professionnel exact du métier.";
            }
            
            $user = ($customPrompt ? ($customPrompt . "\n\n") : '') . "Service: {$serviceName}
Entreprise: {$companyName}
Ville: {$companyCity}
Département: {$companyDept}
Langue: {$language}

{$infosPratiquesPrompt}

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - NE PAS COPIER LES EXEMPLES ⚠️⚠️⚠️
Les valeurs JSON ci-dessous sont des EXEMPLES/INSTRUCTIONS. TU DOIS générer du VRAI contenu, PAS copier ces exemples !

Génère un JSON avec cette structure et remplis chaque champ avec du CONTENU RÉEL, TECHNIQUE et PROFESSIONNEL spécifique à {$serviceName} :

{
  \"description_courte\": \"[Génère une description courte professionnelle de {$serviceName} à {$companyCity} dans le département {$companyDept}. 150-200 caractères, avec bénéfices concrets et spécifiques à ce service. TEXTE BRUT SEULEMENT, PAS DE HTML, PAS DE LIENS]\",
  \"description_longue\": \"[Génère une description longue et détaillée du {$serviceName}. Intègre {$companyCity} et {$companyDept}. Parle des techniques spécifiques utilisées, matériaux concrets, bénéfices énergétiques, durabilité, qualité. 400-600 mots minimum. SOIS TECHNIQUE et SPÉCIFIQUE. TEXTE BRUT SEULEMENT, PAS DE HTML, PAS DE LIENS, PAS DE BALISES]\",
  \"titre_garantie\": \"[Génère un titre d'engagement/garantie spécifique au {$serviceName}]\",
  \"texte_garantie\": \"[Génère une description détaillée des garanties pour {$serviceName}. Mentionne: garantie décennale, chantier propre, respect normes, matériaux qualité, satisfaction garantie. SOIS SPÉCIFIQUE au service. TEXTE BRUT SEULEMENT, PAS DE HTML]\",
  \"prestations\": [
    {\"titre\": \"[Génère prestation technique 1 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 2 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 3 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 4 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 5 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 6 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 7 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 8 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 9 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"},
    {\"titre\": \"[Génère prestation technique 10 RÉELLE et spécifique à {$serviceName}]\", \"description\": \"[Génère description technique détaillée avec vocabulaire professionnel]\"}
  ],
  \"faq\": [
    {\"question\": \"[Génère question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Génère réponse détaillée et professionnelle]\"},
    {\"question\": \"[Génère question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Génère réponse détaillée et professionnelle]\"},
    {\"question\": \"[Génère question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Génère réponse détaillée et professionnelle]\"},
    {\"question\": \"[Génère question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Génère réponse détaillée et professionnelle]\"}
  ],
  \"pourquoi_choisir\": \"[Génère un texte détaillant pourquoi choisir {$companyName} pour {$serviceName} à {$companyCity}. Mentionne expertise locale, qualité, réactivité, garanties, savoir-faire, certifications. SOIS SPÉCIFIQUE et CONCRET. TEXTE BRUT SEULEMENT, PAS DE HTML]\",
  \"financement_aides\": \"[Génère un texte sur les aides disponibles: MaPrimeRénov, CEE, éco-PTZ, TVA réduite, etc. Adapte selon {$serviceName}. TEXTE BRUT SEULEMENT, PAS DE HTML]\",
  \"infos_pratiques\": [
    {$infosPratiquesJsonString}
  ],
  \"short_description\": \"[Génère description courte SEO 120-140 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_title\": \"[Génère titre SEO optimisé 50-60 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_description\": \"[Génère description SEO 150-160 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_keywords\": \"[Génère 15-20 mots-clés pertinents incluant: {$serviceName}, {$serviceName} {$companyCity}, {$serviceName} {$companyDept}, expert {$serviceName}, {$serviceName} professionnel, entreprise {$serviceName}, artisan {$serviceName}, {$serviceName} certifié, rénovation, réparation, installation, intervention rapide, devis gratuit, qualité garantie, techniques modernes, matériaux spécifiques au service]\"
}

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - FORMAT JSON ⚠️⚠️⚠️:
- TU DOIS RÉPONDRE UNIQUEMENT AVEC UN JSON VALIDE
- COMMENCE DIRECTEMENT PAR { (accolade ouvrante)
- TERMINE DIRECTEMENT PAR } (accolade fermante)
- PAS de texte avant le JSON
- PAS de texte après le JSON
- PAS de ```json ou ``` autour du JSON
- PAS de commentaires ou explications
- JUSTE le JSON brut

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU ⚠️⚠️⚠️:
- ⚠️ CRITIQUE: Le champ \"prestations\" DOIT contenir EXACTEMENT 10 prestations. PAS moins, PAS plus. Chaque prestation doit avoir un \"titre\" et une \"description\".
- ⚠️ INTERDIT ABSOLU de copier les exemples entre [crochets]. Les valeurs entre [crochets] sont des INSTRUCTIONS, PAS du contenu à copier. Tu DOIS générer du VRAI contenu professionnel qui remplace complètement ces instructions.
- ⚠️ INTERDIT d'utiliser des prestations génériques comme 'Nettoyage', 'Réparation', 'Remplacement' sans précision technique
- ⚠️ Les prestations DOIVENT être TECHNIQUES et SPÉCIFIQUES au {$serviceName}. Exemples inspirants: {$prestationsExamples}
- ⚠️ INTERDIT ABSOLU de générer du HTML, des liens, des balises, du markdown ou du code dans les champs texte
- ⚠️ INTERDIT ABSOLU de générer des URLs, liens href, ou boutons dans les descriptions
- ⚠️ Tous les champs texte doivent contenir UNIQUEMENT du texte brut, sans HTML ni liens
- Utilise le vocabulaire professionnel EXACT du métier de {$serviceName}
- Le champ meta_keywords DOIT contenir AU MINIMUM 15-20 mots-clés pertinents, incluant:
  * Le nom du service et ses variations géographiques
  * Des termes techniques spécifiques au métier
  * Des mots-clés d'action (rénovation, réparation, installation, entretien, etc.)
  * Des termes de qualité (professionnel, expert, certifié, qualifié, etc.)
  * Des termes commerciaux (devis gratuit, intervention rapide, garantie, etc.)
  * Des matériaux ou techniques spécifiques au service
- Pour infos_pratiques, utilise EXACTEMENT les informations fournies ci-dessus
- VÉRIFIE avant d'envoyer: 
  * Le tableau \"prestations\" contient exactement 10 éléments avec contenu réel
  * Toutes les descriptions sont spécifiques et techniques
  * Aucun contenu générique ou copié
  * AUCUN HTML, liens, ou balises dans les champs texte";

            try {
                // Utiliser AiService qui gère automatiquement ChatGPT et Groq avec fallback
                // Calculer max_tokens dynamiquement pour respecter la limite TPM Groq (6000)
                $estimatedInputTokens = (int)((strlen($system) + strlen($user)) / 4);
                $maxTokens = min(4000, max(2000, 5500 - $estimatedInputTokens));
                
                \Log::info('Appel AiService pour génération service', [
                    'service' => $serviceName,
                    'estimated_input_tokens' => $estimatedInputTokens,
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.9
                ]);
                
                $result = \App\Services\AiService::callAI($user, $system, [
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.9, // Température élevée pour créativité et personnalisation
                    'timeout' => 120
                ]);
                
                if (!$result || !isset($result['content'])) {
                    \Log::error('Échec génération service via AiService', [
                        'service' => $serviceName,
                        'result' => $result
                    ]);
                    throw new \Exception('Erreur API IA: Impossible de générer le contenu. Vérifiez vos clés API ChatGPT ou Groq.');
                }
                
                $content = $result['content'];
                $provider = $result['provider'] ?? 'unknown';
                
                \Log::info('Réponse IA reçue pour service', [
                    'service' => $serviceName,
                    'provider' => $provider,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 300)
                ]);

                if (!$content || empty($content)) {
                    \Log::error('Pas de contenu retourné par l\'IA pour le service', [
                        'service' => $serviceName,
                        'provider' => $provider
                    ]);
                    continue;
                }

                \Log::info('Contenu IA reçu pour service', [
                    'service' => $serviceName,
                    'content_length' => strlen($content),
                    'content_preview' => substr($content, 0, 200)
                ]);

                // Parser le JSON de la réponse IA
                $jsonData = $this->parseJsonResponse($content);
                
                if (!$jsonData) {
                    \Log::warning('Impossible de parser le JSON pour le service', [
                        'service' => $serviceName,
                        'content_preview' => substr($content, 0, 500)
                    ]);
                    continue;
                }
                
                \Log::info('JSON parsé avec succès pour service', [
                    'service' => $serviceName,
                    'keys' => array_keys($jsonData),
                    'prestations_count' => count($jsonData['prestations'] ?? [])
                ]);

                // Vérifier que les prestations sont présentes et complètes
                if (!isset($jsonData['prestations']) || !is_array($jsonData['prestations'])) {
                    \Log::warning('Prestations manquantes ou invalides pour le service: ' . $serviceName);
                    $jsonData['prestations'] = [];
                }
                
                // Valider et logger le nombre de prestations
                $prestationsCount = count($jsonData['prestations']);
                if ($prestationsCount < 10) {
                    \Log::warning('Nombre insuffisant de prestations pour le service', [
                        'service' => $serviceName,
                        'count' => $prestationsCount,
                        'expected' => 10
                    ]);
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

                // Si le service existe déjà et qu'on force la régénération, le remplacer
                if ($existingService && $forceRegenerate && $existingServiceIndex !== null) {
                    $existingServices[$existingServiceIndex] = $newService;
                    $updated++;
                    \Log::info('Service régénéré', [
                        'service' => $serviceName,
                        'slug' => $slug
                    ]);
                } else {
                $existingServices[] = $newService;
                $created++;
                    \Log::info('Nouveau service créé', [
                        'service' => $serviceName,
                        'slug' => $slug
                    ]);
                }

            } catch (\Throwable $e) {
                // Ignore and continue to next service
                \Log::error('Erreur génération service IA: ' . $e->getMessage());
            }
        }

        // Sauvegarder tous les services
        if ($created > 0 || $updated > 0) {
            Setting::set('services', json_encode($existingServices), 'json', 'services');
            Setting::clearCache();
        }

        $message = '';
        if ($created > 0) {
            $message .= "$created service(s) créé(s)";
        }
        if ($updated > 0) {
            $message .= ($message ? ' et ' : '') . "$updated service(s) régénéré(s)";
        }
        if ($created > 0 || $updated > 0) {
            $message .= ' par IA.';
        } else {
            $message = 'Aucun service généré. Les services existent peut-être déjà. Utilisez force_regenerate=1 pour les régénérer.';
        }

        return redirect()->route('admin.services.index')->with('status', $message);
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
        $formUrl = $siteUrl . '/devis-gratuit';
        
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
        // Remplacer [FORM_URL] par /devis-gratuit (route relative)
        $html = str_replace('[FORM_URL]', '/devis-gratuit', $html);
        
        // Nettoyer et remplacer TOUS les liens devis pour pointer vers /devis-gratuit
        // Remplacer les liens hardcodés vers le formulaire (fallback pour anciens templates)
        $html = str_replace('href="/form/propertyType"', 'href="/devis-gratuit"', $html);
        $html = str_replace('href="/form"', 'href="/devis-gratuit"', $html);
        $html = preg_replace('/href="https?:\/\/[^"]*form[^"]*"/i', 'href="/devis-gratuit"', $html);
        
        // Remplacer tous les liens contenant "devis" qui pointent vers des domaines externes
        $html = preg_replace_callback('/href="(https?:\/\/[^"]*devis[^"]*)"/i', function($matches) {
            return 'href="/devis-gratuit"';
        }, $html);
        
        // Remplacer les liens relatifs contenant "devis" ou "form"
        $html = preg_replace('/href="[^"]*\/(?:form|devis|quote|estimation)[^"]*"/i', 'href="/devis-gratuit"', $html);
        
        // Nettoyer le HTML généré par l'IA qui pourrait contenir du HTML supplémentaire dans les descriptions
        // Extraire et nettoyer les descriptions pour enlever tout HTML indésirable
        $patterns = [
            // Supprimer les balises HTML dans les descriptions (sauf celles qu'on a générées)
            '/(?<!<li[^>]*>)(?<!<\/i>)(?<!<\/span>)(?<!<\/p>)(?<!<\/strong>)(?<!<\/h[1-6]>)(?<!<\/div>)(?<!<\/ul>)<a\s+[^>]*href[^>]*>(.*?)<\/a>/i' => '$1',
            // Supprimer les balises script et style si présentes
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi' => '',
            '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi' => '',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }
        
        return $html;
    }
}

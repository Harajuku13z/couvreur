<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Services\AiService;

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
            'force_regenerate' => 'nullable|boolean',
        ]);

        $serviceNames = collect(preg_split("/\r?\n/", trim($data['service_names'])))->filter();
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
                Log::info('Service déjà existant, skip', ['service' => $serviceName, 'slug' => $slug]);
                continue;
            }

            try {
                $companyInfo = $this->getCompanyInfo();
                $shortDescription = '';
                
                // Générer le contenu via IA (même approche que AdTemplateController)
                $aiContent = $this->generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo);
                
                if (isset($aiContent['error']) && $aiContent['error']) {
                    throw new \Exception($aiContent['error_message'] ?? 'Erreur lors de la génération par l\'IA');
                }
                
                // Créer ou mettre à jour le service
                $newService = [
                    'id' => $existingService['id'] ?? uniqid(),
                    'name' => $serviceName,
                    'slug' => $slug,
                    'short_description' => $aiContent['short_description'] ?? '',
                    'description' => $aiContent['description'] ?? '',
                    'icon' => $aiContent['icon'] ?? 'fas fa-tools',
                    'meta_title' => $aiContent['meta_title'] ?? '',
                    'meta_description' => $aiContent['meta_description'] ?? '',
                    'meta_keywords' => $aiContent['meta_keywords'] ?? '',
                    'og_title' => $aiContent['og_title'] ?? $aiContent['meta_title'] ?? '',
                    'og_description' => $aiContent['og_description'] ?? $aiContent['meta_description'] ?? '',
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString(),
                ];

                if ($existingService && $forceRegenerate && $existingServiceIndex !== null) {
                    $existingServices[$existingServiceIndex] = $newService;
                    $updated++;
                } else {
                    $existingServices[] = $newService;
                    $created++;
                }
                
            } catch (\Throwable $e) {
                Log::error('Erreur génération service IA: ' . $e->getMessage(), [
                    'service' => $serviceName,
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            }
        }

        if ($created > 0 || $updated > 0) {
            Setting::set('services', json_encode($existingServices), 'json', 'services');
            Setting::clearCache();
        }

        $message = ($created > 0 ? "$created service(s) créé(s)" : '') . 
                   ($updated > 0 ? ($created > 0 ? ' et ' : '') . "$updated service(s) régénéré(s)" : '') . 
                   ($created > 0 || $updated > 0 ? ' par IA.' : 'Aucun service généré.');
        
        return redirect()->route('admin.services.index')->with('status', $message);
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
        ];
    }

    /**
     * Générer un contenu complet de service via IA (inspiré de AdTemplateController::generateCompleteTemplateContent)
     */
    private function generateCompleteServiceContent($serviceName, $shortDescription, $companyInfo)
    {
        try {
            $companyName = $companyInfo['company_name'] ?? setting('company_name', 'Notre Entreprise');
            $companyCity = $companyInfo['company_city'] ?? setting('company_city', '');
            $companyDept = $companyInfo['company_region'] ?? setting('company_region', '');
            
            // Récupérer les informations pratiques depuis les settings
            $companyAddress = setting('company_address', '');
            $companyPhone = setting('company_phone', '');
            $companyEmail = setting('company_email', '');
            $companyHours = setting('company_hours', '');
            
            // Template HTML exact (même que AdTemplateController mais sans [VILLE] et [DÉPARTEMENT])
            $template = '<div class="grid md:grid-cols-2 gap-8">
  <div class="space-y-6">
    <div class="space-y-4">
      <p class="text-lg leading-relaxed">[description_courte]</p>
      <p class="text-lg leading-relaxed">[description_longue]</p>
    </div>
    <!-- Champ 2 : Engagement / garanties -->
    <div class="bg-blue-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">[titre_garantie]</h3>
      <p class="leading-relaxed mb-3">[texte_garantie]</p>
    </div>
    <!-- Champ 3 : Prestations / services il en faut 10 -->
    <h3 class="text-2xl font-bold text-gray-900 mb-4">Nos Prestations [service]</h3>
    <ul class="space-y-3">
[prestations_liste]
    </ul>
    <!-- Champ 8 : FAQ 4 question et reponse -->
    <div class="bg-gray-50 p-6 rounded-lg mt-6">
      <h4 class="text-xl font-bold text-gray-900 mb-3">FAQ du [service]</h4>
      <div class="space-y-2">
[faq_liste]
      </div>
    </div>
  </div>
  <!-- Colonne droite : Informations complémentaires -->
  <div class="space-y-6">
    <!-- Champ 4 : Pourquoi choisir ce service -->
    <div class="bg-green-50 p-6 rounded-lg">
      <h3 class="text-xl font-bold text-gray-900 mb-3">Pourquoi choisir [service] avec [entreprise]</h3>
      <p class="leading-relaxed">[pourquoi_choisir]</p>
    </div>
    <!-- Champ 9 : Financement / aides disponibles -->
    <div class="bg-yellow-50 p-6 rounded-lg border-l-4 border-yellow-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Financement et aides</h4>
      <p>[financement_aides]</p>
    </div>
    <!-- Champ 6 : CTA - demande de devis -->
    <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg border-l-4 border-blue-600">
      <h4 class="text-xl font-bold text-gray-900 mb-3">Besoin d\'un devis ?</h4>
      <p class="mb-4">Contactez-nous pour un devis gratuit pour [service].</p>
      <a href="/devis-gratuit" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg transition-all duration-300">Demande de devis</a>
    </div>
    <!-- Champ 7 : Informations pratiques -->
    <div class="bg-gray-50 p-6 rounded-lg">
      <h4 class="text-lg font-bold text-gray-900 mb-3">Informations Pratiques</h4>
      <ul class="space-y-2 text-sm">
[infos_pratiques_liste]
      </ul>
    </div>
    <!-- Champ 10 : Partage social -->
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
            
            // Prompt simplifié pour générer un JSON structuré (inspiré de AdTemplateController)
            $systemMessage = "Tu es un expert en rédaction web pour services de rénovation/couverture en France. Tu génères UNIQUEMENT du JSON valide. PAS de texte avant ou après le JSON. PAS de markdown. PAS de code blocks. JUSTE le JSON brut.

⚠️ CRITIQUE : Les valeurs entre [crochets] dans les instructions sont des EXEMPLES/INSTRUCTIONS à suivre, PAS du contenu à copier littéralement. Tu DOIS générer du VRAI contenu professionnel et spécifique, en remplaçant complètement ces instructions par du contenu réel.";
            
            // Construire les infos pratiques pour le prompt
            $infosPratiquesPrompt = "Informations pratiques à utiliser EXACTEMENT (ne pas inventer):\n";
            $infosPratiquesJson = [];
            if ($companyAddress) {
                $infosPratiquesPrompt .= "- Adresse : {$companyAddress}\n";
                $infosPratiquesJson[] = '"Adresse : ' . addslashes($companyAddress) . '"';
            }
            if ($companyPhone) {
                $infosPratiquesPrompt .= "- Téléphone : {$companyPhone}\n";
                $infosPratiquesJson[] = '"Téléphone : ' . addslashes($companyPhone) . '"';
            }
            if ($companyEmail) {
                $infosPratiquesPrompt .= "- Email : {$companyEmail}\n";
                $infosPratiquesJson[] = '"Email : ' . addslashes($companyEmail) . '"';
            }
            if ($companyHours) {
                $infosPratiquesPrompt .= "- Horaires de travail : {$companyHours}\n";
                $infosPratiquesJson[] = '"Horaires de travail : ' . addslashes($companyHours) . '"';
            }
            if ($companyName) {
                $infosPratiquesPrompt .= "- Société : {$companyName}\n";
                $infosPratiquesJson[] = '"Société : ' . addslashes($companyName) . '"';
            }
            $infosPratiquesJsonString = implode(",\n    ", $infosPratiquesJson);
            
            // Déterminer les types de prestations selon le service
            $prestationsExamples = '';
            $serviceLower = mb_strtolower($serviceName);
            if (strpos($serviceLower, 'toiture') !== false || strpos($serviceLower, 'couverture') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Réparation de tuiles cassées, Remplacement de faîtage, Traitement hydrofuge, Réfection de zinguerie, Réparation de solin, Remplacement d'ardoises, Réparation de charpente, Isolation des combles, Réfection de gouttières, Traitement anti-moisissure";
            } elseif (strpos($serviceLower, 'isolation') !== false || strpos($serviceLower, 'isol') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Isolation des combles perdus, Isolation sous rampants, Isolation des murs par l'intérieur, Isolation des murs par l'extérieur, Isolation des sols, Traitement des ponts thermiques, Pose de VMC double flux, Calorifugeage";
            } elseif (strpos($serviceLower, 'façade') !== false || strpos($serviceLower, 'ravalement') !== false) {
                $prestationsExamples = "Exemples pour {$serviceName}: Ravalement de façade, Application d'enduit monocouche, Peinture façade, Nettoyage haute pression, Réfection de parement, Réparation de fissures, Traitement anti-humidité, Isolation thermique par l'extérieur";
            } else {
                $prestationsExamples = "Génère 10 prestations techniques spécifiques au {$serviceName} avec le vocabulaire professionnel du métier.";
            }
            
            $userPrompt = "Service: {$serviceName}
Entreprise: {$companyName}
Ville: {$companyCity}
Département: {$companyDept}

{$infosPratiquesPrompt}

🚫🚫🚫 INTERDICTIONS ABSOLUES 🚫🚫🚫:
- INTERDIT de créer un champ \"description\" contenant du HTML
- INTERDIT de générer du HTML dans n'importe quel champ JSON
- INTERDIT de retourner du texte formaté ou du markdown
- TU DOIS générer uniquement un JSON avec les champs EXACTS demandés ci-dessous

⚠️⚠️⚠️ ATTENTION CRITIQUE ⚠️⚠️⚠️:
Le template HTML est DÉJÀ créé sur le serveur. Tu dois UNIQUEMENT fournir les DONNÉES en JSON.
Les champs JSON doivent être: description_courte, description_longue, prestations, faq, etc.
PAS de champ \"description\" avec du HTML !

⚠️⚠️⚠️ STRUCTURE JSON OBLIGATOIRE - PAS DE CHAMP \"description\" ⚠️⚠️⚠️:
Les champs JSON DOIVENT être EXACTEMENT ceux listés ci-dessous.
INTERDIT de créer un champ \"description\".
INTERDIT de créer un champ avec du HTML.

Génère un JSON avec cette structure EXACTE (remplis chaque champ avec du CONTENU RÉEL et PROFESSIONNEL) :

{
  \"description_courte\": \"[Génère ici une description courte professionnelle de {$serviceName} à {$companyCity} dans le département {$companyDept}. 150-200 caractères, mentionnant les bénéfices principaux. TEXTE BRUT SEULEMENT]\",
  \"description_longue\": \"[Génère ici une description longue et détaillée du {$serviceName}. Intègre naturellement {$companyCity} et {$companyDept}. Parle des techniques utilisées, matériaux, bénéfices énergétiques, durabilité, qualité. 400-600 mots. TEXTE BRUT SEULEMENT]\",
  \"titre_garantie\": \"Garantie de satisfaction\",
  \"texte_garantie\": \"[Génère un texte détaillant les garanties offertes: garantie décennale, assurance, normes respectées, chantier propre, suivi post-intervention, etc.]\",
  \"prestations\": [
    {\"titre\": \"[Prestation technique 1 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 2 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 3 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 4 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 5 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 6 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 7 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 8 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 9 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"},
    {\"titre\": \"[Prestation technique 10 spécifique au {$serviceName}]\", \"description\": \"[Description détaillée technique et professionnelle]\"}
  ],
  \"faq\": [
    {\"question\": \"[Question fréquente réelle sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée et professionnelle]\"},
    {\"question\": \"[Question fréquente réelle sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée et professionnelle]\"},
    {\"question\": \"[Question fréquente réelle sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée et professionnelle]\"},
    {\"question\": \"[Question fréquente réelle sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée et professionnelle]\"}
  ],
  \"pourquoi_choisir\": \"[Génère un texte détaillant pourquoi choisir {$companyName} pour {$serviceName} à {$companyCity} dans le département {$companyDept}. Mentionne expertise, qualité, réactivité, garanties, savoir-faire local, etc.]\",
  \"financement_aides\": \"[Génère un texte sur les aides disponibles: MaPrimeRénov, CEE, éco-PTZ, TVA réduite, etc. Adapte selon le service.]\",
  \"infos_pratiques\": [
    {$infosPratiquesJsonString}
  ],
  \"meta_title\": \"{$serviceName} à {$companyCity} - Expert professionnel | Devis gratuit\",
  \"meta_description\": \"Service professionnel de {$serviceName} à {$companyCity} et dans le département {$companyDept}. Devis gratuit, intervention rapide.\",
  \"meta_keywords\": \"{$serviceName}, {$serviceName} {$companyCity}, {$serviceName} {$companyDept}, expert {$serviceName}, {$serviceName} professionnel, entreprise {$serviceName}, artisan {$serviceName}, {$serviceName} certifié, rénovation, réparation, installation, intervention rapide, devis gratuit, qualité garantie, satisfaction garantie, matériaux performants, techniques modernes, normes professionnelles, intervention {$companyCity}, service {$companyCity}, professionnel {$companyCity}\",
  \"og_title\": \"{$serviceName} à {$companyCity} - Expert professionnel\",
  \"og_description\": \"Service professionnel de {$serviceName} à {$companyCity} dans le département {$companyDept}. Devis gratuit.\",
  \"twitter_title\": \"{$serviceName} à {$companyCity} - Expert professionnel\",
  \"twitter_description\": \"Service professionnel de {$serviceName} à {$companyCity} dans le département {$companyDept}. Devis gratuit.\"
}

🚫🚫🚫 CHAMPS INTERDITS 🚫🚫🚫:
- INTERDIT ABSOLU de créer un champ \"description\" (même vide)
- INTERDIT ABSOLU de créer un champ avec du HTML ou des balises
- INTERDIT ABSOLU de créer d'autres champs que ceux listés ci-dessus

✅✅✅ CHAMPS OBLIGATOIRES (utilise EXACTEMENT ces noms) ✅✅✅:
- description_courte (TEXTE BRUT)
- description_longue (TEXTE BRUT)
- titre_garantie (TEXTE BRUT)
- texte_garantie (TEXTE BRUT)
- prestations (TABLEAU de 10 objets)
- faq (TABLEAU de 4 objets)
- pourquoi_choisir (TEXTE BRUT)
- financement_aides (TEXTE BRUT)
- infos_pratiques (TABLEAU de strings)
- meta_title, meta_description, meta_keywords, og_title, og_description, twitter_title, twitter_description (TEXTE BRUT)

RÈGLES STRICTES:
1. Réponds UNIQUEMENT avec le JSON (commence par { et finit par })
2. PAS de texte avant le {
3. PAS de texte après le }
4. PAS de ```json ou ``` autour
5. ⚠️ CRITIQUE: Les valeurs entre [crochets] ci-dessus sont des INSTRUCTIONS, PAS du contenu à copier. Tu DOIS générer du VRAI contenu professionnel qui remplace ces instructions.
6. Les prestations DOIVENT être techniques et spécifiques au {$serviceName}. {$prestationsExamples}
7. Utilise le vocabulaire professionnel du métier de {$serviceName}
8. Pour infos_pratiques, utilise EXACTEMENT les informations fournies ci-dessus (ne pas inventer)
9. Les guillemets dans les valeurs doivent être échappés avec \\
10. Assure-toi que le JSON est valide (vérifie les virgules, les accolades)
11. ⚠️ MOTS-CLÉS: Le champ meta_keywords DOIT contenir AU MINIMUM 15-20 mots-clés pertinents et variés, séparés par des virgules.
12. ⚠️ INTERDIT ABSOLU de copier les exemples entre [crochets]. Génère du contenu professionnel réel.
13. ⚠️ CRITIQUE: Le champ \"prestations\" DOIT contenir EXACTEMENT 10 prestations. PAS moins, PAS plus.
14. ⚠️⚠️⚠️ INTERDIT ABSOLU de créer un champ \"description\". Les champs sont description_courte et description_longue, PAS description.";
            
            Log::info('Appel à AiService::callAI pour service', [
                'service_name' => $serviceName,
                'prompt_length' => strlen($userPrompt),
                'system_message_length' => strlen($systemMessage)
            ]);
            
            // Calculer max_tokens dynamiquement pour respecter la limite TPM Groq (6000)
            $totalMessageLength = strlen($systemMessage) + strlen($userPrompt);
            $estimatedInputTokens = (int)($totalMessageLength / 4);
            $maxTokens = min(4000, max(2000, 5500 - $estimatedInputTokens));
            
            Log::info('Calcul tokens pour génération service', [
                'estimated_input_tokens' => $estimatedInputTokens,
                'adjusted_max_tokens' => $maxTokens
            ]);
            
            // Utiliser AiService directement (gère automatiquement ChatGPT et Groq)
            $result = AiService::callAI($userPrompt, $systemMessage, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
                'timeout' => 120
            ]);
            
            if (!$result || !isset($result['content'])) {
                Log::error('Échec génération service via AiService', [
                    'service_name' => $serviceName,
                    'result' => $result
                ]);
                return [
                    'error' => true,
                    'error_message' => 'Erreur API IA: Impossible de générer le contenu. Vérifiez vos clés API ChatGPT ou Groq.'
                ];
            }
            
            Log::info('Réponse IA reçue pour service', [
                'service_name' => $serviceName,
                'provider' => $result['provider'] ?? 'unknown',
                'content_length' => strlen($result['content']),
                'content_preview' => substr($result['content'], 0, 300)
            ]);
            
            // Vérifier si l'IA a retourné du HTML ou un champ "description" avec HTML
            $content = $result['content'];
            if (preg_match('/"description"\s*:\s*["\']<[^>]+>/', $content) || preg_match('/"description"\s*:\s*["\']\\n\s*<div/', $content)) {
                Log::error('L\'IA a généré un champ "description" avec du HTML', [
                    'service_name' => $serviceName,
                    'content_preview' => substr($content, 0, 500)
                ]);
                return [
                    'error' => true,
                    'error_message' => 'L\'IA a généré un champ "description" contenant du HTML au lieu des champs JSON attendus (description_courte, description_longue, prestations). Veuillez réessayer.'
                ];
            }
            
            // Parser le JSON de la réponse IA (même méthode que AdTemplateController)
            $jsonData = $this->parseJsonResponseForService($result['content']);
            
            if (!$jsonData) {
                $content = $result['content'];
                $jsonStart = strpos($content, '{');
                $isTruncated = false;
                if ($jsonStart !== false) {
                    $potentialJson = substr($content, $jsonStart);
                    $openBraces = substr_count($potentialJson, '{');
                    $closeBraces = substr_count($potentialJson, '}');
                    $isTruncated = $openBraces > $closeBraces;
                }
                
                Log::error('Impossible de parser le JSON pour le service', [
                    'service_name' => $serviceName,
                    'provider' => $result['provider'] ?? 'unknown',
                    'content_length' => strlen($content),
                    'content_full' => $content,
                    'content_preview' => substr($content, 0, 1000),
                    'content_end' => substr($content, -500),
                    'json_error' => json_last_error_msg(),
                    'is_truncated' => $isTruncated,
                    'open_braces' => $openBraces ?? 0,
                    'close_braces' => $closeBraces ?? 0
                ]);
                
                $errorMessage = 'Erreur: L\'IA n\'a pas retourné un JSON valide. ';
                if ($isTruncated) {
                    $errorMessage .= 'La réponse semble tronquée (accolades non fermées). Essayez d\'augmenter max_tokens ou réduisez la taille du prompt. ';
                }
                $errorMessage .= 'Contenu reçu: ' . substr($content, 0, 200) . '... Consultez les logs pour plus de détails.';
                
                return [
                    'error' => true,
                    'error_message' => $errorMessage
                ];
            }
            
            // Vérifier qu'il n'y a PAS de champ "description" (c'est une erreur - le champ est interdit)
            if (isset($jsonData['description'])) {
                Log::error('Le JSON contient un champ "description" INTERDIT - FORMAT INCORRECT', [
                    'service_name' => $serviceName,
                    'json_keys' => array_keys($jsonData),
                    'description_preview' => substr($jsonData['description'], 0, 200),
                    'description_length' => strlen($jsonData['description'] ?? ''),
                    'contains_html' => preg_match('/<[^>]+>/', $jsonData['description'] ?? '')
                ]);
                // Ignorer le champ "description" et continuer si description_courte et description_longue existent
                if (isset($jsonData['description_courte']) && isset($jsonData['description_longue'])) {
                    unset($jsonData['description']); // Supprimer le champ interdit
                    Log::info('Champ "description" supprimé, utilisation de description_courte et description_longue', [
                        'service_name' => $serviceName
                    ]);
                } else {
                    return [
                        'error' => true,
                        'error_message' => 'Le JSON généré contient un champ "description" (interdit). Le système attend les champs: description_courte, description_longue, prestations, faq, etc. Veuillez réessayer.'
                    ];
                }
            }
            
            // Vérifier que les champs attendus sont présents
            if (!isset($jsonData['description_courte']) || !isset($jsonData['prestations'])) {
                Log::error('Champs JSON attendus manquants', [
                    'service_name' => $serviceName,
                    'json_keys' => array_keys($jsonData),
                    'has_description_courte' => isset($jsonData['description_courte']),
                    'has_prestations' => isset($jsonData['prestations']),
                    'has_description_instead' => isset($jsonData['description'])
                ]);
                return [
                    'error' => true,
                    'error_message' => 'Le JSON généré ne contient pas les champs requis (description_courte, prestations). Champs trouvés: ' . implode(', ', array_keys($jsonData))
                ];
            }
            
            // Vérifier que les prestations sont présentes (10 exactement)
            if (!isset($jsonData['prestations']) || !is_array($jsonData['prestations']) || count($jsonData['prestations']) < 10) {
                Log::error('Nombre insuffisant de prestations', [
                    'service_name' => $serviceName,
                    'prestations_count' => count($jsonData['prestations'] ?? []),
                    'expected' => 10
                ]);
                return [
                    'error' => true,
                    'error_message' => 'L\'IA n\'a généré que ' . count($jsonData['prestations'] ?? []) . ' prestation(s) au lieu de 10. Veuillez réessayer.'
                ];
            }
            
            // S'assurer qu'il n'y a plus de champ "description" dans jsonData (nettoyage final)
            if (isset($jsonData['description'])) {
                unset($jsonData['description']);
                Log::info('Nettoyage final: champ "description" supprimé du JSON avant remplissage template');
            }
            
            // Remplir le template HTML avec les données JSON
            $htmlContent = $this->fillTemplateForService($template, $jsonData, $serviceName, $companyName, $companyInfo);
            
            if (!$htmlContent || empty(trim($htmlContent))) {
                Log::error('Template HTML vide après remplissage', [
                    'service_name' => $serviceName,
                    'template_length' => strlen($template),
                    'json_keys' => array_keys($jsonData)
                ]);
                return [
                    'error' => true,
                    'error_message' => 'Erreur: Impossible de remplir le template HTML.'
                ];
            }
            
            // Vérifier que le HTML généré contient bien les éléments attendus
            if (strpos($htmlContent, '[description_courte]') !== false || 
                strpos($htmlContent, '[description_longue]') !== false ||
                strpos($htmlContent, '[prestations_liste]') !== false) {
                Log::error('Template HTML contient encore des placeholders non remplacés', [
                    'service_name' => $serviceName,
                    'html_preview' => substr($htmlContent, 0, 500)
                ]);
                return [
                    'error' => true,
                    'error_message' => 'Erreur: Le template HTML contient encore des placeholders non remplacés. Vérifiez que tous les champs JSON sont présents.'
                ];
            }
            
            // Retourner les données formatées (description = HTML généré depuis template, PAS depuis JSON)
            return [
                'description' => $htmlContent, // HTML généré depuis le template, PAS depuis JSON
                'short_description' => $jsonData['description_courte'] ?? $shortDescription,
                'icon' => 'fas fa-tools',
                'meta_title' => $jsonData['meta_title'] ?? ($serviceName . ' à ' . $companyCity . ' - Expert professionnel | Devis gratuit'),
                'meta_description' => $jsonData['meta_description'] ?? ('Service professionnel de ' . $serviceName . ' à ' . $companyCity . '. Devis gratuit, intervention rapide.'),
                'meta_keywords' => $jsonData['meta_keywords'] ?? '',
                'og_title' => $jsonData['og_title'] ?? ($serviceName . ' à ' . $companyCity . ' - Expert professionnel'),
                'og_description' => $jsonData['og_description'] ?? ('Service professionnel de ' . $serviceName . ' à ' . $companyCity . '. Devis gratuit.')
            ];
        } catch (\Exception $e) {
            Log::error('Erreur génération service: ' . $e->getMessage(), [
                'service_name' => $serviceName,
                'error' => $e->getTraceAsString()
            ]);
            return [
                'error' => true,
                'error_message' => 'Erreur lors de la génération par l\'IA: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Parser le JSON de la réponse IA (même méthode que AdTemplateController::parseJsonResponseForTemplate)
     */
    private function parseJsonResponseForService($content)
    {
        $content = trim($content);
        
        Log::info('Tentative de parsing JSON pour service', [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 300),
            'has_braces' => strpos($content, '{') !== false,
            'last_chars' => substr($content, -50)
        ]);
        
        // Essayer plusieurs patterns pour extraire le JSON
        $jsonPatterns = [
            '/```json\s*(\{[\s\S]*?\})\s*```/s',
            '/```\s*(\{[\s\S]*?\})\s*```/s',
            '/\{[\s\S]*\"description_courte\"[\s\S]*\}/s',
            '/\{[\s\S]*\}/s',
        ];
        
        foreach ($jsonPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $jsonString = $matches[1] ?? $matches[0];
                $jsonString = trim($jsonString);
                
                $data = json_decode($jsonString, true);
                
                if ($data && is_array($data) && !empty($data)) {
                    Log::info('JSON parsé avec succès pour service', ['keys' => array_keys($data)]);
                    return $data;
                } else {
                    $jsonError = json_last_error();
                    if ($jsonError === JSON_ERROR_SYNTAX && !str_ends_with($jsonString, '}')) {
                        $openCount = substr_count($jsonString, '{');
                        $closeCount = substr_count($jsonString, '}');
                        $missingBraces = $openCount - $closeCount;
                        
                        if ($missingBraces > 0) {
                            $attemptedFix = $jsonString . str_repeat('}', $missingBraces);
                            $fixedData = json_decode($attemptedFix, true);
                            if ($fixedData && is_array($fixedData)) {
                                Log::info('JSON réparé en fermant les accolades manquantes', ['missing_braces' => $missingBraces]);
                                return $fixedData;
                            }
                        }
                    }
                }
            }
        }
        
        // Si aucun pattern ne fonctionne, essayer de trouver le JSON manuellement
        $jsonStart = strpos($content, '{');
        $jsonEnd = strrpos($content, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($jsonString, true);
            
            if ($data && is_array($data) && !empty($data)) {
                Log::info('JSON parsé avec extraction manuelle');
                return $data;
            }
        }
        
        // Dernière tentative : décoder directement
        $data = json_decode($content, true);
        if ($data && is_array($data) && !empty($data)) {
            Log::info('JSON parsé directement');
            return $data;
        }
        
        return null;
    }
    
    /**
     * Remplir le template HTML avec les données JSON (même méthode que AdTemplateController::fillTemplateForAds)
     */
    private function fillTemplateForService($template, $data, $serviceName, $companyName, $companyInfo)
    {
        $siteUrl = setting('site_url', config('app.url'));
        if (!str_starts_with($siteUrl, 'http')) {
            $siteUrl = 'https://' . $siteUrl;
        }
        $serviceUrl = $siteUrl . '/services/' . Str::slug($serviceName);
        
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
        
        return $html;
    }
}

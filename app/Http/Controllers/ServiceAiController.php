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
            
            // Message système fort pour forcer JSON PUR - INTERDIT HTML
            $system = "Tu es un expert technique spécialisé en {$serviceName}. 

🚫🚫🚫 INTERDICTIONS ABSOLUES - CRITIQUE 🚫🚫🚫:
- INTERDIT ABSOLU de retourner du HTML ou des balises HTML comme <div>, <p>, <li>, etc.
- INTERDIT ABSOLU de retourner du texte formaté avec markdown (**, ##, etc.)
- INTERDIT ABSOLU de retourner du texte descriptif comme \"Voici le contenu...\" ou \"Description:\"
- INTERDIT ABSOLU de générer le template HTML complet
- INTERDIT d'utiliser des prestations génériques
- INTERDIT d'utiliser des phrases répétitives

✅✅✅ OBLIGATION ABSOLUE - UNIQUEMENT JSON ✅✅✅:
- Tu DOIS répondre UNIQUEMENT avec un JSON valide
- Le JSON DOIT commencer PAR { (accolade ouvrante) - RIEN avant
- Le JSON DOIT finir PAR } (accolade fermante) - RIEN après
- PAS de texte, PAS de HTML, PAS de markdown
- JUSTE le JSON brut avec les champs demandés
- Le template HTML est DÉJÀ créé, tu dois UNIQUEMENT fournir les DONNÉES en JSON
- Chaque contenu DOIT être UNIQUE et TECHNIQUE spécifique à {$serviceName}";
            
            // Template HTML fixe - l'IA génère uniquement le JSON qui sera injecté ici
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

⚠️⚠️⚠️ ATTENTION CRITIQUE ⚠️⚠️⚠️:
1. Le template HTML est DÉJÀ créé sur le serveur
2. Tu dois UNIQUEMENT générer un JSON avec les DONNÉES
3. INTERDIT de générer du HTML - le JSON sera injecté dans le template
4. INTERDIT de copier les exemples entre [crochets] - génère du VRAI contenu

STRUCTURE JSON REQUISE (remplis chaque champ avec du CONTENU RÉEL et TECHNIQUE) :

{
  \"description_courte\": \"[Description courte de {$serviceName} incluant {$companyCity} et le département {$companyDept}. 150-200 caractères, TEXTE BRUT SEULEMENT]\",
  \"description_longue\": \"[Description longue et détaillée du {$serviceName}. Intègre {$companyCity} et {$companyDept}. Techniques, matériaux, bénéfices. 400-600 mots. TEXTE BRUT SEULEMENT]\",
  \"titre_garantie\": \"Garantie de satisfaction\",
  \"texte_garantie\": \"[Détaille les garanties pour {$serviceName}: garantie décennale, chantier propre, normes, matériaux qualité. SOIS SPÉCIFIQUE. TEXTE BRUT SEULEMENT]\",
  \"prestations\": [
    {\"titre\": \"[Prestation technique 1 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 2 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 3 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 4 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 5 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 6 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 7 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 8 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 9 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"},
    {\"titre\": \"[Prestation technique 10 RÉELLE pour {$serviceName}]\", \"description\": \"[Description technique en une phrase]\"}
  ],
  \"faq\": [
    {\"question\": \"[Question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée]\"},
    {\"question\": \"[Question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée]\"},
    {\"question\": \"[Question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée]\"},
    {\"question\": \"[Question fréquente RÉELLE sur {$serviceName}]\", \"reponse\": \"[Réponse détaillée]\"}
  ],
  \"pourquoi_choisir\": \"[Pourquoi choisir {$companyName} pour {$serviceName} à {$companyCity}. Avantages, expertise, qualité. TEXTE BRUT SEULEMENT]\",
  \"financement_aides\": \"[Aides disponibles en France pour {$serviceName}: MaPrimeRénov, CEE, éco-PTZ, TVA réduite. TEXTE BRUT SEULEMENT]\",
  \"infos_pratiques\": [
    {$infosPratiquesJsonString}
  ],
  \"short_description\": \"[Description courte SEO 120-140 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_title\": \"[Titre SEO 50-60 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_description\": \"[Description SEO 150-160 caractères pour {$serviceName} à {$companyCity}]\",
  \"meta_keywords\": \"[15-20 mots-clés pertinents: {$serviceName}, {$serviceName} {$companyCity}, expert {$serviceName}, artisan {$serviceName}, rénovation, réparation, devis gratuit, qualité garantie]\"
}

🚫🚫🚫 FORMAT JSON OBLIGATOIRE - INTERDICTIONS 🚫🚫🚫:
- INTERDIT de commencer par \"Voici\", \"Voilà\", \"Here is\", ou tout autre texte
- INTERDIT d'utiliser du markdown avec ** ou ##
- INTERDIT d'utiliser des sections comme \"**Description courte**\" ou \"**Titre SEO**\"
- INTERDIT d'utiliser des listes à puces ou des tableaux markdown
- INTERDIT de mettre des ```json ou ``` autour du JSON
- INTERDIT d'ajouter des commentaires ou explications
- INTERDIT de formater le JSON avec des espaces avant/après inutiles

✅✅✅ FORMAT JSON OBLIGATOIRE - STRUCTURE ✅✅✅:
- TU DOIS RÉPONDRE UNIQUEMENT AVEC UN JSON VALIDE
- COMMENCE DIRECTEMENT PAR { (accolade ouvrante) - PAS d'autre caractère avant
- TERMINE DIRECTEMENT PAR } (accolade fermante) - PAS d'autre caractère après
- Le JSON DOIT être valide et parsable directement
- JUSTE le JSON brut, rien d'autre

EXEMPLE DE CE QUI EST INTERDIT:
\"Voici le contenu web complet pour le service...\" ❌
\"**Description courte**\" ❌
\"```json { ... } ```\" ❌

EXEMPLE DE CE QUI EST OBLIGATOIRE:
{ \"description_courte\": \"...\", ... } ✅

⚠️⚠️⚠️ INSTRUCTIONS CRITIQUES - CONTENU ⚠️⚠️⚠️:
- ⚠️⚠️⚠️ CRITIQUE ABSOLUE: Le champ \"prestations\" DOIT être un TABLEAU contenant EXACTEMENT 10 OBJETS. Chaque objet DOIT avoir la structure {\"titre\": \"...\", \"description\": \"...\"}
- ⚠️⚠️⚠️ Tu DOIS générer 10 prestations COMPLÈTES et DIFFÉRENTES, chacune avec un \"titre\" et une \"description\" REMPLIS avec du VRAI contenu
- ⚠️ INTERDIT ABSOLU de copier les exemples entre [crochets]. Les valeurs entre [crochets] sont des INSTRUCTIONS, PAS du contenu à copier. Tu DOIS générer du VRAI contenu professionnel qui remplace complètement ces instructions.
- ⚠️ INTERDIT ABSOLU d'utiliser des prestations génériques comme 'Nettoyage', 'Réparation', 'Remplacement', 'Service professionnel' sans précision technique
- ⚠️ INTERDIT ABSOLU de mettre une seule prestation générique comme \"Service professionnel [service] - Intervention adaptée\"
- ⚠️ INTERDIT ABSOLU d'utiliser des phrases répétitives comme \"pour garantir une propreté et une sécurité optimales\" dans TOUTES les prestations
- ⚠️ INTERDIT ABSOLU d'utiliser des mots inventés ou incorrects (ex: \"Répulsion\" n'existe pas, utilise \"Réparation\" ou \"Remplacement\")
- ⚠️ INTERDIT ABSOLU d'inclure des prestations qui n'ont RIEN à voir avec la toiture (ex: \"élagage\" n'est PAS une prestation de toiture)
- ⚠️ Les prestations DOIVENT être TECHNIQUES et SPÉCIFIQUES au {$serviceName}. Exemples inspirants: {$prestationsExamples}
- ⚠️ Chaque prestation DOIT être UNIQUE et DIFFÉRENTE des autres. PAS de répétitions.
- ⚠️ Chaque description de prestation DOIT être différente et utiliser un vocabulaire technique varié
- ⚠️ Exemples de prestations CORRECTES pour toiture: \"Réparation de tuiles cassées avec remplacement à l'identique\", \"Remplacement de faîtage défectueux avec étanchéité renforcée\", \"Traitement hydrofuge de la toiture avec produit silicone haute performance\"
- ⚠️ Exemples de prestations INCORRECTES à éviter: \"Répulsion et remplacement des tuiles\", \"Retrait et remplacement de l'élagage\", toute prestation avec \"pour garantir une propreté et une sécurité optimales\"
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

                // Vérifier si l'IA a retourné du HTML au lieu de JSON
                if (preg_match('/<div[^>]*>|<p[^>]*>|<li[^>]*>/i', $content)) {
                    \Log::error('L\'IA a généré du HTML au lieu de JSON - rejeté', [
                        'service' => $serviceName,
                        'content_preview' => substr($content, 0, 300)
                    ]);
                    throw new \Exception("L'IA a généré du HTML au lieu de JSON. Le système attend UNIQUEMENT un JSON avec les données. Veuillez réessayer.");
                }
                
                // Parser le JSON de la réponse IA
                $jsonData = $this->parseJsonResponse($content);
                
                if (!$jsonData) {
                    \Log::error('Impossible de parser le JSON pour le service', [
                        'service' => $serviceName,
                        'content_preview' => substr($content, 0, 500),
                        'content_length' => strlen($content),
                        'contains_html' => preg_match('/<[^>]+>/', $content)
                    ]);
                    throw new \Exception("L'IA n'a pas retourné un JSON valide. Veuillez réessayer avec 'Forcer la régénération'.");
                }
                
                // Vérifier que les champs JSON attendus sont présents et pas de HTML
                $expectedKeys = ['description_courte', 'description_longue', 'prestations', 'faq'];
                $hasUnwantedHtmlField = isset($jsonData['description']) && preg_match('/<[^>]+>/', $jsonData['description']);
                
                if ($hasUnwantedHtmlField) {
                    \Log::error('Le JSON contient un champ "description" avec du HTML au lieu des champs attendus', [
                        'service' => $serviceName,
                        'json_keys' => array_keys($jsonData),
                        'has_html_in_description' => true
                    ]);
                    throw new \Exception("L'IA a généré du HTML dans le champ 'description' au lieu de fournir les champs JSON attendus (description_courte, description_longue, prestations, etc.). Veuillez réessayer.");
                }
                
                if (!isset($jsonData['description_courte']) || !isset($jsonData['prestations'])) {
                    \Log::error('Le JSON ne contient pas les champs attendus', [
                        'service' => $serviceName,
                        'json_keys' => array_keys($jsonData),
                        'has_description_courte' => isset($jsonData['description_courte']),
                        'has_prestations' => isset($jsonData['prestations'])
                    ]);
                    throw new \Exception("Le JSON généré ne contient pas les champs requis (description_courte, prestations, etc.). Champs trouvés: " . implode(', ', array_keys($jsonData)));
                }
                
                \Log::info('JSON parsé avec succès pour service', [
                    'service' => $serviceName,
                    'keys' => array_keys($jsonData),
                    'prestations_count' => count($jsonData['prestations'] ?? [])
                ]);

                // Vérifier que les prestations sont présentes et complètes
                if (!isset($jsonData['prestations']) || !is_array($jsonData['prestations'])) {
                    \Log::error('Prestations manquantes ou invalides pour le service', [
                        'service' => $serviceName,
                        'prestations_key_exists' => isset($jsonData['prestations']),
                        'prestations_type' => gettype($jsonData['prestations'] ?? null),
                        'json_keys' => array_keys($jsonData)
                    ]);
                    $jsonData['prestations'] = [];
                }
                
                // Valider et logger le nombre de prestations avec détails
                $prestationsCount = count($jsonData['prestations']);
                \Log::info('Validation prestations', [
                    'service' => $serviceName,
                    'count' => $prestationsCount,
                    'expected' => 10,
                    'prestations_preview' => array_slice($jsonData['prestations'], 0, 3)
                ]);
                
                if ($prestationsCount < 10) {
                    \Log::error('Nombre insuffisant de prestations pour le service - Génération invalide', [
                        'service' => $serviceName,
                        'count' => $prestationsCount,
                        'expected' => 10,
                        'prestations' => $jsonData['prestations']
                    ]);
                    // Ne pas continuer si moins de 10 prestations - c'est critique
                    throw new \Exception("L'IA n'a généré que {$prestationsCount} prestation(s) au lieu de 10. Veuillez réessayer avec 'Forcer la régénération'.");
                }
                
                // Valider que chaque prestation a un titre et une description
                foreach ($jsonData['prestations'] as $index => $prestation) {
                    if (!isset($prestation['titre']) || empty(trim($prestation['titre']))) {
                        \Log::error('Prestation invalide - titre manquant', [
                            'service' => $serviceName,
                            'prestation_index' => $index,
                            'prestation' => $prestation
                        ]);
                    }
                    if (!isset($prestation['description']) || empty(trim($prestation['description']))) {
                        \Log::error('Prestation invalide - description manquante', [
                            'service' => $serviceName,
                            'prestation_index' => $index,
                            'prestation' => $prestation
                        ]);
                    }
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
        
        // Supprimer tout texte avant le premier {
        // L'IA peut retourner "Voici le contenu..." ou du markdown
        if (strpos($content, '{') === false) {
            \Log::warning('JSON non trouvé dans la réponse IA - pas de {', [
                'content_preview' => substr($content, 0, 500),
                'content_length' => strlen($content)
            ]);
            return null;
        }
        
        // Extraire tout depuis le premier { jusqu'au dernier }
        $jsonStart = strpos($content, '{');
        $contentFromJson = substr($content, $jsonStart);
        
        // Trouver le dernier } qui équilibre les accolades
        $braceCount = 0;
        $jsonEnd = -1;
        for ($i = 0; $i < strlen($contentFromJson); $i++) {
            if ($contentFromJson[$i] === '{') {
                $braceCount++;
            } elseif ($contentFromJson[$i] === '}') {
                $braceCount--;
                if ($braceCount === 0) {
                    $jsonEnd = $i;
                    break;
                }
            }
        }
        
        if ($jsonEnd === -1) {
            // Si on ne trouve pas la fin équilibrée, utiliser la dernière }
            $jsonEnd = strrpos($contentFromJson, '}');
            if ($jsonEnd === false) {
                \Log::error('Impossible de trouver la fin du JSON', [
                    'content_preview' => substr($content, 0, 500)
                ]);
                return null;
            }
        }
        
        $jsonString = substr($contentFromJson, 0, $jsonEnd + 1);
        
        // Essayer de parser le JSON
        $data = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Erreur parsing JSON', [
                'error' => json_last_error_msg(),
                'json_preview' => substr($jsonString, 0, 500),
                'json_length' => strlen($jsonString),
                'original_content_preview' => substr($content, 0, 200)
            ]);
            
            // Dernière tentative : essayer de corriger les erreurs JSON communes
            // Supprimer les commentaires ou texte après }
            $jsonStringCleaned = preg_replace('/}[^}]*$/', '}', $jsonString);
            $data = json_decode($jsonStringCleaned, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
        }
        
        \Log::info('JSON parsé avec succès', [
            'json_length' => strlen($jsonString),
            'keys' => array_keys($data ?? [])
        ]);
        
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
            $prestationsCount = count($data['prestations']);
            \Log::info('Génération HTML prestations', [
                'service' => $serviceName,
                'prestations_count' => $prestationsCount
            ]);
            
            foreach ($data['prestations'] as $index => $prestation) {
                // Vérifier que la prestation est bien un tableau avec titre et description
                if (!is_array($prestation)) {
                    \Log::warning('Prestation invalide - pas un tableau', [
                        'service' => $serviceName,
                        'index' => $index,
                        'prestation_type' => gettype($prestation),
                        'prestation_value' => $prestation
                    ]);
                    continue;
                }
                
                $titre = isset($prestation['titre']) ? trim($prestation['titre']) : '';
                $description = isset($prestation['description']) ? trim($prestation['description']) : '';
                
                // Ne pas ajouter si titre ou description vide
                if (empty($titre) && empty($description)) {
                    \Log::warning('Prestation vide ignorée', [
                        'service' => $serviceName,
                        'index' => $index
                    ]);
                    continue;
                }
                
                $titreEscaped = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
                $descriptionEscaped = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
                
                $prestationsHtml .= '<li class="flex items-start">' .
                    '<i class="fas fa-check text-green-600 mr-3 mt-1 flex-shrink-0"></i>' .
                    '<span><strong>' . $titreEscaped . '</strong>' . 
                    (!empty($descriptionEscaped) ? ' - ' . $descriptionEscaped : '') . 
                    '</span>' .
                    '</li>';
            }
        } else {
            \Log::error('Prestations non disponibles dans fillTemplate', [
                'service' => $serviceName,
                'prestations_exists' => isset($data['prestations']),
                'prestations_type' => gettype($data['prestations'] ?? null),
                'data_keys' => array_keys($data)
            ]);
        }
        
        // Si aucune prestation générée, ajouter un message d'erreur visible
        if (empty($prestationsHtml)) {
            $prestationsHtml = '<li class="flex items-start text-red-600">
                <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-1 flex-shrink-0"></i>
                <span><strong>Erreur:</strong> Aucune prestation n\'a été générée. Veuillez régénérer le service avec "Forcer la régénération".</span>
            </li>';
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

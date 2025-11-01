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

            $system = "Expert SEO couverture/rénovation France. Produis du HTML pur (pas de Markdown). Utilise <h1>, <h2>, <h3>, <p>, <ul><li> avec icônes Font Awesome <i class=\"fas fa-check text-green-500 mr-2\"></i>. Ajoute des CTA <a href=\"URL\" class=\"cta-link\">Texte</a>. Ton pro, orienté conversion SEO.";
            
            $keywords = "devis gratuit, intervention rapide, couvreur professionnel, réparation toiture, rénovation, artisan couvreur, travaux de couverture, entreprise de couverture, spécialiste toiture, rénovation toiture, réparation urgence, devis personnalisé, garantie travaux, matériaux de qualité, expertise technique, intervention 24h/24, couvreur qualifié, travaux de rénovation, isolation toiture, étanchéité toiture";
            
            $user = ($customPrompt ? ($customPrompt . "\n\n") : '') . "Service: {$serviceName}\nLangue: {$language}\n\nCrée un contenu HTML de 1500-2000 mots, style pro et engageant.\n\nMots-clés à intégrer: {$keywords}\n\nStructure requise:\n<h1>Titre SEO avec mots-clés</h1>\n<p>Intro 3-4 phrases</p>\n<h2>Pourquoi choisir notre service?</h2>\n<h3>Avantages concurrentiels</h3>\n<ul><li><i class=\"fas fa-check text-green-500 mr-2\"></i>Avantage détaillé</li></ul>\n<h2>Expertise technique</h2>\n<h3>Techniques et matériaux</h3>\n<p>Description détaillée</p>\n<h2>Processus</h2>\n<h3>Étapes de travail</h3>\n<ul><li><i class=\"fas fa-check text-green-500 mr-2\"></i>Étape détaillée</li></ul>\n<h2>Tarifs et devis</h2>\n<h3>Transparence</h3>\n<p>Tarifs, garanties, délais</p>\n<h2>Zone d'intervention</h2>\n<p>Zone géographique, urgences</p>\n<p><a href=\"/devis-gratuit\" class=\"cta-link\">Devis gratuit</a></p>\n\nRègles: 5-6 <h2>, 6-8 <h3>, icône dans chaque <li>, 15-20 mots-clés, paragraphes 4-6 phrases, plusieurs CTA, HTML uniquement.";

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

                // Extraire un extrait du contenu
                $plain = trim(strip_tags($content));
                $excerpt = Str::limit(preg_replace('/\s+/', ' ', $plain), 200);
                $metaDescription = Str::limit($excerpt, 155);

                // Créer le service
                $newService = [
                    'id' => time() . rand(1000, 9999),
                    'name' => $serviceName,
                    'slug' => $slug,
                    'short_description' => $excerpt,
                    'description' => $content,
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
                    'faq' => [],
                    'meta_title' => $serviceName . ' - ' . setting('company_name', 'Sauser Couverture'),
                    'meta_description' => $metaDescription,
                    'meta_keywords' => 'devis gratuit, couvreur professionnel, ' . Str::slug($serviceName, ', ') . ', artisan couvreur, travaux de couverture, entreprise de couverture, spécialiste toiture, rénovation toiture, réparation urgence, devis personnalisé, garantie travaux, matériaux de qualité, expertise technique, intervention 24h/24, couvreur qualifié, travaux de rénovation, isolation toiture, étanchéité toiture',
                    'og_title' => $serviceName . ' - ' . setting('company_name', 'Sauser Couverture'),
                    'og_description' => $metaDescription,
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
}

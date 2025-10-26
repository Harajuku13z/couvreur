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

            $system = "Tu es un expert en marketing digital et SEO spécialisé pour les entreprises de couverture, toiture et rénovation en France. Tu produis STRICTEMENT du HTML (aucun Markdown), sans balises <html> ni <body>, uniquement le contenu. Utilise <h1> pour le titre principal, des <h2> pour les sections et des <h3> pour les sous-sections, des paragraphes <p>, et des listes <ul><li> avec des icônes Font Awesome. Inclure des CTA avec <a href=\"URL\" class=\"cta-link\">Texte CTA</a>. Ton professionnel, persuasif, orienté SEO et conversion. Intègre naturellement les mots-clés français du secteur.";
            
            $user = ($customPrompt ? ($customPrompt . "\n\n") : '') . "Service: {$serviceName}\nLangue: {$language}\n\nCONSIGNES DÉTAILLÉES:\n- Longueur: 1500 à 2000 mots minimum (contenu riche et détaillé)\n- Style: professionnel, engageant, orienté prospection/conversion\n- Mots-clés à intégrer naturellement: devis gratuit, intervention rapide, couvreur professionnel, réparation toiture, rénovation, artisan couvreur, travaux de couverture, entreprise de couverture, spécialiste toiture, rénovation toiture, réparation urgence, devis personnalisé, garantie travaux, matériaux de qualité, expertise technique, intervention 24h/24, couvreur qualifié, travaux de rénovation, isolation toiture, étanchéité toiture\n\nSTRUCTURE HTML OBLIGATOIRE:\n<h1>Titre principal optimisé SEO avec mots-clés</h1>\n<p>Introduction accrocheuse de 3-4 phrases sur le service, ses avantages et pourquoi choisir notre entreprise.</p>\n\n<h2>Pourquoi choisir notre service de [nom du service] ?</h2>\n<h3>Nos avantages concurrentiels</h3>\n<ul>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Avantage 1 détaillé</li>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Avantage 2 détaillé</li>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Avantage 3 détaillé</li>\n</ul>\n\n<h2>Notre expertise technique</h2>\n<h3>Techniques et matériaux utilisés</h3>\n<p>Description détaillée des techniques, matériaux, certifications, qualifications de l'équipe.</p>\n\n<h2>Processus de réalisation</h2>\n<h3>Étapes de travail détaillées</h3>\n<ul>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Étape 1: Description complète</li>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Étape 2: Description complète</li>\n<li><i class=\"fas fa-check text-green-500 mr-2\"></i>Étape 3: Description complète</li>\n</ul>\n\n<h2>Tarifs et devis personnalisé</h2>\n<h3>Transparence et qualité</h3>\n<p>Information sur les tarifs, devis gratuit, garanties, délais d'intervention.</p>\n\n<h2>Zone d'intervention</h2>\n<p>Précision sur la zone géographique couverte, délais d'intervention, service d'urgence.</p>\n\n<p><a href=\"/devis-gratuit\" class=\"cta-link\">Demander un devis gratuit et personnalisé</a></p>\n\nCONTRAINTES STRICTES:\n- 5 à 6 <h2> minimum, 6 à 8 <h3> minimum\n- Chaque <li> doit avoir l'icône <i class=\"fas fa-check text-green-500 mr-2\"></i>\n- Intégrer 15-20 mots-clés différents naturellement\n- Chaque paragraphe doit faire 4-6 phrases\n- Inclure des CTA multiples pour la conversion\n- Ton rassurant et professionnel\n- Mentionner les garanties et certifications\n- HTML uniquement, pas de CSS/JS externe";

            try {
                if (empty(env('GROQ_API_KEY'))) {
                    return back()->with('error', "Veuillez définir GROQ_API_KEY dans le fichier .env");
                }
                
                $response = Http::withToken(env('GROQ_API_KEY'))
                    ->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => $model ?: 'llama-3.1-8b-instant',
                        'messages' => [
                            ['role' => 'system', 'content' => $system],
                            ['role' => 'user', 'content' => $user],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 4000,
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

@extends('layouts.admin')

@section('title', 'Génération d\'articles par IA')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Génération d'articles par IA</h1>
                    <p class="text-gray-600 mt-2">Créez des articles de blog optimisés SEO avec l'intelligence artificielle</p>
                </div>
                <a href="{{ route('admin.articles.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Retour
                </a>
            </div>
        </div>

        <!-- Messages -->
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                    <div class="text-red-800">{{ session('error') }}</div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 mr-3"></i>
                    <div class="text-green-800">{{ session('success') }}</div>
                </div>
            </div>
        @endif

        <!-- Formulaire -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form action="{{ route('admin.articles.ai.generate') }}" method="POST">
                @csrf
                
                <!-- Titres des articles -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-heading mr-2"></i>Titres des articles (un par ligne)
                    </label>
                    <textarea name="titles" rows="8" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Exemples:
Comment choisir le bon matériau de couverture pour votre toiture
Les 5 signes qui indiquent qu'il faut refaire votre toiture
Guide complet de la rénovation de toiture en 2025
Isolation thermique de toiture : économies et confort
Réparation d'urgence de toiture : que faire en cas de fuite" 
                              required>{{ old('titles') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Saisissez un titre par ligne. Chaque titre générera un article complet.</p>
                </div>

                <!-- Paramètres -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2"></i>Catégorie
                        </label>
                        <input type="text" name="category" value="{{ old('category', 'Blog') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Blog">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-language mr-2"></i>Langue
                        </label>
                        <input type="text" name="language" value="{{ old('language', 'fr') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="fr">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-robot mr-2"></i>Modèle IA
                        </label>
                        <select name="model" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="gpt-4o" {{ old('model', setting('chatgpt_model', 'gpt-4o')) == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (Recommandé)</option>
                            <option value="gpt-4o-mini" {{ old('model') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Rapide)</option>
                            <option value="gpt-4-turbo" {{ old('model') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo</option>
                            <option value="gpt-3.5-turbo" {{ old('model') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-cog mr-2"></i>Fournisseur
                        </label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" 
                               value="OpenAI ChatGPT" disabled>
                    </div>
                </div>

                <!-- Instructions personnalisées -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-edit mr-2"></i>Instructions personnalisées (optionnel)
                    </label>
                    <textarea name="custom_prompt" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Ajoutez des consignes précises (ton, structure, CTA, etc.)
Exemple: 
- Ton plus commercial et persuasif
- Inclure des statistiques et chiffres
- Terminer par un CTA pour demande de devis
- Mentionner les garanties et certifications">{{ old('custom_prompt') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Ces instructions seront ajoutées au prompt de base pour personnaliser la génération.</p>
                </div>

                <!-- Boutons d'action -->
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-magic mr-2"></i>Générer les articles
                    </button>
                    
                    <button type="submit" formaction="{{ route('admin.articles.ai.test') }}" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors font-medium">
                        <i class="fas fa-wifi mr-2"></i>Tester la connexion
                    </button>
                </div>
            </form>

            <!-- Informations -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="text-sm font-medium text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Informations importantes
                </h3>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Chaque article généré fera 1500-2000 mots minimum</li>
                    <li>• Le contenu sera optimisé SEO avec mots-clés pertinents</li>
                    <li>• Structure HTML professionnelle avec H1, H2, H3, listes et CTA</li>
                    <li>• Articles automatiquement publiés après génération</li>
                    <li>• Clé API OpenAI requise dans la configuration</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

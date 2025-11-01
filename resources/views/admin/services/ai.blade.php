@extends('layouts.admin')

@section('title', 'Génération de Services par IA')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Génération de Services par IA</h1>
        <a href="{{ route('admin.services.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux Services
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('status'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg p-6">
        <form action="{{ route('admin.services.ai.generate') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="service_names" class="block text-sm font-medium text-gray-700 mb-2">
                    Noms des Services (un par ligne)
                </label>
                <textarea 
                    name="service_names" 
                    id="service_names"
                    rows="8" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    placeholder="Exemples:
Rénovation de toiture
Réparation de gouttières
Isolation des combles
Ravalement de façade
Charpente traditionnelle
Couverture en ardoise
Couverture en tuiles
Zinguerie et étanchéité"
                    required
                >{{ old('service_names') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Saisissez un nom de service par ligne. L'IA générera automatiquement un contenu riche de 1500-2000 mots avec des listes à puces avec icônes de validation et une optimisation SEO avancée.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Catégorie
                    </label>
                    <input 
                        type="text" 
                        name="category" 
                        id="category"
                        value="{{ old('category', 'Services de Couverture') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        placeholder="Services de Couverture"
                    >
                </div>
                
                <div>
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                        Langue
                    </label>
                    <input 
                        type="text" 
                        name="language" 
                        id="language"
                        value="{{ old('language', 'fr') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
                
                <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">
                        Fournisseur IA
                    </label>
                    <input 
                        type="text" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" 
                        value="Groq" 
                        disabled
                    >
                    <input type="hidden" name="provider" value="groq">
                </div>
                
                <div>
                    <label for="model" class="block text-sm font-medium text-gray-700 mb-2">
                        Modèle IA
                    </label>
                    <input 
                        type="text" 
                        name="model" 
                        id="model"
                        value="{{ old('model', 'llama-3.1-8b-instant') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                        placeholder="llama-3.1-8b-instant"
                    >
                </div>
            </div>

            <div class="mb-6">
                <label for="preset-model" class="block text-sm font-medium text-gray-700 mb-2">
                    Modèles suggérés
                </label>
                <select id="preset-model" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">— Choisir un modèle suggéré —</option>
                    <optgroup label="Groq">
                        <option value="llama-3.1-8b-instant">llama-3.1-8b-instant (Recommandé)</option>
                        <option value="llama-3.1-70b-versatile">llama-3.1-70b-versatile (Plus puissant)</option>
                        <option value="mixtral-8x7b-32768">mixtral-8x7b-32768 (Très rapide)</option>
                    </optgroup>
                </select>
            </div>

            <div class="mb-6">
                <label for="custom_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                    Instructions personnalisées (optionnel)
                </label>
                <textarea 
                    name="custom_prompt" 
                    id="custom_prompt"
                    rows="4" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    placeholder="Ajoutez des consignes précises (ton, structure, CTA, mots-clés spécifiques, etc.)

Exemples:
- Mettre l'accent sur la qualité des matériaux
- Inclure des informations sur les garanties
- Utiliser un ton rassurant et professionnel
- Mentionner les certifications et qualifications"
                >{{ old('custom_prompt') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Ces instructions seront ajoutées au prompt principal pour personnaliser la génération.</p>
            </div>

            <div class="mb-6">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="force_regenerate" 
                        value="1"
                        class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        {{ old('force_regenerate') ? 'checked' : '' }}
                    >
                    <span class="text-sm font-medium text-gray-700">
                        Forcer la régénération même si le service existe déjà
                    </span>
                </label>
                <p class="text-sm text-gray-500 mt-1 ml-6">Cochez cette case pour régénérer le contenu des services existants avec un nouveau contenu unique.</p>
            </div>

            <div class="flex gap-4">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
                >
                    <i class="fas fa-magic mr-2"></i>
                    Générer les Services
                </button>
                
                <button 
                    type="submit" 
                    formaction="{{ route('admin.services.ai.test') }}"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
                >
                    <i class="fas fa-wifi mr-2"></i>
                    Tester la Connexion
                </button>
            </div>
        </form>

        <div class="mt-6 p-4 bg-blue-50 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Configuration requise
            </h3>
            <p class="text-blue-700 text-sm">
                Assurez-vous d'avoir configuré <code class="bg-blue-100 px-2 py-1 rounded">GROQ_API_KEY</code> dans votre fichier <code class="bg-blue-100 px-2 py-1 rounded">.env</code>
            </p>
            <p class="text-blue-600 text-xs mt-2">
                <i class="fas fa-lightbulb mr-1"></i>
                L'IA générera automatiquement : contenu riche (1500-2000 mots), titre SEO optimisé, description détaillée, listes avec icônes de validation, 15-20 mots-clés intégrés naturellement, et CTA multiples pour la conversion.
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const preset = document.getElementById('preset-model');
    const input = document.querySelector('input[name="model"]');
    
    if (preset && input) {
        preset.addEventListener('change', function() {
            if (this.value) {
                input.value = this.value;
            }
        });
    }
});
</script>
@endsection

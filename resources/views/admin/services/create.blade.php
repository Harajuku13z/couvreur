@extends('layouts.admin')

@section('title', 'Créer un Nouveau Service')

<style>
/* Styles pour le formulaire de création de service */
.form-section {
    background: white;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-section h3 {
    color: #374151;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 16px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

.form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.form-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
}

.icon-preview {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    font-size: 18px;
    margin-left: 12px;
}

.ai-generator {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
}

.ai-generator h3 {
    color: white;
    border-bottom: 1px solid rgba(255,255,255,0.3);
}

.btn-ai {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-ai:hover {
    background: rgba(255,255,255,0.3);
}

.btn-primary {
    background: #3b82f6;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary:hover {
    background: #4b5563;
}
</style>

@section('content')
<div class="bg-gray-50 min-h-full p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">🛠️ Créer un Nouveau Service</h2>
                    <p class="mt-2 text-gray-600">Créez une page de service avec génération automatique de contenu par IA</p>
                </div>
                <a href="{{ route('services.admin.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Retour aux Services
                </a>
            </div>
            
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif
        </div>

        <form action="{{ route('services.admin.store') }}" method="POST" id="service-form" enctype="multipart/form-data">
            @csrf
            
            <!-- Informations de base -->
            <div class="form-section">
                <h3>📋 Informations de Base</h3>
                <p class="text-sm text-gray-600 mb-6">
                    <i class="fas fa-robot text-blue-500 mr-1"></i>
                    L'IA générera automatiquement tout le contenu (description courte, description longue, SEO, icône, etc.) à partir de ces informations
                </p>
                
                <div class="form-group">
                    <label class="form-label">Nom du Service *</label>
                    <input type="text" name="name" class="form-input" placeholder="Ex: Couverture, Façade, Isolation, Hydrofuge..." required>
                    <p class="text-xs text-gray-500 mt-1">Le nom de votre service (ex: "Couverture de toiture")</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Brief / Contexte du Service *</label>
                    <textarea name="short_description" class="form-input form-textarea" rows="3" placeholder="Ex: Nous proposons tous types de travaux de toiture, réparation, rénovation complète, remplacement de tuiles..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                        Donnez un brief court à l'IA pour qu'elle génère automatiquement la description courte et longue
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Prompt IA Personnalisé (optionnel)</label>
                    <textarea name="ai_prompt" class="form-input form-textarea" rows="3" placeholder="Tu es un expert en rédaction de contenu pour services. Génère une description de service professionnelle, incluant les avantages, la qualité, et l'expertise. Le contenu doit inspirer confiance."></textarea>
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-robot text-blue-500 mr-1"></i>
                        Laissez vide pour utiliser le prompt par défaut. Sinon, décrivez comment l'IA doit générer le contenu.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Image de Mise en Avant</label>
                    <input type="file" name="featured_image" accept="image/*" class="form-input">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-image text-blue-500 mr-1"></i>
                        Image principale du service (recommandé: 800x600px, format JPG/PNG)
                    </p>
                </div>
            </div>

            <!-- Générateur IA -->
            <div class="ai-generator">
                <h3>🤖 Génération Automatique par IA</h3>
                <p class="mb-4">Lors de l'enregistrement, l'IA va créer automatiquement :</p>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <ul class="text-sm space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Description courte</strong> pour la page d'accueil (100-150 caractères)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Description longue HTML</strong> riche et détaillée (800-1200 mots)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Icône Font Awesome</strong> adaptée au service</span>
                        </li>
                    </ul>
                    <ul class="text-sm space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Meta Title SEO</strong> optimisé avec localisation</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Meta Description SEO</strong> engageante (150-160 caractères)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-0.5 text-green-300"></i>
                            <span><strong>Mots-clés locaux</strong> pour votre région</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-3 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    Tout ce contenu sera généré automatiquement et sera modifiable après la création
                </div>
            </div>

            <!-- Visibilité -->
            <div class="form-section">
                <h3>👁️ Visibilité</h3>
                
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" name="is_featured" id="is_featured" value="1" checked>
                        <label for="is_featured">Afficher sur la page d'accueil</label>
                    </div>
                    <p class="text-sm text-gray-500">Le service apparaîtra dans la section "Nos Services" de la page d'accueil</p>
                </div>
                
                <div class="form-group">
                    <div class="form-checkbox">
                        <input type="checkbox" name="is_menu" id="is_menu" value="1" checked>
                        <label for="is_menu">Afficher dans le menu de navigation</label>
                    </div>
                    <p class="text-sm text-gray-500">Le service apparaîtra dans le menu principal du site</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('services.admin.index') }}" class="btn-secondary">
                    <i class="fas fa-times mr-2"></i>Annuler
                </a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Créer le Service
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Mise à jour de l'aperçu de l'icône
document.querySelector('input[name="icon"]').addEventListener('input', function() {
    const iconClass = this.value || 'fas fa-tools';
    const preview = document.getElementById('icon-preview');
    preview.className = iconClass;
});

// Génération de contenu IA
function generateAIContent() {
    const serviceName = document.querySelector('input[name="name"]').value;
    const description = document.querySelector('textarea[name="description"]').value;
    
    if (!serviceName || !description) {
        alert('Veuillez remplir le nom et la description du service avant de générer le contenu.');
        return;
    }
    
    // Simulation de génération IA
    const generatedContent = `
    <div class="service-hero bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold mb-4">Service ${serviceName}</h1>
            <p class="text-xl mb-8">${description}</p>
            <a href="#contact" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Demander un Devis
            </a>
        </div>
    </div>
    
    <div class="service-content py-16">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold mb-6">Notre Expertise en ${serviceName}</h2>
                    <p class="text-lg text-gray-700 mb-6">${description}</p>
                    
                    <h3 class="text-2xl font-semibold mb-4">Pourquoi Choisir Notre Entreprise ?</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Plus de 10 ans d'expérience</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Matériaux de qualité premium</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Équipe d'artisans qualifiés</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Garantie sur tous nos travaux</span>
                        </li>
                    </ul>
                </div>
                
                <div class="bg-gray-50 p-8 rounded-lg">
                    <h3 class="text-2xl font-semibold mb-6">Contactez-nous</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-phone text-blue-600 mr-3"></i>
                            <span>Votre numéro de téléphone</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-600 mr-3"></i>
                            <span>Votre email</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
                            <span>Votre adresse</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `;
    
    // Afficher le contenu généré
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold">Contenu Généré par l'IA</h3>
                <p class="text-gray-600">Voici le contenu qui sera généré pour votre page de service</p>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap">${generatedContent}</pre>
                </div>
                <div class="flex justify-end gap-3">
                    <button onclick="this.closest('.fixed').remove()" class="btn-secondary">
                        Fermer
                    </button>
                    <button onclick="applyGeneratedContent()" class="btn-primary">
                        Appliquer le Contenu
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function applyGeneratedContent() {
    // Ici on pourrait appliquer le contenu généré au formulaire
    alert('Contenu généré appliqué ! Vous pouvez maintenant personnaliser les champs.');
    document.querySelector('.fixed').remove();
}
</script>
@endsection











@extends('layouts.admin')

@section('title', 'Configuration Google Reviews')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Configuration Google Reviews</h1>
            <p class="text-gray-600 mt-2">Importez vos avis Google directement</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux Avis
        </a>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Configuration -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuration Google Places</h2>
            
            <form action="{{ route('admin.reviews.google.config.save') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="google_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Clé API Google Places
                        </label>
                        <input type="text" 
                               id="google_api_key" 
                               name="google_api_key" 
                               value="{{ old('google_api_key', $googleApiKey) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Votre clé API Google Places"
                               required>
                        <p class="text-sm text-gray-500 mt-1">
                            Obtenez votre clé sur <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>
                        </p>
                    </div>

                    <div>
                        <label for="google_place_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Google Place ID
                        </label>
                        <input type="text" 
                               id="google_place_id" 
                               name="google_place_id" 
                               value="{{ old('google_place_id', $googlePlaceId) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ex: ChIJN1t_tDeuEmsRUsoyG83frY4"
                               required>
                        <p class="text-sm text-gray-500 mt-1">
                            Trouvez votre Place ID sur <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank" class="text-blue-600 hover:underline">Google Places API</a>
                        </p>
                    </div>

                    <div>
                        <label for="outscraper_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Clé API Outscraper (Optionnel - pour import complet)
                        </label>
                        <input type="text" 
                               id="outscraper_api_key" 
                               name="outscraper_api_key" 
                               value="{{ old('outscraper_api_key', $outscraperApiKey) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Votre clé API Outscraper">
                        <p class="text-sm text-gray-500 mt-1">
                            Obtenez votre clé sur <a href="https://outscraper.com/" target="_blank" class="text-blue-600 hover:underline">Outscraper.com</a> pour importer plus de 5 avis
                        </p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="auto_approve_google" 
                               name="auto_approve_google" 
                               value="1"
                               {{ old('auto_approve_google', $autoApprove) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="auto_approve_google" class="ml-2 block text-sm text-gray-700">
                            Approuver automatiquement les avis importés
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>Sauvegarder la Configuration
                    </button>
                </div>
            </form>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Actions</h2>
            
            @if($googleApiKey && $googlePlaceId)
                <div class="space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h3 class="text-sm font-medium text-green-800">Configuration Complète</h3>
                                <p class="text-sm text-green-700">Vous pouvez importer les avis Google</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="testConnection()" class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition flex items-center justify-center">
                            <i class="fas fa-wifi mr-2"></i>Test Connexion Google
                        </button>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <form action="{{ route('admin.reviews.google.import') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>Import Standard (5 avis)
                                </button>
                            </form>
                            
                            @if($outscraperApiKey)
                                <form action="{{ route('admin.reviews.google.import-all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center justify-center">
                                        <i class="fas fa-download mr-2"></i>Import Complet (Tous les avis)
                                    </button>
                                </form>
                            @else
                                <div class="w-full bg-gray-300 text-gray-600 px-4 py-2 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-lock mr-2"></i>Import Complet (Clé Outscraper requise)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Configuration Incomplète</h3>
                            <p class="text-sm text-yellow-700">Veuillez remplir tous les champs pour importer des avis</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800">Options d'Import</h3>
                        <ul class="text-sm text-blue-700 mt-2 space-y-1">
                            <li><strong>Import Standard :</strong> 5 avis via Google Places API (rapide)</li>
                            <li><strong>Import Complet :</strong> Tous les avis via scraping (plus lent)</li>
                            <li>• Configurez votre clé API Google Places</li>
                            <li>• Ajoutez votre Place ID Google</li>
                            <li>• Testez la connexion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Test -->
<div id="testModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-600"></div>
                </div>
                <h3 class="text-lg font-semibold text-center mb-2">Test de Connexion</h3>
                <p class="text-gray-600 text-center">Vérification de la connexion avec Google Places...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Résultat -->
<div id="resultModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div id="resultIcon" class="flex items-center justify-center mb-4">
                    <!-- Icône sera ajoutée par JavaScript -->
                </div>
                <h3 id="resultTitle" class="text-lg font-semibold text-center mb-2"></h3>
                <p id="resultMessage" class="text-gray-600 text-center mb-4"></p>
                <button onclick="closeResultModal()" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    // Afficher le modal de test
    document.getElementById('testModal').classList.remove('hidden');
    
    // Créer un formulaire pour la requête
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Faire la requête AJAX
    fetch('{{ route("admin.reviews.google.test") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        // Cacher le modal de test
        document.getElementById('testModal').classList.add('hidden');
        
        // Afficher le résultat
        showResult(data.success, data.message);
    })
    .catch(error => {
        // Cacher le modal de test
        document.getElementById('testModal').classList.add('hidden');
        
        // Afficher l'erreur
        showResult(false, 'Erreur de connexion: ' + error.message);
    });
}

function showResult(success, message) {
    const modal = document.getElementById('resultModal');
    const icon = document.getElementById('resultIcon');
    const title = document.getElementById('resultTitle');
    const messageEl = document.getElementById('resultMessage');
    
    if (success) {
        icon.innerHTML = '<i class="fas fa-check-circle text-green-600 text-4xl"></i>';
        title.textContent = 'Connexion Réussie';
        title.className = 'text-lg font-semibold text-center mb-2 text-green-600';
    } else {
        icon.innerHTML = '<i class="fas fa-times-circle text-red-600 text-4xl"></i>';
        title.textContent = 'Connexion Échouée';
        title.className = 'text-lg font-semibold text-center mb-2 text-red-600';
    }
    
    messageEl.textContent = message;
    modal.classList.remove('hidden');
}

function closeResultModal() {
    document.getElementById('resultModal').classList.add('hidden');
}
</script>
@endsection
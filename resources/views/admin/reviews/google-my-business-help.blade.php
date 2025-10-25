@extends('layouts.admin')

@section('title', 'Aide Google My Business API')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <i class="fas fa-building text-green-600 mr-2"></i>Aide Google My Business API
                </h1>
                <p class="text-gray-600 mt-2">Guide pour configurer l'import de tous les avis Google</p>
            </div>
            <a href="{{ route('admin.reviews.google.config') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Retour à la Configuration
            </a>
        </div>

        <!-- Configuration simplifiée -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Configuration Simplifiée</h2>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mr-3 mt-1"></i>
                    <div>
                        <h4 class="font-semibold text-green-900 mb-1">Package Laravel installé !</h4>
                        <p class="text-green-800 text-sm">
                            Le package <code>adnanhussainturki/google-my-business-php</code> est maintenant installé. 
                            L'import est maintenant beaucoup plus simple !
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Étape 1 -->
                <div class="border-l-4 border-blue-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">1</span>
                        Obtenir un Access Token
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>1. Allez sur <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:text-blue-800">Google Cloud Console</a></p>
                        <p>2. Activez l'API Google My Business</p>
                        <p>3. Créez des credentials OAuth2</p>
                        <p>4. Obtenez votre Access Token</p>
                    </div>
                </div>

                <!-- Étape 2 -->
                <div class="border-l-4 border-green-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-green-100 text-green-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">2</span>
                        Trouver vos IDs
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p><strong>Account ID :</strong> Utilisez cette commande :</p>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <code class="text-sm">curl -H "Authorization: Bearer YOUR_TOKEN" "https://mybusiness.googleapis.com/v4/accounts"</code>
                        </div>
                        <p><strong>Location ID :</strong> Puis cette commande :</p>
                        <div class="bg-gray-100 p-3 rounded-lg">
                            <code class="text-sm">curl -H "Authorization: Bearer YOUR_TOKEN" "https://mybusiness.googleapis.com/v4/accounts/ACCOUNT_ID/locations"</code>
                        </div>
                    </div>
                </div>

                <!-- Étape 3 -->
                <div class="border-l-4 border-purple-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">3</span>
                        Configurer dans Laravel
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>1. Remplissez les 3 champs dans la configuration</p>
                        <p>2. Cliquez sur "Sauvegarder"</p>
                        <p>3. Utilisez le bouton "Import Google My Business"</p>
                        <p>4. Tous vos avis seront importés automatiquement !</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations importantes -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                <div>
                    <h3 class="font-semibold text-yellow-900 mb-2">Informations Importantes</h3>
                    <ul class="text-yellow-800 text-sm space-y-1">
                        <li>• L'API Google My Business nécessite une authentification OAuth2</li>
                        <li>• Vous devez avoir un compte Google My Business actif</li>
                        <li>• Les tokens d'accès expirent et doivent être renouvelés</li>
                        <li>• Cette méthode permet de récupérer TOUS les avis (pas seulement 5)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Liens utiles -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Liens Utiles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="https://console.cloud.google.com/" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Google Cloud Console</span>
                </a>
                <a href="https://developers.google.com/my-business/reference/rest/v4/accounts" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Documentation API</span>
                </a>
                <a href="https://developers.google.com/identity/protocols/oauth2" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Guide OAuth2</span>
                </a>
                <a href="https://mybusiness.google.com/" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                    <i class="fas fa-external-link-alt text-blue-600 mr-3"></i>
                    <span class="text-sm font-medium text-gray-700">Google My Business</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

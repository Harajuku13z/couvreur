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

        <!-- Étapes de configuration -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Étapes de Configuration</h2>
            
            <div class="space-y-6">
                <!-- Étape 1 -->
                <div class="border-l-4 border-blue-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">1</span>
                        Créer un Projet Google Cloud
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>1. Allez sur <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:text-blue-800">Google Cloud Console</a></p>
                        <p>2. Créez un nouveau projet ou sélectionnez un projet existant</p>
                        <p>3. Notez l'ID du projet (visible dans l'URL ou les paramètres)</p>
                    </div>
                </div>

                <!-- Étape 2 -->
                <div class="border-l-4 border-green-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-green-100 text-green-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">2</span>
                        Activer Google My Business API
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>1. Dans la console, allez dans "APIs & Services" > "Library"</p>
                        <p>2. Recherchez "Google My Business API"</p>
                        <p>3. Cliquez sur "Enable" pour activer l'API</p>
                    </div>
                </div>

                <!-- Étape 3 -->
                <div class="border-l-4 border-purple-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">3</span>
                        Configurer OAuth2
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p>1. Allez dans "APIs & Services" > "Credentials"</p>
                        <p>2. Cliquez sur "Create Credentials" > "OAuth client ID"</p>
                        <p>3. Sélectionnez "Web application"</p>
                        <p>4. Ajoutez votre domaine dans "Authorized redirect URIs"</p>
                        <p>5. Téléchargez le fichier JSON des credentials</p>
                    </div>
                </div>

                <!-- Étape 4 -->
                <div class="border-l-4 border-yellow-500 pl-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        <span class="bg-yellow-100 text-yellow-800 text-sm font-semibold px-2 py-1 rounded-full mr-3">4</span>
                        Obtenir les IDs et Token
                    </h3>
                    <div class="text-gray-700 space-y-2">
                        <p><strong>Account ID :</strong> Utilisez l'API pour lister vos comptes</p>
                        <p><strong>Location ID :</strong> Utilisez l'API pour lister vos établissements</p>
                        <br>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="font-semibold mb-2">Commandes utiles :</h4>
                            <pre class="text-sm"><code># Lister les comptes
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  "https://mybusiness.googleapis.com/v4/accounts"

# Lister les établissements
curl -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  "https://mybusiness.googleapis.com/v4/accounts/ACCOUNT_ID/locations"</code></pre>
                        </div>
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

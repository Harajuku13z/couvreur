@extends('layouts.admin')

@section('title', 'Indexation Google')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">🔍 Indexation Google Search Console</h1>
    </div>
    
    <!-- Informations sur les limites API -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-blue-800">
            <i class="fas fa-info-circle mr-2"></i>Limites de l'API Google Indexing
        </h2>
        <div class="space-y-3 text-sm text-blue-900">
            <div class="flex items-start">
                <i class="fas fa-check-circle mr-2 mt-1"></i>
                <div>
                    <strong>Quota par défaut :</strong> 200 requêtes par jour maximum par projet Google Cloud
                </div>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check-circle mr-2 mt-1"></i>
                <div>
                    <strong>Format :</strong> 1 URL par requête (pas de batch de plusieurs URLs en une fois)
                </div>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check-circle mr-2 mt-1"></i>
                <div>
                    <strong>Restriction :</strong> L'API est officiellement destinée aux pages avec balisage JobPosting ou BroadcastEvent uniquement
                </div>
            </div>
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mr-2 mt-1 text-yellow-600"></i>
                <div>
                    <strong>Important :</strong> L'utilisation pour d'autres types de pages peut être restreinte ou bloquée par Google
                </div>
            </div>
        </div>
        
        <div class="mt-4 pt-4 border-t border-blue-300">
            <h3 class="font-semibold mb-2">💡 Augmenter le quota</h3>
            <p class="text-sm text-blue-800 mb-2">
                Pour demander une augmentation de quota :
            </p>
            <ol class="text-sm text-blue-800 list-decimal list-inside space-y-1">
                <li>Allez sur <a href="https://console.cloud.google.com/iam-admin/quotas" target="_blank" class="underline">Google Cloud Console → Quotas</a></li>
                <li>Recherchez "Indexing API requests per day"</li>
                <li>Cliquez sur "Edit Quotas"</li>
                <li>Remplissez le formulaire avec une justification professionnelle</li>
                <li>Attendez la réponse (24-72h généralement)</li>
            </ol>
        </div>
    </div>
    
    <!-- Alternatives d'indexation -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-green-800">
            <i class="fas fa-lightbulb mr-2"></i>Alternatives recommandées
        </h2>
        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-green-900 mb-2">✅ Sitemaps XML (Recommandé par Google)</h3>
                <p class="text-sm text-green-800 mb-2">
                    Pour indexer massivement, soumettez vos sitemaps via Google Search Console. C'est la méthode officielle et la plus fiable.
                </p>
                <ul class="text-sm text-green-800 list-disc list-inside space-y-1">
                    <li>Créez un sitemap-index si vous avez plus de 50 000 URLs</li>
                    <li>Soumettez-le dans Search Console → Sitemaps</li>
                    <li>Google indexera automatiquement vos pages</li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-semibold text-green-900 mb-2">✅ Services tiers d'indexation</h3>
                <p class="text-sm text-green-800 mb-2">
                    Services disponibles (non officiels, à utiliser avec précaution) :
                </p>
                <div class="grid md:grid-cols-2 gap-4 mt-3">
                    <div class="bg-white p-4 rounded border border-green-200">
                        <h4 class="font-semibold mb-2">IndexJump</h4>
                        <p class="text-xs text-gray-600 mb-2">Version gratuite : 100 URLs</p>
                        <a href="https://indexjump.com" target="_blank" class="text-blue-600 text-sm hover:underline">indexjump.com</a>
                    </div>
                    <div class="bg-white p-4 rounded border border-green-200">
                        <h4 class="font-semibold mb-2">SpeedyIndex</h4>
                        <p class="text-xs text-gray-600 mb-2">Essai gratuit : 100 liens</p>
                        <p class="text-xs text-gray-500">~0.0075 USD/lien</p>
                    </div>
                </div>
                <p class="text-xs text-yellow-700 mt-3">
                    ⚠️ Ces services ne garantissent pas l'indexation et peuvent être considérés comme "manipulatifs" par Google. Utilisez avec précaution.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Configuration actuelle -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Configuration actuelle</h2>
        <p class="text-sm text-gray-600 mb-4">
            Votre configuration d'indexation Google Search Console
        </p>
        
        @if(setting('google_search_console_service_account'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                <div>
                    <p class="font-semibold text-green-800">Service Account configuré</p>
                    <p class="text-sm text-green-700">L'API Google Indexing est activée</p>
                </div>
            </div>
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                <div>
                    <p class="font-semibold text-yellow-800">Service Account non configuré</p>
                    <p class="text-sm text-yellow-700">Configurez votre Service Account dans les paramètres pour activer l'indexation</p>
                </div>
            </div>
        </div>
        @endif
        
        <div class="mt-4">
            <a href="{{ route('config.index') }}#indexation" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition inline-flex items-center">
                <i class="fas fa-cog mr-2"></i>
                Configurer l'indexation
            </a>
        </div>
    </div>
</div>
@endsection

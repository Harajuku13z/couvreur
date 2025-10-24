@extends('layouts.admin')

@section('title', 'Statistiques Détaillées')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Statistiques Détaillées</h1>
        <p class="text-gray-600 mt-2">Analyse complète de votre site et de vos performances</p>
    </div>

    <!-- Stats globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Taux de conversion</p>
                    <p class="text-3xl font-bold mt-2">{{ $conversionRate }}%</p>
                </div>
                <i class="fas fa-chart-line text-4xl text-blue-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Soumissions complétées</p>
                    <p class="text-3xl font-bold mt-2">{{ $completedSubmissions }}</p>
                </div>
                <i class="fas fa-check-circle text-4xl text-green-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Soumissions abandonnées</p>
                    <p class="text-3xl font-bold mt-2">{{ $abandonedSubmissions }}</p>
                </div>
                <i class="fas fa-times-circle text-4xl text-red-200"></i>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total soumissions</p>
                    <p class="text-3xl font-bold mt-2">{{ $totalSubmissions }}</p>
                </div>
                <i class="fas fa-file-alt text-4xl text-purple-200"></i>
            </div>
        </div>
    </div>

    <!-- Contenu du site -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Services -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Services</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des services</span>
                    <span class="font-semibold text-2xl text-blue-600">{{ $totalServices }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Services mis en avant</span>
                    <span class="font-semibold text-xl text-green-600">{{ $featuredServices }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalServices > 0 ? ($featuredServices / $totalServices) * 100 : 0 }}%"></div>
                </div>
                <p class="text-sm text-gray-500">{{ round(($featuredServices / max($totalServices, 1)) * 100, 1) }}% des services sont mis en avant</p>
            </div>
        </div>

        <!-- Portfolio -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Portfolio</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des projets</span>
                    <span class="font-semibold text-2xl text-indigo-600">{{ $totalPortfolioItems }}</span>
                </div>
                @if(count($workTypes) > 0)
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Répartition par type :</p>
                    @foreach($workTypes as $type => $count)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">{{ ucfirst($type) }}</span>
                        <span class="font-medium">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Articles -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Articles</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des articles</span>
                    <span class="font-semibold text-2xl text-pink-600">{{ $totalArticles }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Articles publiés</span>
                    <span class="font-semibold text-xl text-green-600">{{ $publishedArticles }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Brouillons</span>
                    <span class="font-semibold text-xl text-yellow-600">{{ $draftArticles }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-pink-500 h-2 rounded-full" style="width: {{ $totalArticles > 0 ? ($publishedArticles / $totalArticles) * 100 : 0 }}%"></div>
                </div>
                <p class="text-sm text-gray-500">{{ $totalArticles > 0 ? round(($publishedArticles / $totalArticles) * 100, 1) : 0 }}% des articles sont publiés</p>
            </div>
        </div>

        <!-- Annonces -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Annonces</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des annonces</span>
                    <span class="font-semibold text-2xl text-cyan-600">{{ $totalAds }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Annonces publiées</span>
                    <span class="font-semibold text-xl text-green-600">{{ $publishedAds }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Brouillons</span>
                    <span class="font-semibold text-xl text-yellow-600">{{ $draftAds }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-cyan-500 h-2 rounded-full" style="width: {{ $totalAds > 0 ? ($publishedAds / $totalAds) * 100 : 0 }}%"></div>
                </div>
                <p class="text-sm text-gray-500">{{ $totalAds > 0 ? round(($publishedAds / $totalAds) * 100, 1) : 0 }}% des annonces sont publiées</p>
            </div>
        </div>

        <!-- Villes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Villes</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des villes</span>
                    <span class="font-semibold text-2xl text-indigo-600">{{ $totalCities }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Villes favorites</span>
                    <span class="font-semibold text-xl text-yellow-600">{{ $favoriteCities }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $totalCities > 0 ? ($favoriteCities / $totalCities) * 100 : 0 }}%"></div>
                </div>
                <p class="text-sm text-gray-500">{{ $totalCities > 0 ? round(($favoriteCities / $totalCities) * 100, 1) : 0 }}% des villes sont favorites</p>
            </div>
        </div>
    </div>

    <!-- Avis et Appels -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Avis -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Avis Clients</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des avis</span>
                    <span class="font-semibold text-2xl text-orange-600">{{ $totalReviews }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Avis actifs</span>
                    <span class="font-semibold text-xl text-green-600">{{ $activeReviews }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Note moyenne</span>
                    <div class="flex items-center">
                        <span class="font-semibold text-xl text-yellow-600">{{ number_format($avgRating, 1) }}</span>
                        <div class="ml-2 flex">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $avgRating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                    </div>
                </div>
                @if(count($ratingDistribution) > 0)
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Répartition des notes :</p>
                    @for($rating = 5; $rating >= 1; $rating--)
                    @if(isset($ratingDistribution[$rating]))
                    <div class="flex items-center">
                        <span class="text-sm text-gray-600 w-8">{{ $rating }}★</span>
                        <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                            <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ ($ratingDistribution[$rating] / max($activeReviews, 1)) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $ratingDistribution[$rating] }}</span>
                    </div>
                    @endif
                    @endfor
                </div>
                @endif
            </div>
        </div>

        <!-- Appels téléphoniques -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Appels Téléphoniques</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total des appels</span>
                    <span class="font-semibold text-2xl text-teal-600">{{ $totalPhoneCalls }}</span>
                </div>
                @if(count($callsByPage) > 0)
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Appels par page :</p>
                    @foreach($callsByPage as $page => $count)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-600">{{ ucfirst($page) }}</span>
                        <span class="font-medium">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                @endif
                @if(count($callsTrend) > 0)
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700">Tendance (7 derniers jours) :</p>
                    <div class="flex justify-between text-xs text-gray-500">
                        @foreach($callsTrend as $date => $count)
                        <div class="text-center">
                            <div class="font-medium">{{ $count }}</div>
                            <div>{{ $date }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Abandon par étape -->
    @if($stepStatistics->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Analyse des Abandons par Étape</h3>
        <div class="space-y-4">
            @foreach($stepStatistics as $stat)
            <div class="border-l-4 border-red-500 pl-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="font-medium text-gray-900">Étape {{ $stat->abandoned_at_step }}</span>
                    <span class="text-sm text-gray-500">{{ $stat->abandonment_count }} abandons</span>
                </div>
                <div class="flex items-center">
                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-3">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($stat->abandonment_count / max($totalSubmissions, 1)) * 100 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-500">{{ round(($stat->abandonment_count / max($totalSubmissions, 1)) * 100, 1) }}%</span>
                </div>
                @if($stat->avg_time_spent)
                <p class="text-xs text-gray-500 mt-1">Temps moyen passé : {{ gmdate('i:s', $stat->avg_time_spent) }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
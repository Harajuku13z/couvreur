@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
<div class="p-4 md:p-6">
    <!-- Menu rapide mobile -->
    <div class="md:hidden mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Accès rapide</h2>
        <div class="grid grid-cols-2 gap-3">
            <!-- Devis & Facturation -->
            @if(Route::has('admin.quotations.dashboard'))
            <a href="{{ route('admin.quotations.dashboard') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-chart-pie text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Devis & Facturation</span>
            </a>
            @else
            <a href="{{ route('admin.devis.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-chart-pie text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Devis & Facturation</span>
            </a>
            @endif
            
            <a href="{{ route('admin.devis.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-file-invoice text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Devis</span>
            </a>
            
            <a href="{{ route('admin.factures.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-receipt text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Factures</span>
            </a>
            
            <a href="{{ route('admin.clients.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-users text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Clients</span>
            </a>
            
            <!-- Leads, Appels, Visites -->
            <a href="{{ route('admin.submissions') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Leads</span>
            </a>
            
            <a href="{{ route('admin.phone-calls') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-teal-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-phone text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Appels</span>
            </a>
            
            <a href="{{ route('admin.visits') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-chart-line text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Visites</span>
            </a>
            
            <!-- Articles -->
            <a href="{{ route('admin.articles.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-newspaper text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Articles</span>
            </a>
            
            <!-- Annonces -->
            <a href="{{ route('admin.ads.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-cyan-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-bullhorn text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Annonces</span>
            </a>
            
            <!-- Services -->
            <a href="{{ route('services.admin.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-tools text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Services</span>
            </a>
            
            <!-- Portfolio -->
            <a href="{{ route('portfolio.admin.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-images text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Réalisations</span>
            </a>
            
            <!-- Avis -->
            <a href="{{ route('admin.reviews.index') }}" 
               class="bg-white rounded-lg shadow p-4 flex flex-col items-center justify-center hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-star text-white text-xl"></i>
                </div>
                <span class="text-sm font-medium text-gray-700 text-center">Avis</span>
            </a>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-6">
        <a href="{{ route('admin.export.submissions') }}" 
           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-center sm:text-left">
            <i class="fas fa-download mr-2"></i>
            Export CSV
        </a>
        <a href="{{ route('admin.statistics') }}" 
           class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center sm:text-left">
            <i class="fas fa-chart-bar mr-2"></i>
            Statistiques
        </a>
    </div>

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Soumissions -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Soumissions</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($totalSubmissions) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-check-circle text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Complétées</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($completedSubmissions) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-times-circle text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Abandonnées</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($abandonedSubmissions) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">En cours</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($inProgressSubmissions) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenu du site -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
            <!-- Services -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-tools text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Services</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalServices }}</dd>
                            <dd class="text-sm text-gray-500">{{ $activeServices }} actifs</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Portfolio -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-images text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Portfolio</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalPortfolioItems }}</dd>
                            <dd class="text-sm text-gray-500">projets</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Avis -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-star text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Avis</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalReviews }}</dd>
                            <dd class="text-sm text-gray-500">{{ $activeReviews }} actifs ({{ number_format($avgRating, 1) }}★)</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Appels -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-teal-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-phone text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Appels</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalPhoneCalls }}</dd>
                            <dd class="text-sm text-gray-500">{{ $todayPhoneCalls }} aujourd'hui</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Articles -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-pink-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-newspaper text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Articles</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalArticles }}</dd>
                            <dd class="text-sm text-gray-500">{{ $publishedArticles }} publiés</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Annonces -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-cyan-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-bullhorn text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Annonces</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $totalAds }}</dd>
                            <dd class="text-sm text-gray-500">{{ $publishedAds }} publiées</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques - En bas sur mobile -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Taux de conversion -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Taux de Conversion</h3>
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-blue-600">{{ $conversionRate }}%</div>
                        <div class="text-sm text-gray-500 mt-2">
                            {{ $completedSubmissions }} / {{ $totalSubmissions }} soumissions
                        </div>
                    </div>
                </div>
            </div>

            <!-- Temps moyen de complétion -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Temps Moyen de Complétion</h3>
                <div class="flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-green-600">
                            {{ $avgCompletionTime->avg_seconds > 0 ? gmdate('i:s', $avgCompletionTime->avg_seconds) : 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-500 mt-2">minutes:secondes</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lien vers statistiques détaillées (mobile) -->
        <div class="md:hidden">
            <a href="{{ route('admin.statistics') }}" 
               class="bg-white rounded-lg shadow p-6 flex items-center justify-between hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-chart-bar text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Statistiques détaillées</h3>
                        <p class="text-sm text-gray-500">Voir toutes les statistiques</p>
                    </div>
                </div>
                <i class="fas fa-chevron-right text-gray-400"></i>
            </a>
        </div>

        <!-- Abandon par étape -->
        @if($abandonmentByStep->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Abandons par Étape</h3>
            <div class="space-y-3">
                @foreach($abandonmentByStep as $step)
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">Étape {{ $step->abandoned_at_step }}</span>
                    <div class="flex items-center">
                        <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($step->count / max($totalSubmissions, 1)) * 100 }}%"></div>
                        </div>
                        <span class="text-sm text-gray-500">{{ $step->count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection




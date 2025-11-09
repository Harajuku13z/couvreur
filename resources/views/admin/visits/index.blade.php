@extends('layouts.admin')

@section('title', 'Statistiques de Visites')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <h1 class="text-xl md:text-2xl font-bold">📊 Statistiques de Visites</h1>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
            @if($showAll ?? false)
            <a href="{{ route('admin.visits') }}?days={{ $days ?? 30 }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-flag mr-2"></i>France uniquement
            </a>
            @else
            <a href="{{ route('admin.visits') }}?days={{ $days ?? 30 }}&all=1" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center">
                <i class="fas fa-globe mr-2"></i>Tous les visiteurs
            </a>
            @endif
            <select id="periodSelect" class="px-4 py-2 border border-gray-300 rounded-lg w-full sm:w-auto" onchange="changePeriod()">
                <option value="7" {{ ($days ?? 30) == 7 ? 'selected' : '' }}>7 derniers jours</option>
                <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>30 derniers jours</option>
                <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>90 derniers jours</option>
                <option value="365" {{ ($days ?? 30) == 365 ? 'selected' : '' }}>1 an</option>
            </select>
            <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition w-full sm:w-auto text-center">
                <i class="fas fa-sync-alt mr-2"></i>Actualiser
            </button>
        </div>
    </div>
    
    @if(!($showAll ?? false))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-green-600 mr-3"></i>
            <div>
                <p class="text-sm text-green-800">
                    <strong>Filtre actif : France uniquement</strong> - Affichage des statistiques pour les visiteurs de France uniquement.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-purple-600 mr-3"></i>
            <div>
                <p class="text-sm text-purple-800">
                    <strong>Affichage : Tous les pays</strong> - Statistiques pour tous les visiteurs, toutes origines confondues.
                </p>
            </div>
        </div>
    </div>
    @endif
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
            <div>
                <p class="text-sm text-blue-800">
                    <strong>Tracking interne activé</strong> - Les statistiques sont collectées directement depuis votre base de données, sans dépendre de Google Analytics.
                </p>
            </div>
        </div>
    </div>
    
    @if(isset($error))
    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-red-800 mb-2">Erreur</h3>
                <p class="text-red-700">{{ $error }}</p>
            </div>
        </div>
    </div>
    @endif
    
    @if($isConfigured)
    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 mb-1">
                        @if($showAll ?? false)
                            Visiteurs totaux (tous pays)
                        @else
                            Visiteurs France
                        @endif
                    </p>
                    <p class="text-xl md:text-3xl font-bold text-gray-900">
                        @if($showAll ?? false)
                            {{ number_format($stats['totalVisitors']) }}
                        @else
                            {{ number_format($stats['totalFranceVisitors'] ?? $stats['totalVisitors']) }}
                        @endif
                    </p>
                    @if($showAll ?? false && isset($stats['totalFranceVisitors']))
                    <p class="text-xs text-gray-500 mt-1">
                        Dont {{ number_format($stats['totalFranceVisitors']) }} de France
                    </p>
                    @endif
                </div>
                <div class="bg-blue-100 rounded-full p-2 md:p-3 flex-shrink-0 ml-2">
                    <i class="fas fa-users text-blue-600 text-xl md:text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 mb-1">Pages vues</p>
                    <p class="text-xl md:text-3xl font-bold text-gray-900">{{ number_format($stats['totalPageViews']) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-2 md:p-3 flex-shrink-0 ml-2">
                    <i class="fas fa-eye text-green-600 text-xl md:text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 mb-1">Pages vues/visiteur</p>
                    <p class="text-xl md:text-3xl font-bold text-gray-900">
                        {{ $stats['totalVisitors'] > 0 ? number_format($stats['totalPageViews'] / $stats['totalVisitors'], 2) : '0' }}
                    </p>
                </div>
                <div class="bg-purple-100 rounded-full p-2 md:p-3 flex-shrink-0 ml-2">
                    <i class="fas fa-chart-line text-purple-600 text-xl md:text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 mb-1">Période</p>
                    <p class="text-xl md:text-3xl font-bold text-gray-900">{{ $days ?? 30 }}j</p>
                </div>
                <div class="bg-orange-100 rounded-full p-2 md:p-3 flex-shrink-0 ml-2">
                    <i class="fas fa-calendar text-orange-600 text-xl md:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphique des visiteurs -->
    <div class="bg-white rounded-lg shadow p-4 md:p-6 mb-6">
        <h2 class="text-base md:text-lg font-semibold mb-4">Évolution des visiteurs</h2>
        <div class="overflow-x-auto">
            <canvas id="visitorsChart" height="100"></canvas>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-6">
        <!-- Top pages -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-base md:text-lg font-semibold mb-4">Pages les plus visitées</h2>
            <div class="space-y-3">
                       @forelse($topPages as $page)
                       <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg gap-2">
                           <div class="flex-1 min-w-0">
                               <p class="font-medium text-xs md:text-sm break-words">{{ Str::limit($page['url'] ?? $page['path'] ?? 'N/A', 50) }}</p>
                               <p class="text-xs text-gray-500">{{ number_format($page['visits'] ?? $page['pageViews'] ?? 0) }} vues</p>
                           </div>
                           <div class="text-blue-600 font-semibold flex-shrink-0">
                               {{ number_format($page['visits'] ?? $page['pageViews'] ?? 0) }}
                           </div>
                       </div>
                       @empty
                       <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                       @endforelse
            </div>
        </div>
        
        <!-- Top referrers -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-base md:text-lg font-semibold mb-4">Principales sources de trafic</h2>
            <div class="space-y-3">
                @forelse($topReferrers as $referrer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-xs md:text-sm break-words">{{ Str::limit($referrer['url'] ?? 'Direct', 50) }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($referrer['visits'] ?? $referrer['pageViews'] ?? 0) }} visites</p>
                    </div>
                    <div class="text-green-600 font-semibold flex-shrink-0">
                        {{ number_format($referrer['visits'] ?? $referrer['pageViews'] ?? 0) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
        <!-- Top browsers -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-base md:text-lg font-semibold mb-4">Navigateurs les plus utilisés</h2>
            <div class="space-y-3">
                @forelse($topBrowsers as $browser)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg gap-2">
                    <div class="flex items-center min-w-0 flex-1">
                        <i class="fas fa-globe text-blue-600 mr-3 flex-shrink-0"></i>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-xs md:text-sm break-words">{{ $browser['browser'] }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($browser['sessions']) }} sessions</p>
                        </div>
                    </div>
                    <div class="text-blue-600 font-semibold flex-shrink-0">
                        {{ number_format($browser['sessions']) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
        
        <!-- Top countries -->
        <div class="bg-white rounded-lg shadow p-4 md:p-6">
            <h2 class="text-base md:text-lg font-semibold mb-4">Pays des visiteurs</h2>
            <div class="space-y-3">
                @forelse($topCountries as $country)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg gap-2">
                    <div class="flex items-center min-w-0 flex-1">
                        <i class="fas fa-flag text-green-600 mr-3 flex-shrink-0"></i>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-xs md:text-sm break-words">{{ $country['country'] }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($country['sessions']) }} sessions</p>
                        </div>
                    </div>
                    <div class="text-green-600 font-semibold flex-shrink-0">
                        {{ number_format($country['sessions']) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
let visitorsChart = null;

function initChart() {
    const ctx = document.getElementById('visitorsChart');
    if (!ctx) return;
    
    const visitors = @json($visitors ?? []);
    
    const labels = visitors.map(item => {
        const date = new Date(item['date']);
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
    });
    
    const visitorsData = visitors.map(item => item['visitors'] || item['visits'] || 0);
    const pageViewsData = visitors.map(item => item['pageViews'] || item['visits'] || 0);
    
    visitorsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Visiteurs',
                    data: visitorsData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Pages vues',
                    data: pageViewsData,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function changePeriod() {
    const days = document.getElementById('periodSelect').value;
    const urlParams = new URLSearchParams(window.location.search);
    const allParam = urlParams.get('all');
    const url = '{{ route("admin.visits") }}?days=' + days + (allParam ? '&all=1' : '');
    window.location.href = url;
}

function refreshData() {
    const days = document.getElementById('periodSelect').value;
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualisation...';
    button.disabled = true;
    
    // Conserver le paramètre "all" si présent
    const urlParams = new URLSearchParams(window.location.search);
    const allParam = urlParams.get('all');
    const url = '{{ route("admin.visits") }}?days=' + days + (allParam ? '&all=1' : '');
    
    // Recharger la page avec les nouveaux paramètres
    window.location.href = url;
}

// Initialiser le graphique au chargement
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});
</script>
@endpush
@endsection


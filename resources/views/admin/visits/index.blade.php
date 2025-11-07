@extends('layouts.admin')

@section('title', 'Statistiques de Visites')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">📊 Statistiques de Visites</h1>
        <div class="flex items-center space-x-4">
            <select id="periodSelect" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="7">7 derniers jours</option>
                <option value="30" selected>30 derniers jours</option>
                <option value="90">90 derniers jours</option>
                <option value="365">1 an</option>
            </select>
            <button onclick="refreshData()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-sync-alt mr-2"></i>Actualiser
            </button>
        </div>
    </div>
    
    @if(!$isConfigured)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Google Analytics non configuré</h3>
                <p class="text-yellow-700 mb-4">
                    Pour afficher les statistiques de visites, vous devez configurer Google Analytics.
                </p>
                <a href="{{ route('admin.seo.index') }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-cog mr-2"></i>Configurer Google Analytics
                </a>
            </div>
        </div>
    </div>
    @endif
    
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Visiteurs totaux</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalVisitors']) }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pages vues</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['totalPageViews']) }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-eye text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Pages vues/visiteur</p>
                    <p class="text-3xl font-bold text-gray-900">
                        {{ $stats['totalVisitors'] > 0 ? number_format($stats['totalPageViews'] / $stats['totalVisitors'], 2) : '0' }}
                    </p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Période</p>
                    <p class="text-3xl font-bold text-gray-900">30j</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-calendar text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphique des visiteurs -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Évolution des visiteurs</h2>
        <canvas id="visitorsChart" height="100"></canvas>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Top pages -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Pages les plus visitées</h2>
            <div class="space-y-3">
                @forelse($topPages as $page)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p class="font-medium text-sm">{{ Str::limit($page['url'], 50) }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($page['pageViews']) }} vues</p>
                    </div>
                    <div class="text-blue-600 font-semibold">
                        {{ number_format($page['pageViews']) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
        
        <!-- Top referrers -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Principales sources de trafic</h2>
            <div class="space-y-3">
                @forelse($topReferrers as $referrer)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p class="font-medium text-sm">{{ \Illuminate\Support\Str::limit($referrer['url'], 50) }}</p>
                        <p class="text-xs text-gray-500">{{ number_format($referrer['pageViews']) }} visites</p>
                    </div>
                    <div class="text-green-600 font-semibold">
                        {{ number_format($referrer['pageViews']) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top browsers -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Navigateurs les plus utilisés</h2>
            <div class="space-y-3">
                @forelse($topBrowsers as $browser)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-globe text-blue-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-sm">{{ $browser['browser'] }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($browser['sessions']) }} sessions</p>
                        </div>
                    </div>
                    <div class="text-blue-600 font-semibold">
                        {{ number_format($browser['sessions']) }}
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-sm">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
        
        <!-- Top countries -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Pays des visiteurs</h2>
            <div class="space-y-3">
                @forelse($topCountries as $country)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-flag text-green-600 mr-3"></i>
                        <div>
                            <p class="font-medium text-sm">{{ $country['country'] }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($country['sessions']) }} sessions</p>
                        </div>
                    </div>
                    <div class="text-green-600 font-semibold">
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
    
    const visitorsData = visitors.map(item => item['visitors']);
    const pageViewsData = visitors.map(item => item['pageViews']);
    
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

function refreshData() {
    const days = document.getElementById('periodSelect').value;
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualisation...';
    button.disabled = true;
    
    fetch('{{ route("admin.visits.data") }}?days=' + days, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recharger la page pour afficher les nouvelles données
            window.location.reload();
        } else {
            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'actualisation');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Initialiser le graphique au chargement
document.addEventListener('DOMContentLoaded', function() {
    initChart();
});
</script>
@endpush
@endsection


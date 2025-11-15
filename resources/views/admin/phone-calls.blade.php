@extends('layouts.admin')

@section('title', 'Appels Téléphoniques')

@section('content')
<div class="p-4 md:p-6">
    <div class="mb-6">
        <h1 class="text-xl md:text-3xl font-bold text-gray-800">📞 Appels Téléphoniques</h1>
        <p class="text-gray-600 mt-1">Suivi des clics sur les liens téléphone</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-900">Aujourd'hui</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['today'] ?? 0 }}</p>
                </div>
                <i class="fas fa-phone text-4xl text-blue-300"></i>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-900">Cette semaine</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['this_week'] ?? 0 }}</p>
                </div>
                <i class="fas fa-phone-volume text-4xl text-green-300"></i>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-900">Ce mois</p>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $stats['this_month'] ?? 0 }}</p>
                </div>
                <i class="fas fa-calendar-alt text-4xl text-purple-300"></i>
            </div>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-900">Total</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <i class="fas fa-chart-line text-4xl text-orange-300"></i>
            </div>
        </div>
    </div>

    <!-- Call Sources Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Appels par page</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="callsByPageChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Tendance des appels (7 derniers jours)</h3>
            <div style="position: relative; height: 300px;">
                <canvas id="callsTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Test du tracking -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-blue-800 mb-1">
                    <i class="fas fa-flask mr-1"></i>Test du tracking
                </h3>
                <p class="text-xs text-blue-600">Testez si le tracking fonctionne correctement</p>
            </div>
            <button type="button" 
                    onclick="testPhoneTracking()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition w-full sm:w-auto text-center">
                <i class="fas fa-play mr-2"></i>Tester le tracking
            </button>
        </div>
    </div>

    <!-- Calls Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-4 md:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">
                <i class="fas fa-list mr-2 text-blue-500"></i>Historique des appels
            </h2>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:space-x-4 w-full sm:w-auto">
            @if($phoneCalls->total() > 0)
            <span class="text-sm text-gray-600">{{ $phoneCalls->total() }} appel(s)</span>
            @endif
                @if($phoneCalls->total() > 0)
                <button onclick="showDeleteAllModal()" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center text-sm w-full sm:w-auto justify-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Supprimer tout
                </button>
                @endif
            </div>
        </div>
        
        <!-- Vue mobile : Cartes -->
        <div class="md:hidden">
            @forelse($phoneCalls as $call)
            <div class="border-b border-gray-200 p-4">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <a href="tel:{{ $call->phone_number }}" class="text-blue-600 hover:text-blue-800 font-medium text-lg">
                                <i class="fas fa-phone mr-1"></i>{{ $call->phone_number }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-500 mb-2">
                            <i class="fas fa-calendar mr-1"></i>{{ $call->clicked_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.phone-calls.show', $call->id) }}" 
                           class="text-blue-600 hover:text-blue-900 p-2"
                           title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="showEditCityModal({{ $call->id }}, '{{ $call->city ?? '' }}')" 
                                class="text-blue-600 hover:text-blue-900 p-2"
                                title="Corriger la ville">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                
                <div class="space-y-2 text-sm">
                    @if($call->city)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-map-marker-alt w-5 text-gray-400"></i>
                        <span>{{ $call->city }}@if($call->country), {{ $call->country }}@endif</span>
                    </div>
                    @endif
                    <div class="flex items-center text-gray-600">
                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $call->source_page === 'home' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $call->source_page === 'success' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $call->source_page === 'header' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ !in_array($call->source_page, ['home', 'success', 'header']) ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($call->source_page) }}
                        </span>
                    </div>
                    @if($call->referrer_url)
                    <div class="flex items-start text-gray-600">
                        <i class="fas fa-external-link-alt w-5 text-gray-400 mt-0.5"></i>
                        <a href="{{ $call->referrer_url }}" target="_blank" 
                           class="text-blue-600 hover:text-blue-800 break-all" 
                           title="{{ $call->referrer_url }}">
                            @php
                                $referrerPath = parse_url($call->referrer_url, PHP_URL_PATH);
                                $displayUrl = $referrerPath ?: $call->referrer_url;
                            @endphp
                            {{ Str::limit($displayUrl, 50) }}
                        </a>
                    </div>
                    @endif
                    @if($call->submission_id)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-file-alt w-5 text-gray-400"></i>
                        <a href="{{ route('admin.submission.show', $call->submission_id) }}" class="text-blue-600 hover:text-blue-800">
                            Soumission #{{ $call->submission_id }}
                        </a>
                    </div>
                    @endif
                    @if($call->ip_address)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-network-wired w-5 text-gray-400"></i>
                        <span>{{ $call->ip_address }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-6 text-center">
                <i class="fas fa-phone-slash text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl text-gray-600">Aucun appel téléphonique enregistré</p>
                <p class="text-sm text-gray-500 mt-2">Les clics sur les liens téléphone seront suivis ici</p>
            </div>
            @endforelse
        </div>

        <!-- Vue desktop : Table -->
        <div class="hidden md:block overflow-x-auto table-responsive">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ville</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page d'origine</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soumission Liée</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($phoneCalls as $call)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $call->clicked_at->format('d/m/Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $call->clicked_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="tel:{{ $call->phone_number }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-phone mr-1"></i>{{ $call->phone_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($call->city)
                                <div class="text-sm font-medium text-gray-900">
                                    <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $call->city }}
                                </div>
                                @if($call->country)
                                    <div class="text-xs text-gray-500">{{ $call->country }}</div>
                                @endif
                            @else
                                <span class="text-sm text-gray-400 italic">Non renseigné</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $call->source_page === 'home' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $call->source_page === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $call->source_page === 'header' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ !in_array($call->source_page, ['home', 'success', 'header']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($call->source_page) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($call->referrer_url)
                                <a href="{{ $call->referrer_url }}" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 truncate max-w-xs block" 
                                   title="{{ $call->referrer_url }}">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    @php
                                        $referrerPath = parse_url($call->referrer_url, PHP_URL_PATH);
                                        $displayUrl = $referrerPath ?: $call->referrer_url;
                                        $displayUrl = strlen($displayUrl) > 40 ? substr($displayUrl, 0, 40) . '...' : $displayUrl;
                                    @endphp
                                    {{ $displayUrl }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($call->submission_id)
                                <a href="{{ route('admin.submission.show', $call->submission_id) }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-file-alt mr-1"></i>Soumission #{{ $call->submission_id }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $call->ip_address ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.phone-calls.show', $call->id) }}" 
                                   class="text-blue-600 hover:text-blue-900"
                                   title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="showEditCityModal({{ $call->id }}, '{{ $call->city ?? '' }}')" 
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Corriger la ville">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <i class="fas fa-phone-slash text-6xl text-gray-300 mb-4"></i>
                            <p class="text-xl text-gray-600">Aucun appel téléphonique enregistré</p>
                            <p class="text-sm text-gray-500 mt-2">Les clics sur les liens téléphone seront suivis ici</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($phoneCalls->hasPages())
        <div class="px-4 md:px-6 py-4 border-t border-gray-200">
            {{ $phoneCalls->links() }}
        </div>
        @endif
    </div>
</div>

<script>
    // Chart: Appels par page
    const callsByPageCtx = document.getElementById('callsByPageChart').getContext('2d');
    new Chart(callsByPageCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($callsByPage ?? [])) !!},
            datasets: [{
                data: {!! json_encode(array_values($callsByPage ?? [])) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)', // blue
                    'rgba(34, 197, 94, 0.8)',  // green
                    'rgba(168, 85, 247, 0.8)', // purple
                    'rgba(251, 146, 60, 0.8)', // orange
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart: Tendance
    const callsTrendCtx = document.getElementById('callsTrendChart').getContext('2d');
    new Chart(callsTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($callsTrend ?? [])) !!},
            datasets: [{
                label: 'Appels',
                data: {!! json_encode(array_values($callsTrend ?? [])) !!},
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Fonction de test du tracking
    function testPhoneTracking() {
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
        button.disabled = true;
        
        const payload = {
            source_page: window.location.pathname,
            phone_number: '{{ setting("company_phone_raw") }}',
            referrer_url: document.referrer || window.location.href
        };
        
        fetch('/api/track-phone-call', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Test réussi ! L\'appel a été tracké avec succès (ID: ' + (data.id || 'N/A') + ')\n\nRechargez la page pour voir l\'appel dans la liste.');
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('❌ Erreur lors du test: ' + error.message);
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
</script>

<!-- Modal Supprimer tout -->
<div id="deleteAllModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                    Supprimer tous les appels
                </h3>
                <button onclick="hideDeleteAllModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-700 mb-4">
                    Cette action est <strong>irréversible</strong>. Tous les appels téléphoniques seront définitivement supprimés.
                </p>
                <p class="text-sm font-semibold text-red-600 mb-2">
                    Nombre d'appels à supprimer : <span id="deleteCount">{{ $phoneCalls->total() }}</span>
                </p>
            </div>
            
            <form id="deleteAllForm" method="POST" action="{{ route('admin.phone-calls.delete-all') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe requis
                    </label>
                    <input type="password" 
                           name="password" 
                           id="deletePassword" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500" 
                           placeholder="Entrez le mot de passe"
                           required
                           autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">
                        Mot de passe requis pour confirmer cette action
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideDeleteAllModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Supprimer tout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDeleteAllModal() {
    document.getElementById('deleteAllModal').classList.remove('hidden');
    document.getElementById('deletePassword').focus();
}

function hideDeleteAllModal() {
    document.getElementById('deleteAllModal').classList.add('hidden');
    document.getElementById('deleteAllForm').reset();
}

// Gestion de la soumission du formulaire
document.getElementById('deleteAllForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('deletePassword').value;
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Suppression...';
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.reload();
        } else {
            alert('❌ ' + (data.message || 'Erreur lors de la suppression'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('❌ Erreur lors de la suppression: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

<!-- Modal correction ville -->
<div id="editCityModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 md:top-20 mx-auto p-5 border w-11/12 md:w-96 shadow-lg rounded-md bg-white modal-responsive">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                    Corriger la ville
                </h3>
                <button onclick="hideEditCityModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editCityForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="city" class="block text-sm font-medium mb-2">Ville</label>
                    <input type="text" 
                           name="city" 
                           id="editCityInput" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="Ex: Dijon"
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        Entrez la ville correcte pour cet appel téléphonique
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="hideEditCityModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showEditCityModal(callId, currentCity) {
    document.getElementById('editCityInput').value = currentCity || '';
    document.getElementById('editCityForm').action = '{{ route("admin.phone-calls.update-city", ":id") }}'.replace(':id', callId);
    document.getElementById('editCityModal').classList.remove('hidden');
    document.getElementById('editCityInput').focus();
}

function hideEditCityModal() {
    document.getElementById('editCityModal').classList.add('hidden');
    document.getElementById('editCityForm').reset();
}

document.getElementById('editCityForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = this.action;
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'PUT'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ Ville corrigée avec succès');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            alert('❌ Erreur: ' + (data.message || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('❌ Erreur lors de la correction');
    }
});
</script>
@endsection

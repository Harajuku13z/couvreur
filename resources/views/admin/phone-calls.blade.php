@extends('layouts.admin')

@section('title', 'Appels Téléphoniques')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">📞 Appels Téléphoniques</h1>
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

    <!-- Calls Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-list mr-2 text-blue-500"></i>Historique des appels
            </h2>
            @if($phoneCalls->total() > 0)
            <span class="text-sm text-gray-600">{{ $phoneCalls->total() }} appel(s)</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page Source</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Soumission Liée</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
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
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $call->source_page === 'home' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $call->source_page === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $call->source_page === 'header' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ !in_array($call->source_page, ['home', 'success', 'header']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($call->source_page) }}
                            </span>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
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
        <div class="px-6 py-4 border-t border-gray-200">
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
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Soumission Abandonnée #' . $abandonedSubmission->id)

@section('content')
<div class="p-6">
    <div class="mb-6">
        <a href="{{ route('admin.abandoned-submissions') }}" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Soumission Abandonnée #{{ $abandonedSubmission->id }}</h1>
        <p class="text-gray-600 mt-1">Session: {{ substr($abandonedSubmission->session_id, 0, 8) }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <h2 class="text-xl font-semibold text-red-900">
                        <i class="fas fa-times-circle mr-2"></i>Informations d'abandon
                    </h2>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Étape d'abandon</dt>
                            <dd class="mt-1">
                                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                                    {{ ucfirst($abandonedSubmission->current_step ?? 'N/A') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Statut</dt>
                            <dd class="mt-1 text-lg font-medium text-gray-900">
                                <span class="px-2 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">
                                    {{ $abandonedSubmission->status }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Temps passé</dt>
                            <dd class="mt-1 text-lg font-medium text-gray-900">
                                @if($abandonedSubmission->created_at && $abandonedSubmission->abandoned_at)
                                    <i class="fas fa-clock text-gray-400 mr-1"></i>
                                    {{ $abandonedSubmission->created_at->diffForHumans($abandonedSubmission->abandoned_at) }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date d'abandon</dt>
                            <dd class="mt-1 text-lg font-medium text-gray-900">
                                {{ $abandonedSubmission->abandoned_at ? $abandonedSubmission->abandoned_at->format('d/m/Y à H:i') : 'N/A' }}
                            </dd>
                        </div>
                    </dl>

                    @if($abandonedSubmission->abandon_reason)
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm font-medium text-yellow-900">Raison de l'abandon:</p>
                        <p class="mt-1 text-gray-700">{{ $abandonedSubmission->abandon_reason }}</p>
                    </div>
                    @endif
                </div>
            </div>

            @if($abandonedSubmission->form_data)
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-database mr-2 text-blue-500"></i>Données saisies
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($abandonedSubmission->form_data as $key => $value)
                            <div class="border-b border-gray-200 pb-3 last:border-0">
                                <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="mt-1 text-gray-900">
                                    @if(is_array($value))
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @foreach($value as $item)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        {{ $value ?: '-' }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-database mr-2 text-blue-500"></i>Données saisies
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Type de propriété</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->property_type ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Surface</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->surface ? $abandonedSubmission->surface . ' m²' : '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Types de travaux</dt>
                            <dd class="mt-1 text-gray-900">
                                @if($abandonedSubmission->work_types)
                                    <div class="flex flex-wrap gap-2 mt-1">
                                        @foreach($abandonedSubmission->work_types as $workType)
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-sm rounded">{{ $workType }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Statut de propriété</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->ownership_status ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Genre</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->gender ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Prénom</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->first_name ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Nom</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->last_name ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Code postal</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->postal_code ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->phone ?: '-' }}</dd>
                        </div>
                        <div class="border-b border-gray-200 pb-3 last:border-0">
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-gray-900">{{ $abandonedSubmission->email ?: '-' }}</dd>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div>
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Résumé</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">ID</dt>
                        <dd class="text-sm font-medium text-gray-900">#{{ $abandonedSubmission->id }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Session</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ substr($abandonedSubmission->session_id, 0, 8) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Utilisateur</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $abandonedSubmission->user_identifier ? substr($abandonedSubmission->user_identifier, 0, 8) : '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    <i class="fas fa-lightbulb mr-2"></i>Recommandations
                </h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                        <span>Simplifier l'étape "{{ $abandonedSubmission->current_step ?? 'N/A' }}"</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                        <span>Réduire le nombre de champs</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mr-2 mt-0.5"></i>
                        <span>Ajouter des exemples visuels</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

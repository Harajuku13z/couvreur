@extends('layouts.admin')

@section('title', 'Détails de la soumission #' . $submission->id)

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="{{ route('admin.submissions') }}" class="text-blue-600 hover:text-blue-800 text-sm mb-2 inline-block">
                <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Soumission #{{ $submission->id }}</h1>
            <p class="text-gray-600 mt-1">Créée le {{ $submission->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <div>
            <span class="px-4 py-2 rounded-lg font-semibold text-sm
                {{ $submission->status === 'COMPLETED' ? 'bg-green-100 text-green-800' : 
                   ($submission->status === 'ABANDONED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                {{ $submission->status === 'COMPLETED' ? 'Complétée' : 
                   ($submission->status === 'ABANDONED' ? 'Abandonnée' : 'En cours') }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informations personnelles -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-user mr-2 text-blue-500"></i>Informations personnelles
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Civilité</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $submission->gender ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nom complet</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">
                                {{ $submission->first_name }} {{ $submission->last_name }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-lg text-gray-900">
                                <a href="mailto:{{ $submission->email }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-envelope mr-1"></i>{{ $submission->email ?? '-' }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Téléphone</label>
                            <p class="mt-1 text-lg text-gray-900">
                                <a href="tel:{{ $submission->phone }}" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-phone mr-1"></i>{{ $submission->phone ?? '-' }}
                                </a>
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Code postal / Ville</label>
                            <p class="mt-1 text-lg text-gray-900">{{ $submission->postal_code ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails du projet -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-home mr-2 text-green-500"></i>Détails du projet
                    </h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Type de bien</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">
                                {{ $submission->property_type ? ucfirst(strtolower($submission->property_type)) : '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Surface</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ $submission->surface ?? '-' }} m²</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Statut propriétaire</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">
                                {{ $submission->ownership_status ? ucfirst(strtolower($submission->ownership_status)) : '-' }}
                            </p>
                        </div>
                    </div>

                    @if($submission->work_types)
                    <div class="mt-6">
                        <label class="text-sm font-medium text-gray-500">Types de travaux</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($submission->work_types as $workType)
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                    {{ ucfirst($workType) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($submission->roof_work_types)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Travaux de toiture</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($submission->roof_work_types as $type)
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                                    {{ ucfirst($type) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($submission->facade_work_types)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Travaux de façade</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($submission->facade_work_types as $type)
                                <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">
                                    {{ ucfirst($type) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($submission->isolation_work_types)
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Travaux d'isolation</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($submission->isolation_work_types as $type)
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                    {{ ucfirst($type) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            @php $photos = $submission->tracking_data['photos'] ?? []; @endphp
            @if(!empty($photos))
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-images mr-2 text-indigo-500"></i>Photos du projet</h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($photos as $photo)
                        <a href="{{ asset($photo) }}" target="_blank" class="block border rounded overflow-hidden">
                            <img src="{{ asset($photo) }}" alt="Photo" class="w-full h-28 object-cover">
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
            <!-- Résumé -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Résumé</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">ID Session</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ substr($submission->session_id, 0, 8) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Créée le</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $submission->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if($submission->completed_at)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Complétée le</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $submission->completed_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Durée</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ gmdate('H:i:s', $submission->completed_at->diffInSeconds($submission->created_at)) }}
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Étape actuelle</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $submission->current_step ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Informations de tracking -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>Géolocalisation
                </h3>
                <dl class="space-y-3">
                    @if($submission->ip_address)
                    <div>
                        <dt class="text-sm text-gray-500">Adresse IP</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $submission->ip_address }}</dd>
                    </div>
                    @endif
                    @if($submission->city)
                    <div>
                        <dt class="text-sm text-gray-500">Ville</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $submission->city }}</dd>
                    </div>
                    @endif
                    @if($submission->country)
                    <div>
                        <dt class="text-sm text-gray-500">Pays</dt>
                        <dd class="text-sm font-medium text-gray-900">
                            {{ $submission->country }}
                            @if($submission->country_code)
                                <span class="text-xs text-gray-500">({{ $submission->country_code }})</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Source de trafic -->
            @if($submission->referrer_url)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-link mr-2 text-blue-500"></i>Source de trafic
                </h3>
                <div>
                    <dt class="text-sm text-gray-500 mb-2">Page d'origine</dt>
                    <dd class="text-sm">
                        <a href="{{ $submission->referrer_url }}" target="_blank" 
                           class="text-blue-600 hover:text-blue-800 break-all">
                            <i class="fas fa-external-link-alt mr-1"></i>{{ $submission->referrer_url }}
                        </a>
                    </dd>
                </div>
            </div>
            @endif

            <!-- Informations techniques -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-info-circle mr-2 text-gray-500"></i>Informations techniques
                </h3>
                <dl class="space-y-3">
                    @if($submission->user_agent)
                    <div>
                        <dt class="text-sm text-gray-500">User Agent</dt>
                        <dd class="text-xs font-mono text-gray-700 break-all">{{ $submission->user_agent }}</dd>
                    </div>
                    @endif
                    @if($submission->recaptcha_score !== null)
                    <div>
                        <dt class="text-sm text-gray-500">Score reCAPTCHA</dt>
                        <dd class="text-sm font-medium {{ $submission->recaptcha_score >= 0.5 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($submission->recaptcha_score, 2) }}
                            <span class="text-xs text-gray-500">
                                ({{ $submission->recaptcha_score >= 0.5 ? 'Légitime' : 'Suspect' }})
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Actions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">Actions rapides</h3>
                <div class="space-y-3">
                    @if($submission->email)
                    <a href="mailto:{{ $submission->email }}" class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-envelope mr-2"></i>Envoyer un email
                    </a>
                    @endif
                    @if($submission->phone)
                    <a href="tel:{{ $submission->phone }}" class="block w-full text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-phone mr-2"></i>Appeler
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

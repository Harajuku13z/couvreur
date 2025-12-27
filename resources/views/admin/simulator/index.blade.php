@extends('layouts.admin')

@section('title', 'Gestion du Simulateur')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-calculator mr-3"></i>Gestion du Simulateur de Coûts
        </h1>
        <p class="text-gray-600">Sélectionnez et configurez le type de simulateur selon votre activité</p>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3 text-2xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg shadow">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-2xl"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Sélection du type de simulateur -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
            <i class="fas fa-switch-alt mr-3"></i>Sélectionner le Type de Simulateur
        </h2>

        <form method="POST" action="{{ route('admin.simulator.set-type') }}" id="type-form">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($availableTypes as $typeKey => $typeInfo)
                <label class="relative">
                    <input 
                        type="radio" 
                        name="type" 
                        value="{{ $typeKey }}" 
                        class="sr-only peer" 
                        {{ $simulatorType === $typeKey ? 'checked' : '' }}
                        onchange="document.getElementById('type-form').submit()"
                    >
                    <div class="border-2 rounded-xl p-6 cursor-pointer transition-all duration-200 
                                peer-checked:border-blue-500 peer-checked:bg-blue-50 
                                hover:border-gray-400 hover:shadow-lg
                                {{ $simulatorType === $typeKey ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <div class="flex flex-col items-center text-center">
                            <div class="text-4xl mb-4 {{ $simulatorType === $typeKey ? 'text-blue-600' : 'text-gray-400' }}">
                                <i class="{{ $typeInfo['icon'] }}"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-2">{{ $typeInfo['name'] }}</h3>
                            <p class="text-sm text-gray-600">{{ $typeInfo['description'] }}</p>
                            @if($simulatorType === $typeKey)
                            <div class="mt-3 px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-full">
                                Actif
                            </div>
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </form>
    </div>

    <!-- Actions rapides -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">
            <i class="fas fa-cog mr-3"></i>Configuration
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Configurer le simulateur actif -->
            <a href="{{ route('admin.simulator.config') }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200 text-center">
                <i class="fas fa-edit text-5xl mb-3 block"></i>
                <span class="text-xl block">Configurer</span>
                <span class="text-sm block mt-2 opacity-90">Modifier les services et tarifs</span>
            </a>

            <!-- Voir le simulateur -->
            <a href="{{ route('simulator.index') }}" 
               target="_blank"
               class="bg-green-500 hover:bg-green-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200 text-center">
                <i class="fas fa-eye text-5xl mb-3 block"></i>
                <span class="text-xl block">Voir le simulateur</span>
                <span class="text-sm block mt-2 opacity-90">Ouvrir dans un nouvel onglet</span>
            </a>

            <!-- Réinitialiser -->
            <form method="POST" action="{{ route('admin.simulator.reset-config') }}" 
                  onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser la configuration du simulateur {{ ucfirst($simulatorType) }} ? Toutes les modifications seront perdues.');">
                @csrf
                <input type="hidden" name="type" value="{{ $simulatorType }}">
                <button type="submit" 
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-8 px-6 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200">
                    <i class="fas fa-undo text-5xl mb-3 block"></i>
                    <span class="text-xl block">Réinitialiser</span>
                    <span class="text-sm block mt-2 opacity-90">Valeurs par défaut</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Informations -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 text-xl mr-3 mt-1"></i>
            <div class="text-sm text-blue-800">
                <p class="font-semibold mb-2">💡 Comment ça fonctionne ?</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Sélectionnez le type de simulateur adapté à votre activité</li>
                    <li>Chaque type a sa propre configuration avec des services spécifiques</li>
                    <li>Vous pouvez personnaliser les services et tarifs pour chaque type</li>
                    <li>Le simulateur public affichera automatiquement le type sélectionné</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection


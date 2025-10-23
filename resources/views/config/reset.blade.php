@extends('layouts.admin')

@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection



@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection


@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection



@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection


@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection



@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection


@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection



@section('title', 'Réinitialiser la Configuration')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <!-- Warning Icon -->
            <div class="flex justify-center mb-6">
                <div class="bg-red-100 rounded-full p-6">
                    <i class="fas fa-exclamation-triangle text-6xl text-red-600"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-4">
                Réinitialiser la Configuration
            </h2>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention !</strong> Cette action est <strong>irréversible</strong>.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="space-y-4 mb-8 text-gray-600">
                <p class="text-lg">
                    La réinitialisation de la configuration va :
                </p>
                
                <ul class="list-disc list-inside space-y-2 ml-4">
                    <li><strong>Supprimer TOUTES les configurations</strong> (entreprise, logo, couleurs, etc.)</li>
                    <li><strong>Supprimer les paramètres email</strong> et réseaux sociaux</li>
                    <li><strong>Supprimer les paramètres SEO</strong> (Analytics, Tag Manager)</li>
                    <li><strong>Vous déconnecter</strong> de l'administration</li>
                    <li><strong>Vous rediriger vers le wizard</strong> de configuration initiale</li>
                </ul>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Note :</strong> Les soumissions, avis et autres données ne seront <strong>PAS supprimés</strong>. Seule la configuration du site sera réinitialisée.
                    </p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('config.reset.confirm') }}" onsubmit="return confirmReset()">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Pour confirmer, tapez <span class="text-red-600 font-mono">RESET</span> ci-dessous :
                    </label>
                    <input 
                        type="text" 
                        name="confirm" 
                        id="confirmInput"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-red-500 focus:outline-none font-mono text-lg"
                        placeholder="RESET"
                        required
                    >
                    @error('confirm')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex space-x-4">
                    <a href="{{ route('config.index') }}" 
                       class="flex-1 bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400 transition font-semibold text-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Annuler
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition font-semibold">
                        <i class="fas fa-trash mr-2"></i>
                        Réinitialiser
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Additional Warning -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-shield-alt mr-1"></i>
                Cette page est protégée. Seuls les administrateurs peuvent réinitialiser la configuration.
            </p>
        </div>
    </div>
</div>

<script>
function confirmReset() {
    const input = document.getElementById('confirmInput').value;
    if (input !== 'RESET') {
        alert('Vous devez taper exactement "RESET" pour confirmer.');
        return false;
    }
    
    return confirm('Êtes-vous ABSOLUMENT sûr de vouloir réinitialiser toute la configuration ? Cette action ne peut pas être annulée.');
}
</script>
@endsection






























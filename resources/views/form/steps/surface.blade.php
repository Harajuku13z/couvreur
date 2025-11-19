@extends('layouts.app')

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

@section('title', 'Surface - Simulateur de Travaux')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Étape 2 sur 11</span>
                    <span class="text-sm font-medium text-gray-600">18%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full" style="width: 18%"></div>
                </div>
            </div>

            <!-- Question -->
            <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
                <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">
                    Quelle est la surface approximative à rénover ?
                </h2>
                
                <form method="POST" action="{{ route('form.submit', 'surface') }}">
                    @csrf
                    <div class="max-w-md mx-auto">
                        <div class="mb-6">
                            <label for="surface" class="block text-sm font-medium text-gray-700 mb-2">
                                Surface en m² *
                            </label>
                            <input type="number" 
                                   id="surface" 
                                   name="surface" 
                                   value="{{ old('surface', $submission->surface ?? '') }}"
                                   class="w-full px-4 py-3 text-lg border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                                   placeholder="100"
                                   min="1"
                                   max="10000"
                                   required
                                   autofocus>
                            <p class="text-xs text-gray-500 mt-1">Indiquez la surface totale concernée par les travaux</p>
                        </div>
                        
                        <!-- Aide -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-gray-700 text-sm flex items-start">
                                <i class="fas fa-info-circle mr-2 mt-1 text-blue-600"></i>
                                <span>Une estimation approximative suffit. Nous affinerons ensemble lors de notre échange.</span>
                            </p>
                        </div>
                    </div>

                    <!-- Navigation -->
                    <div class="flex justify-between mt-8">
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Précédent
                        </a>
                        
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Suivant
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const surfaceInput = document.getElementById('surface');

surfaceInput.addEventListener('input', function() {
    if (this.value > 0) {
        this.classList.remove('border-red-500');
        this.classList.add('border-green-500');
    } else {
        this.classList.remove('border-green-500');
        this.classList.add('border-red-500');
    }
});

console.log('✅ Étape 2 - Surface (VERSION SIMPLE)');
</script>

<style>
.border-green-500 {
    border-color: #10b981 !important;
}

.border-red-500 {
    border-color: #ef4444 !important;
}
</style>
@endsection

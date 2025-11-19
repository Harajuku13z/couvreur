@extends('layouts.admin')

@section('title', 'Configuration du Simulateur de Coûts')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-calculator mr-2"></i>
                Configuration du Simulateur de Coûts
            </h1>
            <p class="text-muted mt-2">Personnalisez les services, tarifs et options de votre simulateur</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-cog mr-2"></i>
                        Configuration Générale
                    </h5>
                    <a href="{{ route('simulator.index') }}" target="_blank" class="btn btn-light btn-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        Voir le simulateur
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.simulator.save-config') }}" method="POST" id="simulator-config-form">
                        @csrf
                        
                        <!-- Titre et description -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="title" class="form-label font-weight-bold">Titre du simulateur</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="title" 
                                    name="title" 
                                    value="{{ $config['title'] ?? '' }}" 
                                    required 
                                    maxlength="255"
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="description" class="form-label font-weight-bold">Description</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="description" 
                                    name="description" 
                                    value="{{ $config['description'] ?? '' }}" 
                                    required 
                                    maxlength="500"
                                >
                            </div>
                        </div>
                        
                        <!-- Services -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label font-weight-bold mb-0">Services et Tarifs</label>
                                <button type="button" class="btn btn-success btn-sm" onclick="addService()">
                                    <i class="fas fa-plus mr-1"></i>
                                    Ajouter un service
                                </button>
                            </div>
                            
                            <div id="services-container">
                                @foreach($config['services'] ?? [] as $index => $service)
                                <div class="service-item border rounded p-4 mb-3 bg-light" data-index="{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">
                                            <i class="fas fa-tools mr-2"></i>
                                            Service #{{ $index + 1 }}
                                        </h6>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeService(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label">ID (slug)</label>
                                            <input 
                                                type="text" 
                                                class="form-control form-control-sm" 
                                                name="services[{{ $index }}][id]" 
                                                value="{{ $service['id'] ?? '' }}" 
                                                required
                                                pattern="[a-z0-9-]+"
                                                title="Seulement lettres minuscules, chiffres et tirets"
                                            >
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Nom du service</label>
                                            <input 
                                                type="text" 
                                                class="form-control form-control-sm" 
                                                name="services[{{ $index }}][name]" 
                                                value="{{ $service['name'] ?? '' }}" 
                                                required
                                            >
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Prix/m²</label>
                                            <div class="input-group input-group-sm">
                                                <input 
                                                    type="number" 
                                                    class="form-control" 
                                                    name="services[{{ $index }}][base_cost_per_sqm]" 
                                                    value="{{ $service['base_cost_per_sqm'] ?? 50 }}" 
                                                    required 
                                                    min="0" 
                                                    step="0.01"
                                                >
                                                <span class="input-group-text">€</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Description</label>
                                            <input 
                                                type="text" 
                                                class="form-control form-control-sm" 
                                                name="services[{{ $index }}][description]" 
                                                value="{{ $service['description'] ?? '' }}" 
                                                required
                                            >
                                        </div>
                                    </div>
                                    
                                    <!-- Options additionnelles -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label mb-0 small font-weight-bold">Options additionnelles</label>
                                            <button type="button" class="btn btn-info btn-sm" onclick="addOption(this, {{ $index }})">
                                                <i class="fas fa-plus"></i> Ajouter option
                                            </button>
                                        </div>
                                        <div class="options-container" data-service-index="{{ $index }}">
                                            @foreach($service['additional_options'] ?? [] as $optIndex => $option)
                                            <div class="option-item bg-white border rounded p-2 mb-2" data-option-index="{{ $optIndex }}">
                                                <div class="row align-items-center">
                                                    <div class="col-md-2">
                                                        <input 
                                                            type="text" 
                                                            class="form-control form-control-sm" 
                                                            name="services[{{ $index }}][additional_options][{{ $optIndex }}][id]" 
                                                            value="{{ $option['id'] ?? '' }}" 
                                                            placeholder="id"
                                                            required
                                                        >
                                                    </div>
                                                    <div class="col-md-6">
                                                        <input 
                                                            type="text" 
                                                            class="form-control form-control-sm" 
                                                            name="services[{{ $index }}][additional_options][{{ $optIndex }}][name]" 
                                                            value="{{ $option['name'] ?? '' }}" 
                                                            placeholder="Nom de l'option"
                                                            required
                                                        >
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="input-group input-group-sm">
                                                            <input 
                                                                type="number" 
                                                                class="form-control" 
                                                                name="services[{{ $index }}][additional_options][{{ $optIndex }}][cost_per_sqm]" 
                                                                value="{{ $option['cost_per_sqm'] ?? 0 }}" 
                                                                required 
                                                                min="0" 
                                                                step="0.01"
                                                                placeholder="Prix/m²"
                                                            >
                                                            <span class="input-group-text">€/m²</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Disclaimers -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label font-weight-bold mb-0">Mentions légales et disclaimers</label>
                                <button type="button" class="btn btn-success btn-sm" onclick="addDisclaimer()">
                                    <i class="fas fa-plus mr-1"></i>
                                    Ajouter une mention
                                </button>
                            </div>
                            <div id="disclaimers-container">
                                @foreach($config['disclaimers'] ?? [] as $index => $disclaimer)
                                <div class="disclaimer-item mb-2">
                                    <div class="input-group">
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            name="disclaimers[]" 
                                            value="{{ $disclaimer }}" 
                                            placeholder="Mention légale..."
                                        >
                                        <button type="button" class="btn btn-danger" onclick="removeDisclaimer(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Boutons d'action -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Retour au tableau de bord
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>
                                Sauvegarder la configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let serviceIndex = {{ count($config['services'] ?? []) }};

function addService() {
    const container = document.getElementById('services-container');
    const html = `
        <div class="service-item border rounded p-4 mb-3 bg-light" data-index="${serviceIndex}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-primary">
                    <i class="fas fa-tools mr-2"></i>
                    Service #${serviceIndex + 1}
                </h6>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeService(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">ID (slug)</label>
                    <input 
                        type="text" 
                        class="form-control form-control-sm" 
                        name="services[${serviceIndex}][id]" 
                        required
                        pattern="[a-z0-9-]+"
                        placeholder="ex: toiture"
                    >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom du service</label>
                    <input 
                        type="text" 
                        class="form-control form-control-sm" 
                        name="services[${serviceIndex}][name]" 
                        required
                        placeholder="ex: Rénovation de toiture"
                    >
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prix/m²</label>
                    <div class="input-group input-group-sm">
                        <input 
                            type="number" 
                            class="form-control" 
                            name="services[${serviceIndex}][base_cost_per_sqm]" 
                            value="50" 
                            required 
                            min="0" 
                            step="0.01"
                        >
                        <span class="input-group-text">€</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Description</label>
                    <input 
                        type="text" 
                        class="form-control form-control-sm" 
                        name="services[${serviceIndex}][description]" 
                        required
                        placeholder="Description courte"
                    >
                </div>
            </div>
            
            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 small font-weight-bold">Options additionnelles</label>
                    <button type="button" class="btn btn-info btn-sm" onclick="addOption(this, ${serviceIndex})">
                        <i class="fas fa-plus"></i> Ajouter option
                    </button>
                </div>
                <div class="options-container" data-service-index="${serviceIndex}">
                    <!-- Options seront ajoutées ici -->
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    serviceIndex++;
}

function removeService(btn) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce service ?')) {
        btn.closest('.service-item').remove();
    }
}

function addOption(btn, serviceIdx) {
    const container = btn.parentElement.nextElementSibling;
    const optionCount = container.querySelectorAll('.option-item').length;
    
    const html = `
        <div class="option-item bg-white border rounded p-2 mb-2" data-option-index="${optionCount}">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <input 
                        type="text" 
                        class="form-control form-control-sm" 
                        name="services[${serviceIdx}][additional_options][${optionCount}][id]" 
                        placeholder="id"
                        required
                        pattern="[a-z0-9-]+"
                    >
                </div>
                <div class="col-md-6">
                    <input 
                        type="text" 
                        class="form-control form-control-sm" 
                        name="services[${serviceIdx}][additional_options][${optionCount}][name]" 
                        placeholder="Nom de l'option"
                        required
                    >
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <input 
                            type="number" 
                            class="form-control" 
                            name="services[${serviceIdx}][additional_options][${optionCount}][cost_per_sqm]" 
                            value="0" 
                            required 
                            min="0" 
                            step="0.01"
                            placeholder="Prix/m²"
                        >
                        <span class="input-group-text">€/m²</span>
                    </div>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeOption(btn) {
    btn.closest('.option-item').remove();
}

function addDisclaimer() {
    const container = document.getElementById('disclaimers-container');
    const html = `
        <div class="disclaimer-item mb-2">
            <div class="input-group">
                <input 
                    type="text" 
                    class="form-control" 
                    name="disclaimers[]" 
                    placeholder="Mention légale..."
                >
                <button type="button" class="btn btn-danger" onclick="removeDisclaimer(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeDisclaimer(btn) {
    btn.closest('.disclaimer-item').remove();
}
</script>
@endpush
@endsection


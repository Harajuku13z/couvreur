@extends('layouts.admin')

@section('title', 'Modifier la Réalisation - Portfolio')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">✏️ Modifier la Réalisation</h1>
                    <p class="mt-2 text-gray-600">Modifiez les informations et photos de cette réalisation</p>
                </div>
                <a href="{{ route('portfolio.admin.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Retour au Portfolio
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow">
            <form id="edit-portfolio-form" enctype="multipart/form-data" action="{{ route('portfolio.admin.update', $item['id']) }}" method="POST">
                @csrf
                @method('POST')
                
                <div class="p-6">
                    <!-- Titre -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la réalisation *</label>
                        <input type="text" name="title" id="title" value="{{ $item['title'] ?? '' }}" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Type de travaux -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type de travaux / Service *</label>
                        <select name="work_type" id="work_type" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sélectionnez un type</option>
                            @php
                                $servicesData = setting('services', '[]');
                                $allServices = is_string($servicesData) ? json_decode($servicesData, true) : ($servicesData ?? []);
                            @endphp
                            @foreach($allServices as $service)
                                <option value="{{ $service['slug'] }}" {{ ($item['work_type'] ?? '') == $service['slug'] ? 'selected' : '' }}>
                                    {{ $service['name'] }}
                                </option>
                            @endforeach
                            <option value="other" {{ ($item['work_type'] ?? '') == 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">Choisissez le service correspondant à cette réalisation</p>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description du projet</label>
                        <textarea name="description" id="description" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Décrivez les travaux réalisés, les matériaux utilisés, les défis relevés...">{{ $item['description'] ?? '' }}</textarea>
                    </div>

                    <!-- Visibilité -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_visible" id="is_visible" 
                                   {{ ($item['is_visible'] ?? true) ? 'checked' : '' }} 
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="is_visible" class="ml-2 text-sm text-gray-700">Afficher cette réalisation sur le site</label>
                        </div>
                    </div>

                    <!-- Images existantes -->
                    @if(isset($item['images']) && count($item['images']) > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Images actuelles</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($item['images'] as $index => $image)
                            <div class="relative group">
                                <img src="{{ url($image) }}" alt="Image {{ $index + 1 }}" 
                                     class="w-full h-32 object-cover rounded-lg border border-gray-200">
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                    <button type="button" onclick="removeExistingImage({{ $index }})" 
                                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                                        <i class="fas fa-trash mr-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Nouvelles images -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ajouter de nouvelles images</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer" 
                             onclick="document.getElementById('new-images').click()">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Cliquez pour sélectionner de nouvelles images</p>
                            <p class="text-sm text-gray-500">PNG, JPG, JPEG, GIF (max 2MB par image)</p>
                        </div>
                        <input type="file" id="new-images" name="images[]" multiple accept="image/*" class="hidden">
                        
                        <!-- Aperçu des nouvelles images -->
                        <div id="new-images-preview" class="mt-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 hidden"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                    <a href="{{ route('portfolio.admin.index') }}" 
                       class="px-4 py-2 text-gray-600 hover:text-gray-800 flex items-center">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-save mr-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion des nouvelles images
document.getElementById('new-images').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    const preview = document.getElementById('new-images-preview');
    
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button" onclick="removeNewImage(this)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});

function removeNewImage(button) {
    button.parentElement.remove();
}

function removeExistingImage(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        // Créer un input hidden pour marquer l'image à supprimer
        const form = document.getElementById('edit-portfolio-form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remove_images[]';
        input.value = index;
        form.appendChild(input);
        
        // Masquer l'image
        const imageContainer = event.target.closest('.relative');
        imageContainer.style.display = 'none';
    }
}

// Soumission du formulaire
document.getElementById('edit-portfolio-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Réalisation modifiée avec succès !');
            window.location.href = '{{ route("portfolio.admin.index") }}';
        } else {
            alert('Erreur : ' + (data.message || 'Erreur lors de la modification'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la modification : ' + error.message);
    });
});
</script>
@endsection









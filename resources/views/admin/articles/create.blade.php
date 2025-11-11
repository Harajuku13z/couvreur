@extends('layouts.admin')

@section('title', 'Créer un Article')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Créer un Article</h1>
        <p class="text-gray-600 mt-2">Créez un nouvel article avec le contenu HTML de ChatGPT</p>
    </div>

    <form method="POST" action="{{ route('admin.articles.store') }}" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre de l'article</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select id="status" name="status" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publié</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="content_html" class="block text-sm font-medium text-gray-700 mb-2">Contenu de l'article</label>
                <div id="content_html" style="min-height: 400px;">
                    {!! old('content_html', '') !!}
                </div>
                <textarea name="content_html" id="content_html_hidden" style="display: none;" required>{{ old('content_html') }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Utilisez l'éditeur pour formater votre contenu et ajouter des images avec leurs métadonnées SEO.</p>
                @error('content_html')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">Meta Title (optionnel)</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('meta_title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">Image mise en avant (URL)</label>
                    <input type="url" id="featured_image" name="featured_image" value="{{ old('featured_image') }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description (optionnel)</label>
                <textarea id="meta_description" name="meta_description" rows="3" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('meta_description') }}</textarea>
                @error('meta_description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords (optionnel)</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                @error('meta_keywords')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.articles.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Annuler
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Créer l'Article
            </button>
        </div>
    </form>
</div>

<!-- Modal pour upload d'image avec métadonnées -->
<div id="imageUploadModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ajouter une image</h3>
            <form id="imageUploadForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichier image</label>
                    <input type="file" id="imageFile" name="image" accept="image/*" required
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texte alternatif (Alt) *</label>
                    <input type="text" id="imageAltText" name="alt_text" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2" 
                           placeholder="Description de l'image pour le SEO" required>
                    <p class="text-xs text-gray-500 mt-1">Important pour le référencement</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mots-clés (séparés par des virgules)</label>
                    <input type="text" id="imageKeywords" name="keywords" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2" 
                           placeholder="couvreur, toiture, rénovation">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre (optionnel)</label>
                    <input type="text" id="imageTitle" name="title" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
                    <textarea id="imageDescription" name="description" rows="3" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeImageModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<!-- Quill Editor - Gratuit et open source, pas besoin de clé API -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
let articleId = null; // Sera défini lors de la création de l'article
let quill = null;

// Initialiser Quill
document.addEventListener('DOMContentLoaded', function() {
    const toolbarOptions = [
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['link', 'image'],
        ['blockquote', 'code-block'],
        ['clean']
    ];

    quill = new Quill('#content_html', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: toolbarOptions,
                handlers: {
                    'image': function() {
                        openImageModalForQuill();
                    }
                }
            }
        },
        placeholder: 'Rédigez votre article ici...',
    });

    // Pré-remplir avec le contenu existant si présent
    const hiddenTextarea = document.getElementById('content_html_hidden');
    if (hiddenTextarea && hiddenTextarea.value) {
        quill.root.innerHTML = hiddenTextarea.value;
    }

    // Synchroniser avec le textarea caché pour le formulaire
    quill.on('text-change', function() {
        if (hiddenTextarea) {
            hiddenTextarea.value = quill.root.innerHTML;
        }
    });

    // Pré-remplir l'alt text avec le titre de l'article si disponible
    const title = document.getElementById('title').value;
    if (title) {
        window.articleTitle = title;
    }
});

function openImageModalForQuill() {
    const modal = document.getElementById('imageUploadModal');
    const form = document.getElementById('imageUploadForm');
    const fileInput = document.getElementById('imageFile');
    
    // Réinitialiser le formulaire
    form.reset();
    
    // Ouvrir le sélecteur de fichier
    fileInput.click();
    
    // Quand un fichier est sélectionné, ouvrir le modal
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            showImageModal();
        }
    }, { once: true });
}

function showImageModal() {
    const modal = document.getElementById('imageUploadModal');
    
    // Pré-remplir l'alt text avec le titre de l'article si disponible
    const title = document.getElementById('title').value;
    if (title) {
        document.getElementById('imageAltText').value = title + ' - Image';
    }
    
    modal.classList.remove('hidden');
}

// Gérer la soumission du formulaire d'upload
document.getElementById('imageUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const fileInput = document.getElementById('imageFile');
    
    if (!fileInput.files || !fileInput.files[0]) {
        alert('Veuillez sélectionner une image');
        return;
    }
    
    formData.append('image', fileInput.files[0]);
    if (articleId) {
        formData.append('article_id', articleId);
    }
    
    fetch('{{ route("admin.articles.upload-image") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeImageModal();
            // Insérer l'image dans Quill avec l'alt text
            const range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', data.image_url, 'user');
            // Ajouter l'alt text comme attribut
            const imgElement = quill.root.querySelector(`img[src="${data.image_url}"]`);
            if (imgElement) {
                imgElement.setAttribute('alt', data.alt_text);
            }
            // Synchroniser avec le textarea caché
            document.getElementById('content_html_hidden').value = quill.root.innerHTML;
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'upload: ' + error.message);
    });
});

function closeImageModal() {
    document.getElementById('imageUploadModal').classList.add('hidden');
    // Réinitialiser le formulaire
    document.getElementById('imageUploadForm').reset();
}

// Mettre à jour l'alt text suggéré quand le titre change
document.getElementById('title').addEventListener('input', function(e) {
    window.articleTitle = e.target.value;
});

// Synchroniser avant la soumission du formulaire
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('content_html_hidden').value = quill.root.innerHTML;
});
</script>
<style>
/* Styles pour Quill Editor */
.ql-container {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 16px;
    min-height: 400px;
}

.ql-editor {
    min-height: 400px;
}

.ql-editor.ql-blank::before {
    font-style: normal;
    color: #999;
}
</style>
@endpush
@endsection

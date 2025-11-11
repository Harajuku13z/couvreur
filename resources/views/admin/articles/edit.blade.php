@extends('layouts.admin')

@section('title', 'Modifier l\'Article')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Modifier l'Article</h1>
        <p class="text-gray-600 mt-2">Modifiez le contenu HTML de l'article</p>
    </div>

    <form method="POST" action="{{ route('admin.articles.update', $article) }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre de l'article</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $article->title) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                    <select id="status" name="status" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Publié</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="content_html" class="block text-sm font-medium text-gray-700 mb-2">Contenu de l'article</label>
                <textarea id="content_html" name="content_html" rows="20" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                          required>{{ old('content_html', $article->content_html) }}</textarea>
                <p class="text-sm text-gray-500 mt-1">Utilisez l'éditeur pour formater votre contenu et ajouter des images avec leurs métadonnées SEO.</p>
                @error('content_html')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">Meta Title</label>
                    <input type="text" id="meta_title" name="meta_title" value="{{ old('meta_title', $article->meta_title) }}" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('meta_title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">Image mise en avant</label>
                    <input type="file" id="featured_image" name="featured_image" accept="image/*"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @if($article->featured_image)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
                            <img src="{{ asset($article->featured_image) }}" alt="Image actuelle" class="w-32 h-20 object-cover rounded">
                        </div>
                    @endif
                    @error('featured_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                <textarea id="meta_description" name="meta_description" rows="3" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('meta_description', $article->meta_description) }}</textarea>
                @error('meta_description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $article->meta_keywords) }}" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="mot-clé1, mot-clé2, mot-clé3">
                @error('meta_keywords')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.articles.show', $article) }}" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                Annuler
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Mettre à jour
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
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
let articleId = {{ $article->id }};

tinymce.init({
    selector: '#content_html',
    height: 600,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic forecolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | link image | code | help',
    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
    file_picker_types: 'image',
    images_upload_handler: function (blobInfo, progress) {
        return new Promise(function (resolve, reject) {
            // Ouvrir le modal pour upload avec métadonnées
            openImageModal(blobInfo, resolve, reject);
        });
    },
    setup: function(editor) {
        editor.on('init', function() {
            // Pré-remplir l'alt text avec le titre de l'article si disponible
            const title = document.getElementById('title').value;
            if (title) {
                window.articleTitle = title;
            }
        });
    }
});

function openImageModal(blobInfo, resolve, reject) {
    const modal = document.getElementById('imageUploadModal');
    const form = document.getElementById('imageUploadForm');
    const fileInput = document.getElementById('imageFile');
    
    // Réinitialiser le formulaire
    form.reset();
    
    // Créer un fichier à partir du blob
    const file = new File([blobInfo.blob()], blobInfo.filename(), { type: blobInfo.blob().type });
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    
    // Pré-remplir l'alt text avec le titre de l'article si disponible
    const title = document.getElementById('title').value;
    if (title) {
        document.getElementById('imageAltText').value = title + ' - Image';
    }
    
    modal.classList.remove('hidden');
    
    // Gérer la soumission du formulaire
    form.onsubmit = function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('image', fileInput.files[0]);
        formData.append('article_id', articleId);
        
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
                // Insérer l'image dans l'éditeur avec l'alt text
                const imgTag = `<img src="${data.image_url}" alt="${data.alt_text}" />`;
                tinymce.activeEditor.insertContent(imgTag);
                resolve(data.image_url);
            } else {
                alert('Erreur: ' + data.message);
                reject(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de l\'upload: ' + error.message);
            reject(error.message);
        });
    };
}

function closeImageModal() {
    document.getElementById('imageUploadModal').classList.add('hidden');
}

// Mettre à jour l'alt text suggéré quand le titre change
document.getElementById('title').addEventListener('input', function(e) {
    window.articleTitle = e.target.value;
});
</script>
@endpush
@endsection

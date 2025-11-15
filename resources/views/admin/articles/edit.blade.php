@extends('layouts.admin')

@section('title', 'Modifier l\'Article')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Modifier l'Article</h1>
        <p class="text-gray-600 mt-2">Modifiez le contenu HTML de l'article</p>
        <p class="text-xs text-blue-600 mt-1">
            <i class="fas fa-info-circle"></i> Mode debug activé - Consultez la console du navigateur (F12) pour voir les détails
        </p>
    </div>

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Erreur :</strong> {{ session('error') }}
        </div>
    @endif
    
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Erreurs de validation :</strong>
            <ul class="list-disc list-inside mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.articles.update', $article) }}" class="space-y-6" enctype="multipart/form-data" id="articleEditForm">
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
                <div class="editor-container" style="position: relative;">
                    <div id="content_html" style="min-height: 400px;">
                        {!! old('content_html', $article->content_html) !!}
                    </div>
                </div>
                <textarea name="content_html" id="content_html_hidden" style="display: none;" required>{{ old('content_html', $article->content_html) }}</textarea>
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
                    
                    <!-- Onglets pour basculer entre upload et galerie -->
                    <div class="mb-3 border-b border-gray-200">
                        <nav class="-mb-px flex space-x-4">
                            <button type="button" id="tab-upload" class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600 active-tab">
                                <i class="fas fa-upload mr-2"></i>Uploader
                            </button>
                            <button type="button" id="tab-gallery" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                                <i class="fas fa-images mr-2"></i>Galerie
                            </button>
                            <button type="button" id="tab-url" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">
                                <i class="fas fa-link mr-2"></i>URL
                            </button>
                        </nav>
                    </div>

                    <!-- Contenu onglet Upload -->
                    <div id="content-upload" class="tab-content">
                        <input type="file" id="featured_image_file" name="featured_image" accept="image/*" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               onchange="previewFeaturedImage(this)">
                    @if($article->featured_image)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
                            <img src="{{ asset($article->featured_image) }}" alt="Image actuelle" class="w-32 h-20 object-cover rounded">
                        </div>
                    @endif
                    </div>

                    <!-- Contenu onglet Galerie -->
                    <div id="content-gallery" class="tab-content hidden">
                        <div class="mb-3">
                            <input type="text" id="gallery-search" placeholder="Rechercher une image..." 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div id="gallery-loading" class="text-center py-4">
                            <i class="fas fa-spinner fa-spin text-gray-400"></i> Chargement des images...
                        </div>
                        <div id="gallery-container" class="grid grid-cols-3 gap-2 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4 hidden">
                            <!-- Les images seront chargées ici via JavaScript -->
                        </div>
                        <input type="hidden" id="featured_image_selected" name="featured_image" value="{{ old('featured_image', $article->featured_image) }}">
                    </div>

                    <!-- Contenu onglet URL -->
                    <div id="content-url" class="tab-content hidden">
                        <input type="url" id="featured_image_url" name="featured_image_url" value="{{ old('featured_image', $article->featured_image) }}" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://example.com/image.jpg">
                        <p class="text-xs text-gray-500 mt-1">Entrez l'URL complète de l'image</p>
                    </div>

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

<!-- Modal pour sélection de lien -->
<div id="linkModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white max-h-[80vh]">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Sélectionner un lien</h3>
            <div class="mb-3">
                <input type="text" id="linkSearch" placeholder="Rechercher un lien..." 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div id="linkLoading" class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-gray-400"></i> Chargement des liens...
            </div>
            <div id="linkList" class="max-h-96 overflow-y-auto space-y-1 hidden">
                <!-- Les liens seront chargés ici -->
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Ou saisir une URL personnalisée :</label>
                <input type="url" id="customLinkUrl" placeholder="https://example.com" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" onclick="closeLinkModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Annuler
                </button>
                <button type="button" onclick="insertLink()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Insérer
                </button>
            </div>
        </div>
    </div>
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
let articleId = {{ $article->id }};
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
                    },
                    'link': function(value) {
                        // Toujours ouvrir le modal pour sélectionner/insérer un lien
                        openLinkModal();
                    }
                }
            }
        },
        placeholder: 'Rédigez votre article ici...',
    });

    // Charger le contenu existant
    const existingContent = {!! json_encode(old('content_html', $article->content_html)) !!};
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }

    // Synchroniser avec le textarea pour le formulaire
    quill.on('text-change', function() {
        document.getElementById('content_html_hidden').value = quill.root.innerHTML;
    });

    // Pré-remplir l'alt text avec le titre de l'article si disponible
    const title = document.getElementById('title').value;
    if (title) {
        window.articleTitle = title;
    }
    
    // Déterminer quel onglet afficher selon l'image actuelle
    const currentImage = '{{ $article->featured_image ?? "" }}';
    if (currentImage) {
        if (currentImage.startsWith('http://') || currentImage.startsWith('https://')) {
            // C'est une URL, afficher l'onglet URL
            switchTab('url');
        } else if (currentImage.startsWith('uploads/') || currentImage.startsWith('images/')) {
            // C'est un path, afficher l'onglet galerie
            switchTab('gallery');
            // Charger la galerie pour présélectionner l'image
            setTimeout(() => {
                loadGallery();
            }, 100);
        }
        // Sinon, rester sur l'onglet upload (par défaut)
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
            // Insérer l'image dans Quill avec l'alt text
            const range = quill.getSelection(true);
            if (!range) {
                // Si pas de sélection, insérer à la fin
                range = { index: quill.getLength(), length: 0 };
            }
            
            // Insérer l'image
            quill.insertEmbed(range.index, 'image', data.image_url, 'user');
            
            // Attendre un peu pour que l'image soit insérée dans le DOM
            setTimeout(() => {
                // Trouver l'image insérée et ajouter l'alt text
                const imgElements = quill.root.querySelectorAll('img');
                imgElements.forEach(img => {
                    if (img.src === data.image_url || img.src.includes(data.image_path)) {
                        img.setAttribute('alt', data.alt_text || 'Image article');
                        img.setAttribute('loading', 'lazy');
                    }
                });
                
                // Synchroniser avec le textarea caché
                const hiddenTextarea = document.getElementById('content_html_hidden');
                if (hiddenTextarea) {
                    hiddenTextarea.value = quill.root.innerHTML;
                }
            }, 100);
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

// Synchroniser le contenu Quill en continu
quill.on('text-change', function() {
    const hiddenTextarea = document.getElementById('content_html_hidden');
    if (hiddenTextarea) {
        hiddenTextarea.value = quill.root.innerHTML;
        console.log('Quill content synchronisé (text-change), longueur:', hiddenTextarea.value.length);
    }
});

// Synchroniser avant la soumission du formulaire (première étape)
const form = document.querySelector('form');
form.addEventListener('submit', function(e) {
    const hiddenTextarea = document.getElementById('content_html_hidden');
    if (hiddenTextarea && quill) {
        hiddenTextarea.value = quill.root.innerHTML;
        console.log('Quill content synchronisé (submit - étape 1), longueur:', hiddenTextarea.value.length);
        
        // Vérifier que le contenu n'est pas vide
        if (!hiddenTextarea.value || hiddenTextarea.value.trim() === '' || hiddenTextarea.value.trim() === '<p><br></p>') {
            console.warn('ATTENTION: Le contenu HTML est vide!');
            e.preventDefault();
            alert('Le contenu de l\'article ne peut pas être vide. Veuillez ajouter du contenu.');
            return false;
        }
        
        // S'assurer que le champ a bien le nom content_html
        if (hiddenTextarea.name !== 'content_html') {
            hiddenTextarea.name = 'content_html';
            console.log('Nom du champ corrigé en content_html');
        }
    } else {
        console.error('Erreur: Quill ou textarea caché non trouvé', {
            quill: !!quill,
            textarea: !!hiddenTextarea
        });
    }
}, { capture: true }); // Utiliser capture pour exécuter en premier

// ===== GESTION DES LIENS =====
let allMenuLinks = [];
let selectedLinkUrl = null;

function openLinkModal() {
    const modal = document.getElementById('linkModal');
    const linkList = document.getElementById('linkList');
    const linkLoading = document.getElementById('linkLoading');
    const customLinkUrl = document.getElementById('customLinkUrl');
    
    // Réinitialiser
    selectedLinkUrl = null;
    customLinkUrl.value = '';
    document.getElementById('linkSearch').value = '';
    
    // Si les liens ne sont pas encore chargés, les charger
    if (allMenuLinks.length === 0) {
        linkLoading.classList.remove('hidden');
        linkList.classList.add('hidden');
        
        fetch('{{ route("admin.articles.menu-links") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allMenuLinks = data.links;
                    displayLinks(allMenuLinks);
                } else {
                    alert('Erreur lors du chargement: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors du chargement des liens');
            })
            .finally(() => {
                linkLoading.classList.add('hidden');
            });
    } else {
        displayLinks(allMenuLinks);
    }
    
    modal.classList.remove('hidden');
}

function displayLinks(links) {
    const linkList = document.getElementById('linkList');
    linkList.innerHTML = '';
    
    // Grouper par catégorie
    const grouped = {};
    links.forEach(link => {
        if (!grouped[link.category]) {
            grouped[link.category] = [];
        }
        grouped[link.category].push(link);
    });
    
    // Afficher par catégorie
    Object.keys(grouped).forEach(category => {
        const categoryDiv = document.createElement('div');
        categoryDiv.className = 'link-category';
        categoryDiv.textContent = category;
        linkList.appendChild(categoryDiv);
        
        grouped[category].forEach(link => {
            const linkDiv = document.createElement('div');
            linkDiv.className = 'link-item';
            linkDiv.setAttribute('data-url', link.url);
            linkDiv.innerHTML = `
                <div class="font-medium text-gray-900">${link.label}</div>
                <div class="text-xs text-gray-500 truncate">${link.url}</div>
            `;
            linkDiv.onclick = function() {
                // Désélectionner les autres
                document.querySelectorAll('.link-item').forEach(item => {
                    item.classList.remove('selected');
                });
                // Sélectionner celui-ci
                this.classList.add('selected');
                selectedLinkUrl = link.url;
                customLinkUrl.value = '';
            };
            linkList.appendChild(linkDiv);
        });
    });
    
    linkList.classList.remove('hidden');
}

function closeLinkModal() {
    document.getElementById('linkModal').classList.add('hidden');
    selectedLinkUrl = null;
}

function insertLink() {
    const customUrl = document.getElementById('customLinkUrl').value.trim();
    const url = customUrl || selectedLinkUrl;
    
    if (!url) {
        alert('Veuillez sélectionner un lien ou saisir une URL');
        return;
    }
    
    // Récupérer la sélection actuelle
    let range = quill.getSelection();
    if (!range) {
        // Si pas de sélection, utiliser la position du curseur
        range = { index: quill.getLength() - 1, length: 0 };
    }
    
    // Si du texte est sélectionné, le transformer en lien
    if (range.length > 0) {
        quill.formatText(range.index, range.length, 'link', url);
    } else {
        // Sinon, demander le texte du lien ou utiliser l'URL
        const linkText = prompt('Texte du lien (laisser vide pour utiliser l\'URL) :', '');
        if (linkText !== null) {
            const textToInsert = linkText || url;
            quill.insertText(range.index, textToInsert, 'link', url);
            quill.setSelection(range.index + textToInsert.length);
        }
    }
    
    closeLinkModal();
}

// Recherche dans les liens
document.getElementById('linkSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    if (searchTerm === '') {
        displayLinks(allMenuLinks);
    } else {
        const filtered = allMenuLinks.filter(link => {
            return link.label.toLowerCase().includes(searchTerm) ||
                   link.url.toLowerCase().includes(searchTerm) ||
                   link.category.toLowerCase().includes(searchTerm);
        });
        displayLinks(filtered);
    }
});

// Permettre d'utiliser Enter pour insérer le lien
document.getElementById('customLinkUrl').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        insertLink();
    }
});

// ===== GESTION DE L'IMAGE MISE EN AVANT =====
let allGalleryImages = [];
let filteredGalleryImages = [];

// Gestion des onglets
document.getElementById('tab-upload').addEventListener('click', function() {
    switchTab('upload');
});

document.getElementById('tab-gallery').addEventListener('click', function() {
    switchTab('gallery');
    loadGallery();
});

document.getElementById('tab-url').addEventListener('click', function() {
    switchTab('url');
});

function switchTab(tab) {
    // Masquer tous les contenus
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Réinitialiser tous les onglets
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600', 'active-tab');
        btn.classList.add('text-gray-500');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById('content-' + tab).classList.remove('hidden');
    
    // Activer l'onglet sélectionné
    const activeTab = document.getElementById('tab-' + tab);
    activeTab.classList.remove('text-gray-500');
    activeTab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'active-tab');
    
    // Réinitialiser le champ fichier
    const fileInput = document.getElementById('featured_image_file');
    if (tab !== 'upload') {
        if (fileInput) fileInput.disabled = true;
    } else {
        if (fileInput) fileInput.disabled = false;
    }
}

function previewFeaturedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            // Afficher l'aperçu si nécessaire
            const preview = document.getElementById('preview-featured');
            if (!preview) {
                const uploadArea = document.getElementById('upload-area-featured');
                if (uploadArea) {
                    const previewDiv = document.createElement('div');
                    previewDiv.id = 'preview-featured';
                    previewDiv.className = 'mt-2';
                    previewDiv.innerHTML = `
                        <img id="preview-featured-img" class="w-32 h-20 object-cover rounded" src="${e.target.result}">
                        <p class="text-sm text-gray-600 mt-1">Nouvelle image sélectionnée</p>
                    `;
                    uploadArea.parentNode.appendChild(previewDiv);
                }
            } else {
                document.getElementById('preview-featured-img').src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function loadGallery() {
    const container = document.getElementById('gallery-container');
    const loading = document.getElementById('gallery-loading');
    
    // Si déjà chargée, juste afficher
    if (allGalleryImages.length > 0) {
        displayGallery();
        return;
    }
    
    loading.classList.remove('hidden');
    container.classList.add('hidden');
    
    fetch('{{ route("admin.articles.images.available") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allGalleryImages = data.images;
                filteredGalleryImages = data.images;
                displayGallery();
                
                // Si une image est déjà sélectionnée, la mettre en évidence
                const currentImage = document.getElementById('featured_image_selected').value;
                if (currentImage) {
                    const matchingImage = allGalleryImages.find(img => img.path === currentImage);
                    if (matchingImage) {
                        selectGalleryImage(matchingImage.path, matchingImage.url);
                    }
                }
            } else {
                alert('Erreur lors du chargement: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement de la galerie');
        })
        .finally(() => {
            loading.classList.add('hidden');
        });
}

function displayGallery() {
    const container = document.getElementById('gallery-container');
    container.innerHTML = '';
    
    if (filteredGalleryImages.length === 0) {
        container.innerHTML = '<p class="col-span-3 text-center text-gray-500 py-8">Aucune image trouvée</p>';
        container.classList.remove('hidden');
        return;
    }
    
    filteredGalleryImages.forEach(image => {
        const div = document.createElement('div');
        div.className = 'relative cursor-pointer group';
        div.setAttribute('data-path', image.path);
        div.setAttribute('data-url', image.url);
        div.onclick = function() {
            selectGalleryImage(image.path, image.url);
        };
        
        const img = document.createElement('img');
        img.src = image.url;
        img.alt = image.name;
        img.setAttribute('data-path', image.path);
        img.className = 'w-full h-24 object-cover rounded border-2 border-transparent hover:border-blue-500 transition-all cursor-pointer';
        
        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded flex items-center justify-center pointer-events-none';
        overlay.innerHTML = '<i class="fas fa-check-circle text-white text-2xl opacity-0 group-hover:opacity-100"></i>';
        
        const nameP = document.createElement('p');
        nameP.className = 'text-xs text-gray-600 mt-1 truncate';
        nameP.textContent = image.name;
        nameP.title = image.name;
        
        const categoryP = document.createElement('p');
        categoryP.className = 'text-xs text-gray-400';
        categoryP.textContent = image.category;
        
        div.appendChild(img);
        div.appendChild(overlay);
        div.appendChild(nameP);
        div.appendChild(categoryP);
        
        container.appendChild(div);
    });
    
    container.classList.remove('hidden');
}

function selectGalleryImage(path, url) {
    document.getElementById('featured_image_selected').value = path;
    
    // Afficher un indicateur visuel
    const images = document.querySelectorAll('#gallery-container img');
    images.forEach(img => {
        img.classList.remove('border-blue-500', 'ring-2', 'ring-blue-500');
        // Trouver l'image correspondante et la mettre en évidence
        if (img.src === url || img.getAttribute('data-path') === path) {
            img.classList.add('border-blue-500', 'ring-2', 'ring-blue-500');
        }
    });
    
    // Afficher un aperçu
    showGalleryPreview(url);
}

function showGalleryPreview(url) {
    // Créer ou mettre à jour un aperçu
    let preview = document.getElementById('gallery-preview');
    if (!preview) {
        preview = document.createElement('div');
        preview.id = 'gallery-preview';
        preview.className = 'mt-4 p-4 bg-gray-50 rounded-lg';
        document.getElementById('content-gallery').appendChild(preview);
    }
    preview.innerHTML = `
        <p class="text-sm font-medium text-gray-700 mb-2">Image sélectionnée :</p>
        <img src="${url}" alt="Preview" class="max-w-full h-32 object-contain rounded">
    `;
}

// Recherche dans la galerie
document.getElementById('gallery-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    if (searchTerm === '') {
        filteredGalleryImages = allGalleryImages;
    } else {
        filteredGalleryImages = allGalleryImages.filter(image => {
            return image.name.toLowerCase().includes(searchTerm) ||
                   image.category.toLowerCase().includes(searchTerm) ||
                   image.path.toLowerCase().includes(searchTerm);
        });
    }
    displayGallery();
});

// Gérer la soumission du formulaire pour combiner les différents types d'input
document.querySelector('form').addEventListener('submit', function(e) {
    console.log('=== DEBUG FORM SUBMIT ===');
    console.log('Form action:', this.action);
    console.log('Form method:', this.method);
    
    // Synchroniser le contenu Quill avant soumission (vérification finale)
    const hiddenTextarea = document.getElementById('content_html_hidden');
    if (hiddenTextarea && quill) {
        const quillContent = quill.root.innerHTML;
        hiddenTextarea.value = quillContent;
        hiddenTextarea.name = 'content_html'; // S'assurer que le nom est correct
        console.log('Content HTML synchronisé (étape 2), longueur:', hiddenTextarea.value.length);
        
        // Vérification finale du contenu
        if (!hiddenTextarea.value || hiddenTextarea.value.trim() === '' || hiddenTextarea.value.trim() === '<p><br></p>') {
            console.error('ERREUR: Le contenu est vide après synchronisation!');
            e.preventDefault();
            alert('Le contenu de l\'article ne peut pas être vide. Veuillez ajouter du contenu.');
            return false;
        }
    } else {
        console.error('ERREUR: Quill ou textarea non disponible lors de la soumission', {
            quill: !!quill,
            textarea: !!hiddenTextarea
        });
    }
    
    // Supprimer les anciens inputs cachés s'ils existent
    const existingHidden = this.querySelectorAll('input[name="featured_image"][type="hidden"]');
    console.log('Inputs cachés existants:', existingHidden.length);
    existingHidden.forEach(input => input.remove());
    
    // Désactiver le champ fichier si on n'est pas sur l'onglet upload
    const fileInput = document.getElementById('featured_image_file');
    const galleryTab = document.getElementById('content-gallery');
    const urlTab = document.getElementById('content-url');
    const uploadTab = document.getElementById('content-upload');
    
    console.log('État des onglets:', {
        'gallery_hidden': galleryTab ? galleryTab.classList.contains('hidden') : 'N/A',
        'url_hidden': urlTab ? urlTab.classList.contains('hidden') : 'N/A',
        'upload_hidden': uploadTab ? uploadTab.classList.contains('hidden') : 'N/A',
    });
    
    // Si on est sur l'onglet galerie, utiliser le path
    if (galleryTab && !galleryTab.classList.contains('hidden')) {
        const selectedPath = document.getElementById('featured_image_selected').value;
        console.log('Onglet galerie actif, path sélectionné:', selectedPath);
        if (selectedPath) {
            // Désactiver le champ fichier pour éviter qu'il soit envoyé
            if (fileInput) {
                fileInput.disabled = true;
                fileInput.value = '';
            }
            // Créer un input caché avec le path
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'featured_image';
            hiddenInput.value = selectedPath;
            this.appendChild(hiddenInput);
            console.log('Input caché créé avec path:', selectedPath);
        }
    }
    // Si on est sur l'onglet URL, utiliser l'URL
    else if (urlTab && !urlTab.classList.contains('hidden')) {
        const url = document.getElementById('featured_image_url').value;
        console.log('Onglet URL actif, URL:', url);
        if (url) {
            // Désactiver le champ fichier pour éviter qu'il soit envoyé
            if (fileInput) {
                fileInput.disabled = true;
                fileInput.value = '';
            }
            // Créer un input caché avec l'URL
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'featured_image';
            hiddenInput.value = url;
            this.appendChild(hiddenInput);
            console.log('Input caché créé avec URL:', url);
        }
    }
    // Si on est sur l'onglet upload, le fichier est déjà dans le form
    else {
        console.log('Onglet upload actif');
        // S'assurer que le champ fichier est activé
        if (fileInput) {
            fileInput.disabled = false;
            if (fileInput.files && fileInput.files.length > 0) {
                console.log('Fichier sélectionné:', fileInput.files[0].name);
            } else {
                console.log('Aucun fichier sélectionné');
            }
        }
    }
    
    // Afficher tous les champs du formulaire avant soumission
    const formData = new FormData(this);
    console.log('=== DONNÉES DU FORMULAIRE ===');
    for (let [key, value] of formData.entries()) {
        if (key === 'content_html') {
                console.log(key + ':', value.substring(0, 100) + '... (longueur: ' + value.length + ')');
            } else if (value instanceof File) {
                console.log(key + ':', 'File - ' + value.name + ' (' + value.size + ' bytes)');
            } else {
                console.log(key + ':', value);
            }
    }
    console.log('=== FIN DEBUG ===');
});
</script>
<style>
/* Styles pour Quill Editor */
.editor-container {
    position: relative;
    border: 1px solid #ccc;
    border-radius: 0.375rem;
    background: white;
}

/* Toolbar fixe */
.editor-container .ql-toolbar {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
    border-bottom: 1px solid #ccc;
    border-radius: 0.375rem 0.375rem 0 0;
    padding: 8px;
}

/* Contenu scrollable */
.editor-container .ql-container {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 16px;
    min-height: 400px;
    max-height: 600px;
    overflow-y: auto;
}

.ql-editor {
    min-height: 400px;
    padding: 12px 15px;
}

.ql-editor.ql-blank::before {
    font-style: normal;
    color: #999;
}

/* Styles pour la liste des liens */
#linkList .link-item {
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

#linkList .link-item:hover {
    background-color: #f3f4f6;
}

#linkList .link-item.selected {
    background-color: #dbeafe;
    border-left: 3px solid #3b82f6;
}

#linkList .link-category {
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    font-weight: 600;
    margin-top: 12px;
    margin-bottom: 4px;
    padding: 0 12px;
}
</style>
@endpush
@endsection

@extends('layouts.admin')

@section('title', 'Génération d\'Articles avec IA')

@section('content')
<div class="max-w-6xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Génération d'Articles avec IA</h1>
        <a href="{{ route('admin.articles.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
        </a>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-4">
            <div class="flex items-center">
                <div id="step1-indicator" class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                <span class="ml-2 text-sm font-medium text-gray-700">Brief</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center">
                <div id="step2-indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                <span class="ml-2 text-sm font-medium text-gray-500">Titres</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center">
                <div id="step3-indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">3</div>
                <span class="ml-2 text-sm font-medium text-gray-500">Image</span>
            </div>
            <div class="w-8 h-0.5 bg-gray-300"></div>
            <div class="flex items-center">
                <div id="step4-indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">4</div>
                <span class="ml-2 text-sm font-medium text-gray-500">Contenu</span>
            </div>
        </div>
    </div>

    <!-- Step 1: Brief/Mot-clé -->
    <div id="step1" class="step-content">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Étape 1 : Brief et Mot-clé</h2>
            
            <div class="mb-6">
                <label for="keyword" class="block text-sm font-medium text-gray-700 mb-2">Mot-clé principal</label>
                <input type="text" id="keyword" name="keyword" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Ex: couvreur dijon, rénovation toiture, isolation maison...">
            </div>
            
            <div class="mb-6">
                <label for="instruction" class="block text-sm font-medium text-gray-700 mb-2">Instructions (optionnel)</label>
                <textarea id="instruction" name="instruction" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Ex: Créer des articles sur les meilleurs couvreurs de Dijon avec des conseils pratiques et des prix..."></textarea>
            </div>

            <div class="mb-6">
                <label for="articleCount" class="block text-sm font-medium text-gray-700 mb-2">Nombre d'articles à générer</label>
                <select id="articleCount" name="articleCount" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="1">1 article</option>
                    <option value="2">2 articles</option>
                    <option value="3">3 articles</option>
                    <option value="4">4 articles</option>
                    <option value="5" selected>5 articles</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button onclick="generateTitles()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-arrow-right mr-2"></i>Générer les titres
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Sélection et modification des titres -->
    <div id="step2" class="step-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Étape 2 : Sélection et modification des titres</h2>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Cochez les titres à garder et modifiez-les si nécessaire :</p>
            </div>
            
            <div id="titlesList" class="space-y-3 mb-6">
                <!-- Les titres seront ajoutés ici dynamiquement -->
            </div>

            <div class="mb-4">
                <button onclick="addCustomTitle()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-plus mr-2"></i>Ajouter un titre personnalisé
                </button>
            </div>

            <div class="flex justify-between">
                <button onclick="goToStep(1)" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Précédent
                </button>
                <button onclick="goToStep(3)" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-arrow-right mr-2"></i>Étape suivante
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3: Upload d'image -->
    <div id="step3" class="step-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Étape 3 : Image mise en avant</h2>
            
            <div class="mb-6">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <input type="file" id="imageUpload" accept="image/*" class="hidden" onchange="handleImageUpload(event)">
                    <label for="imageUpload" class="cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">Cliquez pour sélectionner une image</p>
                        <p class="text-sm text-gray-500 mt-2">PNG, JPG, GIF jusqu'à 10MB</p>
                    </label>
                </div>
            </div>

            <!-- Aperçu de l'image -->
            <div id="imagePreview" class="mt-4 hidden">
                <h5 class="text-sm font-medium text-gray-700 mb-2">Aperçu de l'image :</h5>
                <img id="previewImage" src="" alt="Aperçu" class="w-full max-w-md h-48 object-cover rounded-lg border">
            </div>

            <div class="flex justify-between mt-6">
                <button onclick="goToStep(2)" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Précédent
                </button>
                <button onclick="goToStep(4)" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-arrow-right mr-2"></i>Étape suivante
                </button>
            </div>
        </div>
    </div>

    <!-- Step 4: Génération du contenu -->
    <div id="step4" class="step-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Étape 4 : Génération du contenu</h2>
            
            <div class="mb-4">
                <label for="contentPrompt" class="block text-sm font-medium text-gray-700 mb-2">Prompt pour le contenu</label>
                <textarea id="contentPrompt" rows="6" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Instructions pour la génération du contenu...">Tu es un rédacteur web professionnel et expert en rénovation de bâtiments (toiture, isolation, plomberie, électricité, façade, etc.) et SEO.
À partir du titre fourni, rédige un article complet, structuré et optimisé SEO, sous format HTML prêt à publier, en utilisant Tailwind CSS pour que l'article soit agréable à lire.
Structure à respecter précisément :
Container principal : max-w-7xl mx-auto px-4 sm:px-6 lg:px-8
Titre principal (h1) : text-4xl font-bold text-gray-900 mb-6 text-center
Sous-titres (h2) : text-2xl font-semibold text-gray-800 my-4
Sections (div) : bg-white p-6 rounded-xl shadow mb-6 hover:shadow-lg transition duration-300
Paragraphes (p) : text-gray-700 text-base leading-relaxed mb-4
Listes à puces (ul > li) : list-disc list-inside text-gray-700 mb-2
Icônes / emojis : ajouter avant le texte ou dans les titres pour illustrer certaines sections. Exemples : toiture 🏠, jardin 🌿, énergie ⚡, peinture 🎨, sécurité 🛡️
FAQ : bg-green-50 p-4 rounded-lg mb-4, questions en gras et réponses normales
Call-to-action : bouton bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300
Contenu à générer :
Article original, informatif, entre 1 000 et 2 000 mots
Introduction engageante
Sections explicatives détaillées avec sous-titres et paragraphes
Conseils pratiques pour les propriétaires ou professionnels
FAQ pertinente sur le sujet
Conclusion avec appel à l'action pour contacter l'entreprise ou découvrir ses services
SEO et mots-clés :
Intégrer naturellement des mots-clés liés à la rénovation, toiture, façade, isolation, plomberie, électricité, énergie, maison, entretien, travaux…
Optimiser les titres et sous-titres pour le référencement
Important :
Générer directement un fichier HTML complet et propre
Ne pas afficher le code HTML comme texte brut, mais un HTML prêt à publier
Ajouter des icônes et emojis pour rendre la lecture agréable et visuelle</textarea>
            </div>

            <button onclick="generateAllArticles()" class="bg-purple-600 text-white px-6 py-2 rounded hover:bg-purple-700">
                <i class="fas fa-magic mr-2"></i>Générer tous les articles
            </button>

            <div class="flex justify-between mt-6">
                <button onclick="goToStep(3)" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-2"></i>Précédent
                </button>
            </div>
        </div>
    </div>

    <!-- Loader -->
    <div id="loader" class="hidden text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Génération en cours...</p>
    </div>
</div>

<script>
let currentStep = 1;
let generatedTitles = [];
let selectedTitles = [];
let selectedImage = '';

function goToStep(step) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
    
    // Show target step
    document.getElementById('step' + step).classList.remove('hidden');
    
    // Update indicators
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById('step' + i + '-indicator');
        if (i < step) {
            indicator.className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-bold';
        } else if (i === step) {
            indicator.className = 'w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold';
        } else {
            indicator.className = 'w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold';
        }
    }
    
    currentStep = step;
}


async function generateTitles() {
    const keyword = document.getElementById('keyword').value;
    const instruction = document.getElementById('instruction').value;
    const articleCount = document.getElementById('articleCount').value;
    
    if (!keyword.trim()) {
        alert('Veuillez saisir un mot-clé');
        return;
    }
    
    showLoader();
    
    try {
        const response = await fetch('{{ route("admin.articles.generate-titles") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                keyword: keyword,
                instruction: instruction || `Génère ${articleCount} titres d'articles SEO optimisés pour ce mot-clé`,
                count: parseInt(articleCount)
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            generatedTitles = data.titles;
            displayTitles();
            hideLoader();
            goToStep(2);
        } else {
            hideLoader();
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        hideLoader();
        alert('Erreur lors de la génération des titres: ' + error.message);
    }
}

function displayTitles() {
    const titlesList = document.getElementById('titlesList');
    titlesList.innerHTML = '';
    
    generatedTitles.forEach((title, index) => {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50';
        div.innerHTML = `
            <input type="checkbox" id="title_${index}" value="${title}" checked class="rounded">
            <input type="text" id="title_input_${index}" value="${title}" class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm" onchange="updateTitle(${index}, this.value)">
            <button onclick="removeTitle(${index})" class="text-red-600 hover:text-red-800">
                <i class="fas fa-trash"></i>
            </button>
        `;
        titlesList.appendChild(div);
    });
}

function updateTitle(index, newTitle) {
    generatedTitles[index] = newTitle;
}

function removeTitle(index) {
    generatedTitles.splice(index, 1);
    displayTitles();
}

function addCustomTitle() {
    const customTitle = prompt('Entrez votre titre personnalisé :');
    if (customTitle && customTitle.trim()) {
        generatedTitles.push(customTitle.trim());
        displayTitles();
    }
}


function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const formData = new FormData();
        formData.append('image', file);
        
        showLoader();
        
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
                selectedImage = data.image_url;
                document.getElementById('previewImage').src = selectedImage;
                document.getElementById('imagePreview').classList.remove('hidden');
                hideLoader();
            } else {
                hideLoader();
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            hideLoader();
            alert('Erreur lors de l\'upload: ' + error.message);
        });
    }
}

async function generateAllArticles() {
    // Récupérer les titres sélectionnés et modifiés
    selectedTitles = [];
    const checkboxes = document.querySelectorAll('#titlesList input[type="checkbox"]:checked');
    
    checkboxes.forEach(checkbox => {
        const index = parseInt(checkbox.id.replace('title_', ''));
        const titleInput = document.getElementById(`title_input_${index}`);
        if (titleInput && titleInput.value.trim()) {
            selectedTitles.push(titleInput.value.trim());
        }
    });
    
    if (selectedTitles.length === 0) {
        alert('Veuillez sélectionner au moins un titre');
        return;
    }
    
    showLoader();
    
    try {
        const response = await fetch('{{ route("admin.articles.create-from-titles") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                titles: selectedTitles,
                featured_image: selectedImage
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            hideLoader();
            alert(`✅ ${data.created} articles créés avec succès !`);
            window.location.href = '{{ route("admin.articles.index") }}';
        } else {
            hideLoader();
            alert('Erreur: ' + data.message);
        }
    } catch (error) {
        hideLoader();
        alert('Erreur lors de la création des articles: ' + error.message);
    }
}

function showLoader() {
    document.getElementById('loader').classList.remove('hidden');
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
}

function hideLoader() {
    document.getElementById('loader').classList.add('hidden');
    document.getElementById('step' + currentStep).classList.remove('hidden');
}
</script>
@endsection

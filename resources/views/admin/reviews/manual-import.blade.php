@extends('layouts.admin')

@section('title', 'Import Manuel d\'Avis')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Import Manuel d'Avis</h1>
                <p class="text-gray-600 mt-2">Importer des avis en format JSON</p>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux Avis
            </a>
        </div>

        <!-- Messages d'erreur/succès -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Formulaire d'import -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('admin.reviews.manual-import.save') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="reviews_json" class="block text-sm font-medium text-gray-700 mb-2">
                        Données JSON des avis
                    </label>
                    <textarea 
                        id="reviews_json" 
                        name="reviews_json" 
                        rows="15" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                        placeholder="Collez ici votre JSON d'avis..."
                        required
                    ></textarea>
                    <p class="text-sm text-gray-500 mt-1">
                        Format attendu : Un tableau JSON d'objets avis
                    </p>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.reviews.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                        Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition flex items-center">
                        <i class="fas fa-upload mr-2"></i>Importer les Avis
                    </button>
                </div>
            </form>
        </div>

        <!-- Exemple de format JSON -->
        <div class="bg-gray-50 rounded-lg p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Exemple de format JSON :</h3>
            <pre class="bg-gray-800 text-green-400 p-4 rounded-lg overflow-x-auto text-sm"><code>[
  {
    "author_name": "Jean Dupont",
    "author_location": "Paris, France",
    "author_photo_url": "https://example.com/photo.jpg",
    "rating": 5,
    "review_text": "Excellent service, je recommande !",
    "review_date": "2024-01-15 14:30:00",
    "is_active": true,
    "is_verified": true
  },
  {
    "author_name": "Marie Martin",
    "author_location": "Lyon, France",
    "rating": 4,
    "review_text": "Très bon travail, équipe professionnelle.",
    "review_date": "2024-01-10 09:15:00",
    "is_active": true,
    "is_verified": false
  }
]</code></pre>
            
            <div class="mt-4 text-sm text-gray-600">
                <h4 class="font-semibold mb-2">Champs requis :</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>author_name</strong> : Nom de l'auteur (requis)</li>
                    <li><strong>rating</strong> : Note de 1 à 5 (requis)</li>
                    <li><strong>review_text</strong> : Texte de l'avis (requis)</li>
                </ul>
                
                <h4 class="font-semibold mb-2 mt-4">Champs optionnels :</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>author_location</strong> : Localisation de l'auteur</li>
                    <li><strong>author_photo_url</strong> : URL de la photo de profil</li>
                    <li><strong>review_date</strong> : Date de l'avis (format YYYY-MM-DD HH:MM:SS)</li>
                    <li><strong>is_active</strong> : Avis actif (true/false, défaut: true)</li>
                    <li><strong>is_verified</strong> : Avis vérifié (true/false, défaut: false)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

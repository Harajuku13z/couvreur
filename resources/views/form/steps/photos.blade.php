@extends('layouts.app')

@section('title', 'Ajout de photos')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-white shadow rounded-xl p-6 md:p-8">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">Ajoutez des photos (optionnel)</h1>
            <p class="text-gray-600 mb-6">Vous pouvez ajouter 2 à 5 photos pour illustrer votre projet. Cette étape est facultative.</p>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('form.submit', 'photos') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sélectionnez jusqu'à 5 photos</label>
                    <input 
                        type="file" 
                        name="photos[]" 
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" 
                        multiple 
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-2 text-sm text-gray-500">Formats acceptés: JPG, PNG, GIF, WebP. Taille max recommandée 5MB par photo.</p>
                </div>

                @php
                    $existingPhotos = $submission->tracking_data['photos'] ?? [];
                @endphp
                @if(!empty($existingPhotos))
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Photos déjà ajoutées</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($existingPhotos as $photo)
                            <a href="{{ asset($photo) }}" target="_blank" class="block border rounded overflow-hidden">
                                <img src="{{ asset($photo) }}" alt="Photo" class="w-full h-32 object-cover">
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex items-center justify-between">
                    <a href="{{ route('form.previous', 'photos') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                        <i class="fas fa-arrow-left mr-2"></i> Précédent
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Continuer <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

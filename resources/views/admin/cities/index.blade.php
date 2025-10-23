@extends('layouts.admin')

@section('title', 'Villes')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Gestion des villes</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow p-4 mb-8">
        <h2 class="text-lg font-semibold mb-4">Importer des villes (IA)</h2>
        <div class="grid md:grid-cols-3 gap-4">
            <!-- Par département -->
            <form method="POST" action="{{ route('admin.cities.import.department') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Département</label>
                <select name="department" class="border rounded px-3 py-2 w-full" required>
                    <option value="">Sélectionner</option>
                    @foreach($departments as $dep)
                        <option value="{{ $dep }}">{{ $dep }}</option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
            
            <!-- Par région -->
            <form method="POST" action="{{ route('admin.cities.import.region') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Région</label>
                <select name="region" class="border rounded px-3 py-2 w-full" required>
                    <option value="">Sélectionner</option>
                    @foreach($regions as $reg)
                        <option value="{{ $reg }}">{{ $reg }}</option>
                    @endforeach
                </select>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
            
            <!-- Par rayon autour d'une adresse -->
            <form method="POST" action="{{ route('admin.cities.import.radius') }}" class="space-y-2">
                @csrf
                <label class="text-sm font-medium">Adresse + Rayon (km)</label>
                <input name="address" class="border rounded px-3 py-2 w-full" placeholder="12 Rue Exemple, 75000 Paris" required>
                <input name="radius_km" type="number" min="1" max="200" class="border rounded px-3 py-2 w-full" placeholder="Rayon en km" required>
                <button class="bg-blue-600 text-white rounded px-4 py-2 w-full">Importer</button>
            </form>
        </div>
        <p class="text-xs text-gray-500 mt-2">Inclut villes, communes et villages.</p>

        <div class="mt-6">
            <h3 class="text-md font-semibold mb-2">Importer depuis un JSON</h3>
            <p class="text-sm text-gray-600 mb-2">Collez ici un JSON respectant { cities: [{ name, postal_code, department, region }] } ou un tableau direct d'objets.</p>
            <form method="POST" action="{{ route('admin.cities.import.json') }}" class="space-y-2">
                @csrf
                <textarea name="json" rows="6" class="border rounded px-3 py-2 w-full font-mono text-sm" placeholder='{"cities":[{"name":"Dijon","postal_code":"21000","department":"21 Côte-d’Or","region":"Bourgogne-Franche-Comté"}]}' required></textarea>
                <button class="bg-green-600 text-white rounded px-4 py-2">Importer JSON</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.cities.store') }}" class="mb-8 grid grid-cols-1 md:grid-cols-5 gap-3">
        @csrf
        <input name="name" class="border rounded px-3 py-2" placeholder="Nom" required>
        <input name="postal_code" class="border rounded px-3 py-2" placeholder="Code postal" required>
        <input name="department" class="border rounded px-3 py-2" placeholder="Département">
        <input name="region" class="border rounded px-3 py-2" placeholder="Région">
        <button class="bg-blue-600 text-white rounded px-4">Ajouter</button>
    </form>

    <div class="flex items-center justify-between mb-2">
        <div class="text-sm text-gray-500">Total: {{ $cities->total() }}</div>
        <form method="POST" action="{{ route('admin.cities.destroy.all') }}" onsubmit="return confirm('Supprimer TOUTES les villes ? Cette action est irréversible.')">
            @csrf
            @method('DELETE')
            <button class="px-4 py-2 bg-red-600 text-white rounded">Supprimer toutes les villes</button>
        </form>
    </div>

    <div class="bg-white rounded shadow">
        <table class="min-w-full">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="p-3">Nom</th>
                    <th class="p-3">CP</th>
                    <th class="p-3">Département</th>
                    <th class="p-3">Région</th>
                    <th class="p-3">Actif</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cities as $city)
                <tr class="border-t">
                    <td class="p-3">{{ $city->name }}</td>
                    <td class="p-3">{{ $city->postal_code }}</td>
                    <td class="p-3">{{ $city->department }}</td>
                    <td class="p-3">{{ $city->region }}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.cities.update', $city) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active" value="{{ $city->active ? 0 : 1 }}">
                            <button class="px-3 py-1 rounded {{ $city->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $city->active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $cities->links() }}</div>
</div>
@endsection



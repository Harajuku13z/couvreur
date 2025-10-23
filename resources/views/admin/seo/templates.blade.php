@extends('layouts.admin')

@section('title', 'Templates SEO')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Templates de génération</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.seo.templates.store') }}" class="space-y-3 mb-8">
        @csrf
        <input name="name" class="border rounded px-3 py-2 w-full" placeholder="Nom du template" required>
        <input name="template_title" class="border rounded px-3 py-2 w-full" placeholder="Template de titre (ex: {keyword} à {ville})" required>
        <input name="template_meta" class="border rounded px-3 py-2 w-full" placeholder="Template meta description" required>
        <textarea name="content_blocks_json" class="border rounded px-3 py-2 w-full" rows="5" placeholder='JSON blocs de contenu (facultatif)'></textarea>
        <textarea name="config_json" class="border rounded px-3 py-2 w-full" rows="5" placeholder='{"longueurContenu":{"min":800,"max":1500},"densiteMotsCles":1.2}'></textarea>
        <div>
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" name="active" value="1" checked>
                <span>Actif</span>
            </label>
        </div>
        <button class="bg-blue-600 text-white rounded px-4 py-2">Enregistrer</button>
    </form>

    <div class="bg-white rounded shadow divide-y">
        @forelse($templates as $tpl)
        <div class="p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">{{ $tpl->name }}</div>
                    <div class="text-sm text-gray-600">Titre: {{ $tpl->template_title }}</div>
                    <div class="text-sm text-gray-600">Meta: {{ $tpl->template_meta }}</div>
                </div>
                <span class="text-xs px-2 py-1 rounded {{ $tpl->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $tpl->active ? 'Actif' : 'Inactif' }}</span>
            </div>
        </div>
        @empty
        <div class="p-4 text-gray-500">Aucun template pour l'instant.</div>
        @endforelse
    </div>
</div>
@endsection



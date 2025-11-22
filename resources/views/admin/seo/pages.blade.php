@extends('layouts.admin')

@section('title', 'Configuration SEO par page')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Configuration SEO par page</h1>

    <form action="{{ route('admin.seo.pages.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        @foreach($seoPages as $slug => $data)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Page: {{ ucfirst(str_replace('-', ' ', $slug)) }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="{{ $slug }}_meta_title">Titre Meta</label>
                        <input id="{{ $slug }}_meta_title" name="{{ $slug }}_meta_title" type="text" class="w-full border rounded p-2" value="{{ $data['meta'] ?? $data['meta_title'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="{{ $slug }}_meta_description">Description Meta</label>
                        <input id="{{ $slug }}_meta_description" name="{{ $slug }}_meta_description" type="text" class="w-full border rounded p-2" value="{{ $data['description'] ?? $data['meta_description'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="{{ $slug }}_og_title">OG Title</label>
                        <input id="{{ $slug }}_og_title" name="{{ $slug }}_og_title" type="text" class="w-full border rounded p-2" value="{{ $data['og_title'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="{{ $slug }}_og_description">OG Description</label>
                        <input id="{{ $slug }}_og_description" name="{{ $slug }}_og_description" type="text" class="w-full border rounded p-2" value="{{ $data['og_description'] ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="{{ $slug }}_og_image">OG Image (1200x630)</label>
                        <input id="{{ $slug }}_og_image" name="{{ $slug }}_og_image" type="file" accept="image/*" class="w-full border rounded p-2">
                        @if(!empty($data['og_image']))
                            <img src="{{ asset($data['og_image']) }}" class="mt-2 h-20" alt="OG {{ $slug }}">
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="text-center">
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded">Enregistrer</button>
        </div>
    </form>
</div>
@endsection

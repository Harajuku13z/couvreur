@extends('layouts.admin')

@section('title', 'Assistant article SEO')

@section('content')
<div class="max-w-7xl mx-auto p-4 md:py-10">
    <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Assistant de redaction SEO</p>
            <h1 class="mt-2 text-3xl md:text-4xl font-bold text-slate-950">Créer un article HTML comme sur WordPress</h1>
            <p class="mt-3 max-w-3xl text-slate-700">
                Donnez le titre, la ville, les mots-clés et vos photos. L'IA crée un article long, structuré, optimisé SEO, avec métadonnées et images intégrées.
            </p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-50">
            <i class="fas fa-arrow-left mr-2"></i>Retour aux articles
        </a>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-semibold">Corrigez ces points avant de générer l'article :</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="seoArticleForm" method="POST" action="{{ route('admin.articles.seo.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.55fr)]">
        @csrf

        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                <div class="mb-6 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Brief de l'article</h2>
                        <p class="text-sm text-slate-600">Ces informations guident le sujet, la localisation et l'intention SEO.</p>
                    </div>
                </div>

                <div class="grid gap-5">
                    <div>
                        <label for="title" class="mb-2 block text-sm font-semibold text-slate-900">Titre de l'article *</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="500"
                               class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                               placeholder="Ex: Prix d'un élagage d'arbre à Paris : guide complet">
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="city_id" class="mb-2 block text-sm font-semibold text-slate-900">Ville liée</label>
                            <select id="city_id" name="city_id"
                                    class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                <option value="">Choisir une ville enregistrée</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" @selected((string) old('city_id') === (string) $city->id)>
                                        {{ $city->name }}{{ $city->postal_code ? ' (' . $city->postal_code . ')' : '' }}{{ $city->department ? ' - ' . $city->department : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="city_name" class="mb-2 block text-sm font-semibold text-slate-900">Ville libre si absente</label>
                            <input id="city_name" name="city_name" type="text" value="{{ old('city_name') }}"
                                   class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                   placeholder="Ex: Paris, Mormant, Chartres...">
                        </div>
                    </div>

                    <div>
                        <label for="keywords" class="mb-2 block text-sm font-semibold text-slate-900">Mots-clés principaux *</label>
                        <textarea id="keywords" name="keywords" rows="5" required
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                  placeholder="Un mot-clé par ligne ou séparés par virgules : élagage paris, prix élagage arbre, artisan élagueur...">{{ old('keywords') }}</textarea>
                        <p class="mt-2 text-xs text-slate-500">Vous pouvez en mettre beaucoup. Le système les nettoie et les intègre naturellement.</p>
                    </div>

                    <div>
                        <label for="secondary_keywords" class="mb-2 block text-sm font-semibold text-slate-900">Mots-clés secondaires et longue traîne</label>
                        <textarea id="secondary_keywords" name="secondary_keywords" rows="4"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                  placeholder="Ex: quand tailler un arbre, devis élagage, entretien jardin, abattage sécurisé...">{{ old('secondary_keywords') }}</textarea>
                    </div>

                    <div>
                        <label for="brief" class="mb-2 block text-sm font-semibold text-slate-900">Consignes supplémentaires</label>
                        <textarea id="brief" name="brief" rows="5"
                                  class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                  placeholder="Ajoutez vos consignes : angle, prestations à mettre en avant, zones desservies, infos à éviter...">{{ old('brief') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-7">
                <div class="mb-6 flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                        <i class="fas fa-images"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Photos de l'article</h2>
                        <p class="text-sm text-slate-600">Ajoutez plusieurs photos. Elles seront reliées à l'article avec alt text et légendes SEO.</p>
                    </div>
                </div>

                <label for="photos" class="flex cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center hover:border-emerald-400 hover:bg-emerald-50">
                    <i class="fas fa-cloud-upload-alt text-3xl text-slate-500"></i>
                    <span class="mt-3 text-base font-semibold text-slate-900">Sélectionner plusieurs photos</span>
                    <span class="mt-1 text-sm text-slate-600">JPG, PNG, WebP, AVIF jusqu'à 10 Mo par image</span>
                    <input id="photos" name="photos[]" type="file" multiple accept="image/*" class="hidden">
                </label>

                <div id="photoPreview" class="mt-5 grid gap-4 md:grid-cols-2"></div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="sticky top-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:p-6">
                <h2 class="text-lg font-bold text-slate-950">Réglages de génération</h2>
                <p class="mt-1 text-sm text-slate-600">Choisissez la profondeur de l'article et son statut.</p>

                <div class="mt-5 space-y-5">
                    <div>
                        <label for="word_count" class="mb-2 block text-sm font-semibold text-slate-900">Longueur cible</label>
                        <select id="word_count" name="word_count" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            <option value="1200" @selected(old('word_count') === '1200')>Court solide - environ 1 200 mots</option>
                            <option value="1800" @selected(old('word_count', '1800') === '1800')>Standard SEO - environ 1 800 mots</option>
                            <option value="2500" @selected(old('word_count') === '2500')>Long complet - environ 2 500 mots</option>
                            <option value="3200" @selected(old('word_count') === '3200')>Pilier SEO - environ 3 200 mots</option>
                        </select>
                    </div>

                    <div>
                        <label for="tone" class="mb-2 block text-sm font-semibold text-slate-900">Ton éditorial</label>
                        <select id="tone" name="tone" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            <option value="expert" @selected(old('tone', 'expert') === 'expert')>Expert, rassurant</option>
                            <option value="local" @selected(old('tone') === 'local')>Local, proximité</option>
                            <option value="practical" @selected(old('tone') === 'practical')>Pratique, conseils concrets</option>
                            <option value="premium" @selected(old('tone') === 'premium')>Premium, haut de gamme</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-semibold text-slate-900">Statut</label>
                        <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-950 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Brouillon pour relecture</option>
                            <option value="published" @selected(old('status') === 'published')>Publier directement</option>
                        </select>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
                        <p class="font-semibold text-slate-950">Ce que l'IA génère</p>
                        <p class="mt-2">HTML structuré, H1/H2/H3, sommaire, FAQ visible, CTA, meta title, meta description, meta keywords, tags, temps de lecture et photos intégrées.</p>
                    </div>

                    <button id="submitButton" type="submit" class="flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-5 py-4 text-base font-bold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200">
                        <i class="fas fa-magic mr-2"></i>
                        <span>Créer l'article SEO avec l'IA</span>
                    </button>
                </div>
            </section>
        </aside>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const photosInput = document.getElementById('photos');
    const preview = document.getElementById('photoPreview');
    const form = document.getElementById('seoArticleForm');
    const submitButton = document.getElementById('submitButton');

    photosInput?.addEventListener('change', () => {
        preview.innerHTML = '';
        Array.from(photosInput.files || []).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = event => {
                const card = document.createElement('div');
                card.className = 'rounded-2xl border border-slate-200 bg-white p-3 shadow-sm';
                card.innerHTML = `
                    <img src="${event.target.result}" alt="Aperçu image ${index + 1}" class="h-40 w-full rounded-xl object-cover">
                    <div class="mt-3 space-y-2">
                        <label class="block text-xs font-semibold text-slate-700" for="photo_alt_${index}">Texte alternatif SEO</label>
                        <input id="photo_alt_${index}" name="photo_alt[${index}]" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-950" placeholder="Ex: élagage d'arbre à Paris">
                        <label class="block text-xs font-semibold text-slate-700" for="photo_caption_${index}">Légende</label>
                        <input id="photo_caption_${index}" name="photo_caption[${index}]" type="text" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-950" placeholder="Légende affichée sous l'image">
                    </div>
                `;
                preview.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    });

    form?.addEventListener('submit', () => {
        submitButton.disabled = true;
        submitButton.classList.add('opacity-75', 'cursor-not-allowed');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Génération en cours, ne fermez pas la page...</span>';
    });
});
</script>
@endsection

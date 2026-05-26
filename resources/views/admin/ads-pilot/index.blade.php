@extends('layouts.admin')

@section('title', 'Couvreur Ads Pilot')
@section('page_title', 'Couvreur Ads Pilot')

@section('content')
<div class="max-w-5xl mx-auto p-4 md:py-10">
    <div class="bg-gradient-to-br from-slate-900 to-blue-900 rounded-3xl p-6 md:p-10 text-white shadow-xl">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="inline-flex items-center rounded-full bg-emerald-400/15 px-4 py-2 text-sm font-semibold text-emerald-100 border border-emerald-300/30">
                    <i class="fas fa-shield-alt mr-2"></i>Validation humaine par défaut
                </div>
                <h1 class="mt-5 text-3xl md:text-5xl font-black leading-tight">Couvreur Ads Pilot</h1>
                <p class="mt-4 max-w-2xl text-blue-100 text-lg">
                    Application SaaS séparée pour piloter Google Ads: campagnes locales, search terms, mots-clés négatifs, recommandations IA et rapports.
                </p>
            </div>
            <div class="bg-white/10 rounded-2xl p-5 border border-white/15 min-w-[220px]">
                <div class="text-xs uppercase tracking-wider text-blue-100">Statut</div>
                @if($adsPilotUrl)
                    <div class="mt-2 text-xl font-bold text-emerald-200">URL configurée</div>
                @else
                    <div class="mt-2 text-xl font-bold text-orange-200">Non déployé ici</div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Comment y accéder ?</h2>
            @if($adsPilotUrl)
                <p class="mt-3 text-gray-700">
                    L'URL Ads Pilot est configurée. Cliquez sur le bouton ci-dessous pour ouvrir l'application.
                </p>
                <a href="{{ $adsPilotUrl }}" target="_blank" rel="noopener"
                   class="mt-5 inline-flex items-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Ouvrir Ads Pilot
                </a>
            @else
                <p class="mt-3 text-gray-700">
                    Le dossier <code class="bg-gray-100 px-2 py-1 rounded">ads/</code> contient une application Next.js séparée.
                    Elle ne peut pas apparaître comme une page Laravel classique tant qu'elle n'est pas lancée ou déployée.
                </p>
                <div class="mt-5 rounded-xl bg-gray-950 text-gray-100 p-4 text-sm overflow-x-auto">
                    <pre>cd ads
cp .env.example .env
npm install
npx prisma generate
npm run dev</pre>
                </div>
                <p class="mt-4 text-sm text-gray-600">
                    En local, ouvrez ensuite <code class="bg-gray-100 px-2 py-1 rounded">http://localhost:3000</code>.
                </p>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Pour l'afficher sur un vrai site</h2>
            <p class="mt-3 text-gray-700">
                Déployez l'app Next.js sur un sous-domaine, par exemple :
            </p>
            <div class="mt-4 rounded-xl bg-gray-50 border p-4 text-sm text-gray-800">
                <code>https://ads.votre-domaine.fr</code>
            </div>
            <p class="mt-4 text-gray-700">
                Puis ajoutez cette variable dans le <code class="bg-gray-100 px-2 py-1 rounded">.env</code> Laravel :
            </p>
            <div class="mt-4 rounded-xl bg-gray-950 text-gray-100 p-4 text-sm overflow-x-auto">
                <pre>ADS_PILOT_URL=https://ads.votre-domaine.fr
php artisan config:clear
php artisan config:cache</pre>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-emerald-50 border border-emerald-200 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-emerald-950">Pourquoi ce n'est pas directement dans Laravel ?</h2>
        <p class="mt-2 text-emerald-900">
            Ads Pilot utilise Next.js, Prisma, PostgreSQL, Auth.js et des jobs Node. C'est donc une application séparée du site PHP/Laravel.
            Le menu Laravel sert de pont pour l'ouvrir proprement une fois l'URL configurée.
        </p>
    </div>
</div>
@endsection

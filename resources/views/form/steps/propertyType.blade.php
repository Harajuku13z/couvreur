@extends('layouts.app')

@php
    $currentPage = 'form';
    if (isset($pageTitle)) {
        // SEO depuis FormControllerSimple::showStep()
    } else {
        $pageTitle = 'Simulateur de devis gratuit - ' . setting('company_name', 'Notre Entreprise');
        $pageDescription = 'Obtenez votre devis gratuit en quelques clics pour vos travaux de rénovation. Estimation rapide et gratuite.';
        $pageKeywords = 'devis gratuit, simulateur devis, estimation travaux, devis en ligne';
    }
@endphp

@section('title', $pageTitle ?? 'Simulateur de devis gratuit')

@section('description', $pageDescription ?? 'Obtenez votre devis gratuit en quelques clics.')

@section('content')
@php
    $genderVal = old('gender');
    if ($genderVal === null && !empty($submission->gender ?? null)) {
        $g = strtoupper((string) $submission->gender);
        $genderVal = in_array($g, ['M', 'MONSIEUR', 'MR'], true) ? 'M' : (in_array($g, ['MME', 'MADAME', 'MLLE'], true) ? 'Mme' : '');
    }
    $ptVal = old('property_type');
    if ($ptVal === null && !empty($submission->property_type ?? null)) {
        $p = $submission->property_type;
        $ptVal = in_array($p, ['HOUSE', 'maison', 'MAISON'], true) ? 'maison' : (in_array($p, ['APARTMENT', 'appartement', 'APPARTEMENT'], true) ? 'appartement' : '');
    }
@endphp
<div class="simulator-step min-h-screen bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Progress -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Étape 1 sur 10</span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">10%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 10%"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200/80 dark:border-slate-700 p-6 sm:p-10 mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-center mb-2 text-slate-900 dark:text-white">
                    Votre projet en quelques clics
                </h1>
                <p class="text-center text-slate-600 dark:text-slate-300 mb-8 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                    Indiquez d’abord <strong class="text-slate-800 dark:text-slate-100">comment nous vous contacter</strong> : ces informations servent uniquement à vous envoyer votre <strong class="text-slate-800 dark:text-slate-100">devis personnalisé</strong> et à vous recontacter sur votre projet.
                </p>

                @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-red-800 dark:text-red-200 text-sm">
                    <p class="font-semibold mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Veuillez corriger les champs ci-dessous :</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @error('recaptcha')
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-800 rounded-xl text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}
                </div>
                @enderror

                <form method="POST" action="{{ route('form.submit', 'propertyType') }}" id="propertyForm" class="space-y-10">
                    @csrf
                    <input type="hidden" name="recaptcha_token" id="recaptcha_token">

                    <!-- Bloc identité -->
                    <section aria-labelledby="identity-heading" class="rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-blue-50/50 dark:bg-slate-900/50 p-5 sm:p-6">
                        <h2 id="identity-heading" class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white text-sm font-bold">1</span>
                            Vos coordonnées
                        </h2>

                        <div class="space-y-5">
                            <div>
                                <p class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Civilité <span class="text-red-600">*</span></p>
                                <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                    <label for="gender_m" class="cursor-pointer group">
                                        <input type="radio" name="gender" value="M" id="gender_m" class="sr-only peer" {{ $genderVal === 'M' ? 'checked' : '' }} required>
                                        <div class="gender-option rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 p-4 text-center transition peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/40 peer-checked:shadow-md hover:border-blue-400">
                                            <i class="fas fa-male text-2xl mb-2 text-slate-700 dark:text-slate-200" aria-hidden="true"></i>
                                            <p class="font-semibold text-slate-900 dark:text-white">Monsieur</p>
                                        </div>
                                    </label>
                                    <label for="gender_mme" class="cursor-pointer group">
                                        <input type="radio" name="gender" value="Mme" id="gender_mme" class="sr-only peer" {{ $genderVal === 'Mme' ? 'checked' : '' }}>
                                        <div class="gender-option rounded-xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 p-4 text-center transition peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/40 peer-checked:shadow-md hover:border-blue-400">
                                            <i class="fas fa-female text-2xl mb-2 text-slate-700 dark:text-slate-200" aria-hidden="true"></i>
                                            <p class="font-semibold text-slate-900 dark:text-white">Madame</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">Prénom <span class="text-red-600">*</span></label>
                                    <input type="text" id="first_name" name="first_name" autocomplete="given-name" required
                                           value="{{ old('first_name', $submission->first_name ?? '') }}"
                                           class="w-full px-4 py-3 text-base text-slate-900 dark:text-white bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30 focus:outline-none placeholder:text-slate-400"
                                           placeholder="Votre prénom">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">Nom <span class="text-red-600">*</span></label>
                                    <input type="text" id="last_name" name="last_name" autocomplete="family-name" required
                                           value="{{ old('last_name', $submission->last_name ?? '') }}"
                                           class="w-full px-4 py-3 text-base text-slate-900 dark:text-white bg-white dark:bg-slate-900 border-2 border-slate-300 dark:border-slate-600 rounded-xl focus:border-blue-600 focus:ring-2 focus:ring-blue-500/30 focus:outline-none placeholder:text-slate-400"
                                           placeholder="Votre nom">
                                </div>
                            </div>

                            <div class="flex gap-3 p-4 rounded-xl bg-white/80 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-600 text-sm text-slate-700 dark:text-slate-300">
                                <i class="fas fa-shield-alt text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0" aria-hidden="true"></i>
                                <span>Données utilisées uniquement pour votre dossier de devis et la relation commerciale — jamais revendues.</span>
                            </div>
                        </div>
                    </section>

                    <!-- Type de bien -->
                    <section aria-labelledby="property-heading">
                        <h2 id="property-heading" class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-700 dark:bg-slate-600 text-white text-sm font-bold">2</span>
                            Type de bien à rénover
                        </h2>
                        <p class="text-slate-600 dark:text-slate-400 text-sm mb-4">Choisissez le bien concerné par les travaux <span class="text-red-600 font-semibold">*</span></p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label for="property_maison" class="cursor-pointer">
                                <input type="radio" name="property_type" value="maison" id="property_maison" class="sr-only peer" {{ $ptVal === 'maison' ? 'checked' : '' }} required>
                                <div class="property-option rounded-2xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 text-center transition peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/30 peer-checked:shadow-lg hover:border-blue-400 h-full">
                                    <img src="{{ asset('icons2/Maison.webp') }}" alt="" class="w-28 h-28 mx-auto mb-3 object-contain" width="112" height="112">
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Maison</h3>
                                    <p class="text-slate-600 dark:text-slate-400 text-sm">Individuelle ou mitoyenne</p>
                                </div>
                            </label>

                            <label for="property_appartement" class="cursor-pointer">
                                <input type="radio" name="property_type" value="appartement" id="property_appartement" class="sr-only peer" {{ $ptVal === 'appartement' ? 'checked' : '' }}>
                                <div class="property-option rounded-2xl border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 p-6 text-center transition peer-checked:border-blue-600 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/30 peer-checked:shadow-lg hover:border-blue-400 h-full">
                                    <img src="{{ asset('icons2/Appartement.webp') }}" alt="" class="w-28 h-28 mx-auto mb-3 object-contain" width="112" height="112">
                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Appartement</h3>
                                    <p class="text-slate-600 dark:text-slate-400 text-sm">En immeuble</p>
                                </div>
                            </label>
                        </div>
                    </section>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-4 pt-2">
                        <a href="{{ url('/') }}"
                           class="inline-flex justify-center items-center bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-white px-6 py-3 rounded-xl font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition">
                            <i class="fas fa-arrow-left mr-2"></i> Retour
                        </a>
                        <button type="submit" id="submitBtn"
                                class="inline-flex justify-center items-center bg-blue-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-600/25">
                            Continuer
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updatePropertySelection(radio) {
    document.querySelectorAll('#propertyForm .property-option').forEach(function (opt) {
        opt.classList.remove('ring-2', 'ring-blue-500');
    });
    var option = radio.closest('label').querySelector('.property-option');
    if (option) option.classList.add('ring-2', 'ring-blue-500');
}

function updateGenderVisual(radio) {
    document.querySelectorAll('#propertyForm .gender-option').forEach(function (opt) {
        opt.classList.remove('ring-2', 'ring-blue-500');
    });
    var option = radio.closest('label').querySelector('.gender-option');
    if (option) option.classList.add('ring-2', 'ring-blue-500');
}

document.querySelectorAll('input[name="property_type"]').forEach(function (radio) {
    radio.addEventListener('change', function () { updatePropertySelection(this); });
});
document.querySelectorAll('input[name="gender"]').forEach(function (radio) {
    radio.addEventListener('change', function () { updateGenderVisual(this); });
});

document.querySelectorAll('input[name="property_type"]:checked').forEach(function (r) { updatePropertySelection(r); });
document.querySelectorAll('input[name="gender"]:checked').forEach(function (r) { updateGenderVisual(r); });

function submitPropertyForm() {
    var form = document.getElementById('propertyForm');
    var recaptchaTokenInput = document.getElementById('recaptcha_token');
    var submitBtn = document.getElementById('submitBtn');
    var recaptchaSiteKey = '{{ setting("recaptcha_site_key") }}';

    if (recaptchaSiteKey) {
        if (typeof grecaptcha === 'undefined') {
            setTimeout(function () {
                if (typeof grecaptcha !== 'undefined') submitPropertyForm();
                else form.submit();
            }, 800);
            return;
        }
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Vérification...';
        }
        grecaptcha.ready(function () {
            grecaptcha.execute(recaptchaSiteKey, { action: 'submit' }).then(function (token) {
                recaptchaTokenInput.value = token || '';
                form.submit();
            }).catch(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Continuer <i class="fas fa-arrow-right ml-2"></i>';
                }
                form.submit();
            });
        });
    } else {
        form.submit();
    }
}

document.getElementById('propertyForm').addEventListener('submit', function (e) {
    e.preventDefault();
    submitPropertyForm();
});
</script>

@include('form.partials.recaptcha')

<style>
.property-option, .gender-option { transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease; }
.property-option:hover, .gender-option:hover { transform: translateY(-2px); }
</style>
@endsection

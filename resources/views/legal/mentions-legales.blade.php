@extends('layouts.app')

@section('title', 'Mentions Légales - ' . $companyName)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Mentions Légales</h1>
            
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Éditeur du site</h2>
                <p class="mb-6">
                    Le présent site est édité par <strong>{{ $companyName }}</strong>.
                    @if($companySiret)
                    <br>SIRET : {{ $companySiret }}
                    @endif
                    @if($companyRcs)
                    <br>RCS : {{ $companyRcs }}
                    @endif
                    @if($companyCapital)
                    <br>Capital social : {{ $companyCapital }}
                    @endif
                    @if($companyTva)
                    <br>TVA intracommunautaire : {{ $companyTva }}
                    @endif
                </p>
                
                @if($companyAddress)
                <p class="mb-6">
                    <strong>Adresse :</strong><br>
                    {{ $companyAddress }}
                </p>
                @endif
                
                @if($companyPhone)
                <p class="mb-6">
                    <strong>Téléphone :</strong> {{ $companyPhone }}
                </p>
                @endif
                
                @if($companyEmail)
                <p class="mb-6">
                    <strong>Email :</strong> {{ $companyEmail }}
                </p>
                @endif
                
                @if($directorName)
                <p class="mb-6">
                    <strong>Directeur de la publication :</strong> {{ $directorName }}
                </p>
                @endif
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. Hébergement</h2>
                @if($hostingProvider)
                <p class="mb-6">
                    Le site est hébergé par {{ $hostingProvider }}.
                </p>
                @else
                <p class="mb-6">
                    Le site est hébergé par un prestataire professionnel.
                </p>
                @endif
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. Propriété intellectuelle</h2>
                <p class="mb-6">
                    L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Collecte et traitement des données</h2>
                <p class="mb-6">
                    Les informations recueillies sur ce site font l'objet d'un traitement informatique destiné à {{ $companyName }}. Conformément à la loi "informatique et libertés" du 6 janvier 1978 modifiée, vous disposez d'un droit d'accès et de rectification aux informations qui vous concernent.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Cookies</h2>
                <p class="mb-6">
                    Ce site utilise des cookies pour améliorer votre expérience de navigation et analyser le trafic. En continuant à utiliser ce site, vous acceptez notre utilisation des cookies.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Responsabilité</h2>
                <p class="mb-6">
                    Les informations contenues sur ce site sont aussi précises que possible et le site remis à jour à différentes périodes de l'année, mais peut toutefois contenir des inexactitudes ou des omissions. Si vous constatez une lacune, erreur ou ce qui parait être un dysfonctionnement, merci de bien vouloir le signaler par email, à l'adresse {{ $companyEmail ?? 'contact@exemple.com' }}, en décrivant le problème de la manière la plus précise possible.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Droit applicable</h2>
                <p class="mb-6">
                    Tout litige en relation avec l'utilisation du site est soumis au droit français. Il est fait attribution exclusive de juridiction aux tribunaux compétents de Paris.
                </p>
                
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <strong>Dernière mise à jour :</strong> {{ date('d/m/Y') }}
                    </p>
                </div>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 mt-8">8. Crédits</h2>
                <p class="mb-6">
                    Ce site web a été créé par <a href="https://www.osmoseconsulting.fr" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors font-medium">Osmose*</a> avec amour ❤️
                </p>
            </div>
        </div>
    </div>
</div>
@endsection


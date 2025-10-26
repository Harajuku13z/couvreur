@extends('layouts.app')

@section('title', 'Politique de Confidentialité - ' . $companyName)

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Politique de Confidentialité</h1>
            
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">1. Introduction</h2>
                <p class="mb-6">
                    {{ $companyName }} s'engage à protéger votre vie privée et vos données personnelles. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations personnelles lorsque vous utilisez notre site web et nos services.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">2. Responsable du traitement</h2>
                <p class="mb-6">
                    <strong>{{ $companyName }}</strong><br>
                    @if($companyAddress)
                    {{ $companyAddress }}<br>
                    @endif
                    @if($companyPhone)
                    Téléphone : {{ $companyPhone }}<br>
                    @endif
                    @if($companyEmail)
                    Email : {{ $companyEmail }}
                    @endif
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">3. Données collectées</h2>
                <p class="mb-4">Nous collectons les types de données suivants :</p>
                <ul class="list-disc pl-6 mb-6">
                    <li><strong>Données d'identification :</strong> nom, prénom, adresse email, numéro de téléphone</li>
                    <li><strong>Données de localisation :</strong> adresse postale, code postal</li>
                    <li><strong>Données de navigation :</strong> adresse IP, cookies, pages visitées</li>
                    <li><strong>Données de contact :</strong> communications avec notre service client</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">4. Finalités du traitement</h2>
                <p class="mb-4">Nous utilisons vos données personnelles pour :</p>
                <ul class="list-disc pl-6 mb-6">
                    <li>Fournir nos services de couverture et rénovation</li>
                    <li>Répondre à vos demandes de devis</li>
                    <li>Vous contacter concernant nos services</li>
                    <li>Améliorer notre site web et nos services</li>
                    <li>Respecter nos obligations légales</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">5. Base légale du traitement</h2>
                <p class="mb-6">
                    Le traitement de vos données personnelles est basé sur :
                </p>
                <ul class="list-disc pl-6 mb-6">
                    <li><strong>Votre consentement</strong> pour l'envoi de communications marketing</li>
                    <li><strong>L'exécution d'un contrat</strong> pour la fourniture de nos services</li>
                    <li><strong>Notre intérêt légitime</strong> pour l'amélioration de nos services</li>
                    <li><strong>Le respect d'obligations légales</strong> (facturation, comptabilité)</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">6. Partage des données</h2>
                <p class="mb-6">
                    Nous ne vendons, ne louons ni ne partageons vos données personnelles avec des tiers, sauf dans les cas suivants :
                </p>
                <ul class="list-disc pl-6 mb-6">
                    <li>Avec votre consentement explicite</li>
                    <li>Pour respecter une obligation légale</li>
                    <li>Avec nos prestataires de services (sous contrat de confidentialité)</li>
                    <li>En cas de fusion ou d'acquisition de notre entreprise</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">7. Conservation des données</h2>
                <p class="mb-6">
                    Nous conservons vos données personnelles pendant la durée nécessaire aux finalités pour lesquelles elles ont été collectées :
                </p>
                <ul class="list-disc pl-6 mb-6">
                    <li><strong>Données clients :</strong> 3 ans après la fin de la relation contractuelle</li>
                    <li><strong>Données de prospection :</strong> 3 ans après le dernier contact</li>
                    <li><strong>Données de navigation :</strong> 13 mois maximum</li>
                    <li><strong>Données comptables :</strong> 10 ans (obligation légale)</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">8. Vos droits</h2>
                <p class="mb-4">Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul class="list-disc pl-6 mb-6">
                    <li><strong>Droit d'accès :</strong> obtenir une copie de vos données</li>
                    <li><strong>Droit de rectification :</strong> corriger vos données inexactes</li>
                    <li><strong>Droit d'effacement :</strong> demander la suppression de vos données</li>
                    <li><strong>Droit à la limitation :</strong> restreindre le traitement de vos données</li>
                    <li><strong>Droit à la portabilité :</strong> récupérer vos données dans un format structuré</li>
                    <li><strong>Droit d'opposition :</strong> vous opposer au traitement de vos données</li>
                </ul>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">9. Cookies</h2>
                <p class="mb-6">
                    Notre site utilise des cookies pour améliorer votre expérience de navigation. Vous pouvez gérer vos préférences de cookies dans les paramètres de votre navigateur.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">10. Contact</h2>
                <p class="mb-6">
                    Pour toute question concernant cette politique de confidentialité ou pour exercer vos droits, contactez-nous :
                </p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p><strong>{{ $companyName }}</strong></p>
                    @if($companyAddress)
                    <p>{{ $companyAddress }}</p>
                    @endif
                    @if($companyPhone)
                    <p>Téléphone : {{ $companyPhone }}</p>
                    @endif
                    @if($companyEmail)
                    <p>Email : {{ $companyEmail }}</p>
                    @endif
                </div>
                
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <strong>Dernière mise à jour :</strong> {{ date('d/m/Y') }}
                    </p>
                </div>
                
                <h2 class="text-2xl font-semibold text-gray-800 mb-4 mt-8">11. Crédits</h2>
                <p class="mb-6">
                    Ce site web a été créé par <a href="https://www.osmoseconsulting.fr" target="_blank" class="text-blue-600 hover:text-blue-800 transition-colors font-medium">Osmose*</a> avec amour ❤️
                </p>
            </div>
        </div>
    </div>
</div>
@endsection


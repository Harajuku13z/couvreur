@extends('layouts.app')

@section('title', 'Accès restreint - ' . setting('company_name', 'Notre Entreprise'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-100 flex items-center justify-center px-4 py-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-lg shadow-xl p-8 md:p-12 text-center">
            <!-- Icône de blocage -->
            <div class="mb-6">
                <div class="mx-auto w-24 h-24 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-globe text-red-600 text-5xl"></i>
                </div>
            </div>
            
            <!-- Titre -->
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                Accès restreint
            </h1>
            
            <!-- Message principal -->
            <div class="mb-6">
                <p class="text-lg text-gray-700 mb-4">
                    Nous sommes désolés, mais notre service de devis en ligne est actuellement disponible uniquement pour les résidents de <strong>France</strong>.
                </p>
                <p class="text-gray-600">
                    Votre localisation détectée : <span class="font-semibold text-gray-800">{{ $country }}</span>
                </p>
            </div>
            
            <!-- Informations -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-blue-800 mb-3">
                    <i class="fas fa-info-circle mr-2"></i>Pourquoi cette restriction ?
                </h2>
                <p class="text-sm text-gray-700 text-left">
                    {{ setting('company_name', 'Notre entreprise') }} opère exclusivement en France. 
                    Nous ne pouvons malheureusement pas proposer nos services en dehors de notre zone d'intervention.
                </p>
            </div>
            
            <!-- Options de contact -->
            <div class="space-y-4">
                <div>
                    <h3 class="text-md font-semibold text-gray-800 mb-3">
                        Vous souhaitez tout de même nous contacter ?
                    </h3>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        @if(setting('company_phone'))
                        <a href="tel:{{ setting('company_phone_raw') ?? setting('company_phone') }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                            <i class="fas fa-phone mr-2"></i>
                            Nous appeler
                        </a>
                        @endif
                        
                        @if(setting('company_email'))
                        <a href="mailto:{{ setting('company_email') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center justify-center">
                            <i class="fas fa-envelope mr-2"></i>
                            Nous écrire
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Bouton retour -->
                <div class="pt-4 border-t border-gray-200">
                    <a href="{{ url('/') }}" 
                       class="inline-flex items-center text-gray-600 hover:text-gray-800 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour à l'accueil
                    </a>
                </div>
            </div>
            
            <!-- Message de contact -->
            @if(setting('company_email'))
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-envelope mr-2 text-gray-400"></i>
                    Pour toute question, n'hésitez pas à nous contacter à 
                    <a href="mailto:{{ setting('company_email') }}" class="text-blue-600 hover:underline">
                        {{ setting('company_email') }}
                    </a>
                </p>
            </div>
            @endif
        </div>
        
        <!-- Informations de l'entreprise -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>{{ setting('company_name', 'Notre Entreprise') }}</p>
            @if(setting('company_address') && setting('company_city'))
            <p>
                {{ setting('company_address') }}, 
                {{ setting('company_postal_code') }} {{ setting('company_city') }}, 
                {{ setting('company_country', 'France') }}
            </p>
            @endif
        </div>
    </div>
</div>
@endsection


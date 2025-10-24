@extends('layouts.app')

@section('title', 'Prestations - Nettoyage de Toiture')

@section('content')
<div class="min-h-screen bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <!-- En-tête -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-home text-blue-600 mr-3"></i>
                    Nettoyage de Toiture
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Nos services professionnels pour l'entretien et la protection de votre toiture
                </p>
            </div>

            <!-- Composant des prestations -->
            <x-prestations-list />

            <!-- Section CTA -->
            <div class="mt-16 bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-8 text-white text-center">
                <h2 class="text-2xl font-bold mb-4">
                    <i class="fas fa-phone mr-2"></i>
                    Prêt à entretenir votre toiture ?
                </h2>
                <p class="text-lg mb-6 opacity-90">
                    Contactez-nous pour un devis gratuit et personnalisé
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('form.step', 'propertyType') }}" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
                        <i class="fas fa-calculator mr-2"></i>
                        Devis Gratuit
                    </a>
                    <a href="tel:{{ setting('company_phone_raw') }}" 
                       class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-8 rounded-lg text-lg transition-colors shadow-lg">
                        <i class="fas fa-phone mr-2"></i>
                        {{ setting('company_phone') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Message envoyé avec succès')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-20">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            <!-- Carte de succès -->
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                <!-- Header avec gradient -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-12 text-center text-white">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full mb-4">
                        <i class="fas fa-check-circle text-5xl"></i>
                    </div>
                    <h1 class="text-4xl font-bold mb-3">Message envoyé avec succès !</h1>
                    <p class="text-xl opacity-95">Nous avons bien reçu votre demande</p>
                </div>
                
                <!-- Contenu -->
                <div class="p-8 md:p-12">
                    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-envelope-open-text text-green-600 text-2xl mr-4 mt-1"></i>
                            <div>
                                <h3 class="font-bold text-green-800 mb-2">Email de confirmation envoyé</h3>
                                <p class="text-green-700">
                                    Nous vous avons envoyé un email de confirmation à <strong>{{ $contactData['email'] ?? '' }}</strong>.
                                    Vérifiez votre boîte de réception (et vos spams).
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
                        <h3 class="font-bold text-blue-800 mb-3">
                            <i class="fas fa-info-circle mr-2"></i>Prochaines étapes
                        </h3>
                        <ol class="space-y-3 text-blue-700">
                            <li class="flex items-start">
                                <span class="font-bold mr-3 text-blue-600">1.</span>
                                <span>Notre équipe examine votre demande dans les plus brefs délais</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-3 text-blue-600">2.</span>
                                <span>Un conseiller vous contacte sous 24h pour affiner les détails</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-3 text-blue-600">3.</span>
                                <span>Vous recevez votre devis personnalisé et détaillé</span>
                            </li>
                        </ol>
                    </div>
                    
                    @if(!empty($contactData['callback_time']))
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-lg mb-6">
                        <p class="text-yellow-800">
                            <i class="fas fa-clock mr-2"></i>
                            <strong>Rappel préféré :</strong> {{ $contactData['callback_time'] }}
                        </p>
                    </div>
                    @endif
                    
                    <!-- Actions -->
                    <div class="grid md:grid-cols-3 gap-4 mt-8">
                        <a href="{{ url('/') }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-bold transition-all transform hover:scale-105 text-center">
                            <i class="fas fa-home mr-2"></i>
                            Accueil
                        </a>
                        <a href="{{ route('contact') }}" 
                           class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-xl font-bold transition-all transform hover:scale-105 text-center">
                            <i class="fas fa-envelope mr-2"></i>
                            Nouveau message
                        </a>
                        <a href="{{ route('form.step', 'propertyType') }}" 
                           class="text-white px-6 py-3 rounded-xl font-bold transition-all transform hover:scale-105 text-center"
                           style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                            <i class="fas fa-calculator mr-2"></i>
                            Simulateur
                        </a>
                    </div>
                    
                    <!-- Contact direct -->
                    <div class="mt-8 pt-8 border-t border-gray-200 text-center">
                        <p class="text-gray-600 mb-4">Besoin d'une réponse immédiate ?</p>
                        <a href="tel:{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}" 
                           class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg"
                           onclick="trackPhoneCall('{{ $companySettings['phone_raw'] ?? $companySettings['phone'] }}', 'contact-success')">
                            <i class="fas fa-phone mr-2"></i>
                            Appeler {{ $companySettings['phone'] }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


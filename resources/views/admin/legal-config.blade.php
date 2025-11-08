@extends('layouts.admin')

@section('title', 'Configuration des Informations Légales')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">📋 Configuration des Informations Légales</h1>
            <p class="mt-2 text-gray-600">Configurez les informations de votre entreprise pour les pages légales</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Configuration Form -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Informations de l'Entreprise</h2>
                <p class="text-gray-600 text-sm">Ces informations apparaîtront dans vos pages légales</p>
            </div>
            
            <form method="POST" action="{{ route('admin.legal.config.update') }}" class="p-6">
                @csrf
                
                <!-- Informations de base -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'entreprise *</label>
                        <input type="text" name="company_name" value="{{ $legalData['company_name'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Ex: Sausser Couverture" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact *</label>
                        <input type="email" name="company_email" value="{{ $legalData['company_email'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="contact@exemple.com" required>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adresse complète *</label>
                    <textarea name="company_address" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                              placeholder="Adresse complète de l'entreprise" required>{{ $legalData['company_address'] }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                        <input type="text" name="company_phone" value="{{ $legalData['company_phone'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="01 23 45 67 89" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Directeur de publication</label>
                        <input type="text" name="company_director" value="{{ $legalData['company_director'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Nom du directeur">
                    </div>
                </div>

                <!-- Coordonnées bancaires (RIB) -->
                <div class="border-t border-gray-200 pt-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Coordonnées Bancaires (RIB)</h3>
                    <p class="text-gray-600 text-sm mb-4">Ces informations apparaîtront sur les devis et factures pour le paiement</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">RIB (Relevé d'Identité Bancaire)</label>
                        <textarea name="company_rib" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Exemple:&#10;Banque: Crédit Agricole&#10;IBAN: FR76 1234 5678 9012 3456 7890 123&#10;BIC: AGRIFRPPXXX">{{ $legalData['company_rib'] }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Format libre. Vous pouvez inclure le nom de la banque, l'IBAN, le BIC, etc.</p>
                    </div>
                </div>

                <!-- Informations légales -->
                <div class="border-t border-gray-200 pt-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations Légales</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">SIRET</label>
                            <input type="text" name="company_siret" value="{{ $legalData['company_siret'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="12345678901234">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">RCS</label>
                            <input type="text" name="company_rcs" value="{{ $legalData['company_rcs'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="RCS Paris B 123456789">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Capital social</label>
                            <input type="text" name="company_capital" value="{{ $legalData['company_capital'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="10 000 €">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">TVA intracommunautaire</label>
                            <input type="text" name="company_tva" value="{{ $legalData['company_tva'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="FR12345678901">
                        </div>
                    </div>
                </div>

                <!-- Informations techniques -->
                <div class="border-t border-gray-200 pt-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations Techniques</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hébergeur</label>
                            <input type="text" name="hosting_provider" value="{{ $legalData['hosting_provider'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Ex: OVH, AWS, etc.">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description de l'entreprise</label>
                            <textarea name="company_description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Description courte de votre entreprise">{{ $legalData['company_description'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Modalités de paiement -->
                <div class="border-t border-gray-200 pt-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Modalités de Paiement (CGV)</h3>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalités de paiement</label>
                            <textarea name="payment_terms" rows="6" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Décrivez vos modalités de paiement (HTML autorisé)">{{ $legalData['payment_terms'] }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Exemple: &lt;ul&gt;&lt;li&gt;&lt;strong&gt;Acompte:&lt;/strong&gt; 30% à la commande&lt;/li&gt;&lt;/ul&gt;</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pénalités de retard (optionnel)</label>
                            <textarea name="late_payment_penalties" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Décrivez les pénalités en cas de retard de paiement (HTML autorisé)">{{ $legalData['late_payment_penalties'] }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Laissez vide pour ne pas afficher de pénalités</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.dashboard') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Sauvegarder
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview des pages légales -->
        <div class="mt-8 bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Aperçu des Pages Légales</h2>
                <p class="text-gray-600 text-sm">Vos pages légales seront mises à jour automatiquement</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('legal.mentions') }}" target="_blank" 
                       class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-gavel text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Mentions Légales</h3>
                            <p class="text-sm text-gray-600">Informations légales obligatoires</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('legal.privacy') }}" target="_blank" 
                       class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">Politique de Confidentialité</h3>
                            <p class="text-sm text-gray-600">Protection des données personnelles</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('legal.cgv') }}" target="_blank" 
                       class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-contract text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-800">CGV</h3>
                            <p class="text-sm text-gray-600">Conditions générales de vente</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.admin')

@section('title', 'Configuration')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Configuration</h1>
        <p class="text-gray-600 mt-2">Gérez les paramètres de votre simulateur</p>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <a href="#company" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm active">
                    <i class="fas fa-building mr-2"></i>Entreprise
                </a>
                <a href="#branding" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-palette mr-2"></i>Branding
                </a>
                <a href="#email" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-envelope mr-2"></i>Email
                </a>
                <a href="#email-preview" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-eye mr-2"></i>Prévisualisation Email
                </a>
                <a href="#ai-config" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-robot mr-2"></i>IA & ChatGPT
                </a>
                <a href="#social" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-share-alt mr-2"></i>Réseaux Sociaux
                </a>
                <a href="#security" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-shield-alt mr-2"></i>Sécurité
                </a>
                <a href="#analytics" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-chart-line mr-2"></i>Analytics
                </a>
                <a href="#conversion" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-question-circle mr-2"></i>FAQ
                </a>
            </nav>
        </div>
    </div>

    <!-- Company Settings -->
    <div id="company" class="config-section">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Informations de l'entreprise</h2>
            <form method="POST" action="{{ route('config.update.company') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'entreprise *</label>
                        <input type="text" name="company_name" value="{{ setting('company_name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone principal *</label>
                        <input type="text" name="company_phone" value="{{ setting('company_phone') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Format libre (ex : 07 78 84 04 95). Ce numéro sera utilisé partout sur le site.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="company_email" value="{{ setting('company_email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slogan</label>
                        <input type="text" name="company_slogan" value="{{ setting('company_slogan') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description de l'entreprise</label>
                        <textarea name="company_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Brève description de votre entreprise qui apparaîtra dans le footer...">{{ setting('company_description') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Cette description apparaîtra dans le footer du site</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone secondaire 1</label>
                        <input type="text" name="company_phone_2" value="{{ setting('company_phone_2') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Optionnel">
                        <p class="text-xs text-gray-500 mt-1">Numéro supplémentaire (commercial, mobile, etc.). Affiché dans le footer s'il est rempli.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone secondaire 2</label>
                        <input type="text" name="company_phone_3" value="{{ setting('company_phone_3') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Optionnel">
                        <p class="text-xs text-gray-500 mt-1">Deuxième numéro supplémentaire (ex : support, urgence).</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horaires d'ouverture</label>
                        <textarea name="company_hours" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Ex : Lun–Ven 8h00–19h00, Sam 9h00–13h00, Dim fermé">{{ setting('company_hours') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Affiché dans le footer et utilisable pour le référencement local.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adresse</label>
                        <input type="text" name="company_address" value="{{ setting('company_address') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                        <input type="text" name="company_city" value="{{ setting('company_city') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Code postal</label>
                        <input type="text" name="company_postal_code" value="{{ setting('company_postal_code') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Clé API Google Maps (pour la carte)</label>
                        <input type="text" 
                               name="google_maps_api_key" 
                               value="{{ setting('google_maps_api_key') }}" 
                               placeholder="AIzaSy..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">
                            Optionnel : Obtenez votre clé API sur <a href="https://console.cloud.google.com/google/maps-apis" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Settings -->
    <div id="email" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Configuration Email SMTP</h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Pour Hostinger, utilisez : smtp.hostinger.com, port 587, encryption TLS
            </p>
            
            <form method="POST" action="{{ route('config.update.email') }}">
                @csrf
                <div class="space-y-4">
                    <!-- Activation -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="email_enabled" value="1" {{ setting('email_enabled') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm font-medium text-gray-700">✉️ Activer l'envoi d'emails</span>
                        </label>
                    </div>

                    <!-- Serveur SMTP -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">Paramètres SMTP</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Serveur SMTP *</label>
                                <input type="text" name="mail_host" value="{{ setting('mail_host', 'smtp.hostinger.com') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="smtp.hostinger.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Port SMTP *</label>
                                <input type="number" name="mail_port" value="{{ setting('mail_port', '587') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="587">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Encryption *</label>
                                <select name="mail_encryption" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="tls" {{ setting('mail_encryption') == 'tls' ? 'selected' : '' }}>TLS (recommandé)</option>
                                    <option value="ssl" {{ setting('mail_encryption') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur (email) *</label>
                                <input type="email" name="mail_username" value="{{ setting('mail_username') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="contact@votredomaine.com">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe SMTP *</label>
                                <input type="password" name="mail_password" value="{{ setting('mail_password') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="••••••••">
                                <p class="text-xs text-gray-500 mt-1">Le mot de passe de votre compte email Hostinger</p>
                            </div>
                        </div>
                    </div>

                    <!-- Expéditeur -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">Informations d'expédition</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email d'expédition *</label>
                                <input type="email" name="mail_from_address" value="{{ setting('mail_from_address') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="noreply@votredomaine.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom d'expédition *</label>
                                <input type="text" name="mail_from_name" value="{{ setting('mail_from_name', setting('company_name')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Mon Entreprise">
                            </div>
                        </div>
                    </div>

                    <!-- Email de notification -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">Notifications admin</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email pour recevoir les notifications</label>
                            <input type="email" name="admin_notification_email" value="{{ setting('admin_notification_email', setting('company_email')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="admin@votredomaine.com">
                            <p class="text-xs text-gray-500 mt-1">Email où vous recevrez les notifications de nouvelles soumissions</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                    <button type="button" onclick="testEmail()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Tester l'envoi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Preview Section -->
    <div id="email-preview" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">👁️ Prévisualisation des Emails</h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Visualisez comment apparaîtront vos emails aux clients
            </p>
            
            <div class="space-y-6">
                <!-- Email Client Preview -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold mb-3">📧 Email Client (Confirmation de demande)</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;">
                                <h1 style="margin: 0; font-size: 28px;">✅ Demande Reçue !</h1>
                                <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">{{ setting('company_name', 'Rénovation Expert') }}</p>
                            </div>
                            
                            <div style="padding: 30px;">
                                <p style="font-size: 16px; margin-bottom: 20px;">Bonjour <strong>Jean Dupont</strong>,</p>
                                
                                <p style="font-size: 16px; margin-bottom: 25px;">Nous vous remercions d'avoir choisi <strong>{{ setting('company_name', 'notre entreprise') }}</strong> pour votre projet de rénovation.</p>
                            
                                <div style="background: #f8f9fa; padding: 25px; border-left: 5px solid #007bff; margin: 25px 0; border-radius: 0 8px 8px 0;">
                                    <h3 style="color: #007bff; margin-top: 0; font-size: 20px;">📋 Récapitulatif de votre demande</h3>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                        <div>
                                            <p style="margin: 8px 0;"><strong>Type de bien :</strong> Maison</p>
                                            <p style="margin: 8px 0;"><strong>Surface :</strong> 120 m²</p>
                                            <p style="margin: 8px 0;"><strong>Code postal :</strong> 75001</p>
                                        </div>
                                        <div>
                                            <p style="margin: 8px 0;"><strong>Téléphone :</strong> 01 23 45 67 89</p>
                                            <p style="margin: 8px 0;"><strong>Email :</strong> jean.dupont@email.com</p>
                                        </div>
                                    </div>
                                    <p style="margin: 15px 0;"><strong>Types de travaux :</strong> Toiture, Façade</p>
                                </div>
                                
                                <div style="background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 25px 0;">
                                    <h3 style="color: #28a745; margin-top: 0; font-size: 18px;">⏰ Prochaines étapes</h3>
                                    <ul style="margin: 10px 0; padding-left: 20px;">
                                        <li style="margin: 5px 0;">Nous étudions votre demande sous 24h</li>
                                        <li style="margin: 5px 0;">Un expert vous contactera pour un rendez-vous</li>
                                        <li style="margin: 5px 0;">Devis gratuit et sans engagement</li>
                                    </ul>
                                </div>
                                
                                <div style="text-align: center; margin: 30px 0;">
                                    <a href="tel:{{ setting('company_phone', '01 23 45 67 89') }}" style="display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;">
                                        📞 {{ setting('company_phone', '01 23 45 67 89') }}
                                    </a>
                                    <a href="mailto:{{ setting('company_email', 'contact@entreprise.com') }}" style="display: inline-block; background: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 5px;">
                                        ✉️ {{ setting('company_email', 'contact@entreprise.com') }}
                                    </a>
                                </div>
                                
                                <p style="font-size: 14px; color: #666; margin-top: 30px;">
                                    Cordialement,<br>
                                    <strong>{{ setting('company_name', 'L\'équipe Rénovation Expert') }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Email Button -->
                <div class="text-center">
                    <button onclick="sendTestEmail()" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700">
                        <i class="fas fa-paper-plane mr-2"></i>Envoyer un Email de Test
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- IA & ChatGPT Configuration -->
    <div id="ai-config" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">🤖 Configuration IA & ChatGPT</h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Configurez l'intégration ChatGPT pour la génération automatique de contenu de vos pages de services
            </p>
            
            <form method="POST" action="{{ route('config.update.ai') }}">
                @csrf
                <div class="space-y-6">
                    <!-- API Key Configuration -->
                    <div class="border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">🔑 Configuration API ChatGPT</h3>
                        
                        <div class="mb-4">
                            <label class="flex items-center mb-4">
                                <input type="checkbox" 
                                       name="chatgpt_enabled" 
                                       id="chatgpt_enabled"
                                       value="1"
                                       {{ setting('chatgpt_enabled', true) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700">
                                    Activer ChatGPT (si désactivé, Groq sera utilisé directement)
                                </span>
                            </label>
                        </div>
                        
                        <div class="mb-4">
                            <label for="chatgpt_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Clé API OpenAI/ChatGPT
                            </label>
                            <div class="flex">
                                <input type="password" 
                                       id="chatgpt_api_key" 
                                       name="chatgpt_api_key" 
                                       value="{{ setting('chatgpt_api_key', '') }}"
                                       class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="sk-...">
                                <button type="button" 
                                        id="test-chatgpt-btn"
                                        onclick="testChatGPT()" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-r-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-check mr-1"></i>Tester
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Obtenez votre clé API sur <a href="https://platform.openai.com/api-keys" target="_blank" class="text-blue-600 hover:underline">platform.openai.com</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label for="chatgpt_model" class="block text-sm font-medium text-gray-700 mb-2">
                                Modèle ChatGPT
                            </label>
                            <select name="chatgpt_model" 
                                    id="chatgpt_model"
                                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="gpt-3.5-turbo" {{ setting('chatgpt_model', 'gpt-3.5-turbo') == 'gpt-3.5-turbo' ? 'selected' : '' }}>GPT-3.5 Turbo (Recommandé)</option>
                                <option value="gpt-4" {{ setting('chatgpt_model', 'gpt-3.5-turbo') == 'gpt-4' ? 'selected' : '' }}>GPT-4 (Plus puissant)</option>
                                <option value="gpt-4-turbo" {{ setting('chatgpt_model', 'gpt-3.5-turbo') == 'gpt-4-turbo' ? 'selected' : '' }}>GPT-4 Turbo (Plus rapide)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                GPT-3.5 Turbo est plus économique, GPT-4 est plus créatif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Groq IA Configuration (Fallback) -->
                    <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                        <h3 class="text-lg font-semibold mb-4">🔄 Configuration API Groq (Alternative)</h3>
                        <p class="text-xs text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Groq sera utilisé automatiquement si ChatGPT ne fonctionne pas
                        </p>
                        
                        <div class="mb-4">
                            <label for="groq_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Clé API Groq
                            </label>
                            <div class="flex">
                                <input type="password" 
                                       id="groq_api_key" 
                                       name="groq_api_key" 
                                       value="{{ setting('groq_api_key', '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') }}"
                                       class="flex-1 border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                       placeholder="gsk-...">
                                <button type="button" 
                                        id="test-groq-btn"
                                        onclick="testGroq()" 
                                        class="bg-purple-600 text-white px-4 py-2 rounded-r-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <i class="fas fa-check mr-1"></i>Tester
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Obtenez votre clé API sur <a href="https://console.groq.com/" target="_blank" class="text-purple-600 hover:underline">console.groq.com</a>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label for="groq_model" class="block text-sm font-medium text-gray-700 mb-2">
                                Modèle Groq
                            </label>
                            <select name="groq_model" 
                                    id="groq_model"
                                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <option value="llama-3.1-8b-instant" {{ setting('groq_model', 'llama-3.1-8b-instant') == 'llama-3.1-8b-instant' ? 'selected' : '' }}>llama-3.1-8b-instant (Recommandé)</option>
                                <option value="llama-3.1-70b-versatile" {{ setting('groq_model', 'llama-3.1-8b-instant') == 'llama-3.1-70b-versatile' ? 'selected' : '' }}>llama-3.1-70b-versatile (Plus puissant)</option>
                                <option value="mixtral-8x7b-32768" {{ setting('groq_model', 'llama-3.1-8b-instant') == 'mixtral-8x7b-32768' ? 'selected' : '' }}>mixtral-8x7b-32768 (Très rapide)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Groq sera utilisé automatiquement en cas d'échec de ChatGPT
                            </p>
                        </div>
                    </div>
                    
                    <!-- Test des APIs avec génération de contenu -->
                    <div class="border border-yellow-200 rounded-lg p-6 bg-yellow-50 mt-6">
                        <h3 class="text-lg font-semibold mb-4">🧪 Test des APIs avec Génération de Contenu</h3>
                        <p class="text-xs text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Testez les APIs avec un vrai exemple de génération de contenu pour vérifier qu'elles fonctionnent correctement.
                        </p>
                        
                        <div class="mb-4">
                            <label for="test_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                Exemple de prompt à tester
                            </label>
                            <textarea id="test_prompt" 
                                      rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                      placeholder="Ex: Créez un contenu pour un service de 'Rénovation de façade'">Créez un contenu web complet pour un service de "Rénovation de façade". Le contenu doit inclure une description détaillée, 3 prestations spécifiques, et une section FAQ avec 2 questions.</textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <button type="button" 
                                        id="test-chatgpt-generate-btn"
                                        onclick="testChatGPTGenerate()" 
                                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-magic mr-2"></i>Tester ChatGPT avec Génération
                                </button>
                            </div>
                            <div>
                                <button type="button" 
                                        id="test-groq-generate-btn"
                                        onclick="testGroqGenerate()" 
                                        class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <i class="fas fa-magic mr-2"></i>Tester Groq avec Génération
                                </button>
                            </div>
                        </div>
                        
                        <!-- Résultats des tests -->
                        <div id="test-results" class="hidden mt-4">
                            <h4 class="text-md font-semibold mb-2">Résultats du test :</h4>
                            <div id="test-results-content" class="bg-white border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                                <!-- Les résultats seront affichés ici -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuration par défaut -->
                    <div class="border border-green-200 rounded-lg p-6 bg-green-50 mt-6">
                        <h3 class="text-lg font-semibold mb-4">⚙️ Configuration par Défaut</h3>
                        <p class="text-xs text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Choisissez quelle API utiliser par défaut si les deux sont disponibles.
                        </p>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                API par défaut
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="default_ai_provider" 
                                           value="chatgpt"
                                           {{ setting('default_ai_provider', 'chatgpt') == 'chatgpt' ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        ChatGPT (recommandé)
                                    </span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" 
                                           name="default_ai_provider" 
                                           value="groq"
                                           {{ setting('default_ai_provider', 'chatgpt') == 'groq' ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm font-medium text-gray-700">
                                        Groq (plus rapide)
                                    </span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Si l'API par défaut n'est pas disponible, l'autre sera utilisée automatiquement.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Save Button -->
                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>Sauvegarder la Configuration IA
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Social Media Settings -->
    <div id="social" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Réseaux Sociaux & Google</h2>
            <form method="POST" action="{{ route('config.update.social') }}">
                @csrf
                <div class="space-y-6">
                    <!-- Google Settings -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Paramètres Google</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google Place ID</label>
                        <input type="text" name="google_place_id" value="{{ setting('google_place_id') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="ChIJ...">
                        <p class="text-xs text-gray-500 mt-1">Pour importer automatiquement vos avis Google</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Google API Key</label>
                        <input type="text" name="google_api_key" value="{{ setting('google_api_key') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Clé API Google Places pour récupérer les avis</p>
                    </div>
                    <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Google Business</label>
                                <input type="url" name="google_business_url" value="{{ setting('google_business_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://business.google.com/...">
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Networks -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Réseaux Sociaux</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-facebook text-blue-600 mr-2"></i>Facebook
                                </label>
                                <input type="url" name="facebook_url" value="{{ setting('facebook_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://facebook.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-instagram text-pink-600 mr-2"></i>Instagram
                                </label>
                                <input type="url" name="instagram_url" value="{{ setting('instagram_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://instagram.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter
                                </label>
                                <input type="url" name="twitter_url" value="{{ setting('twitter_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://twitter.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-linkedin text-blue-700 mr-2"></i>LinkedIn
                                </label>
                                <input type="url" name="linkedin_url" value="{{ setting('linkedin_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://linkedin.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-youtube text-red-600 mr-2"></i>YouTube
                                </label>
                                <input type="url" name="youtube_url" value="{{ setting('youtube_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://youtube.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-tiktok text-black mr-2"></i>TikTok
                                </label>
                                <input type="url" name="tiktok_url" value="{{ setting('tiktok_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://tiktok.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-pinterest text-red-700 mr-2"></i>Pinterest
                                </label>
                                <input type="url" name="pinterest_url" value="{{ setting('pinterest_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://pinterest.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-snapchat text-yellow-500 mr-2"></i>Snapchat
                                </label>
                                <input type="url" name="snapchat_url" value="{{ setting('snapchat_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://snapchat.com/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-whatsapp text-green-600 mr-2"></i>WhatsApp
                                </label>
                                <input type="url" name="whatsapp_url" value="{{ setting('whatsapp_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://wa.me/...">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fab fa-telegram text-blue-500 mr-2"></i>Telegram
                                </label>
                                <input type="url" name="telegram_url" value="{{ setting('telegram_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="https://t.me/...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div id="security" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-shield-alt mr-2 text-green-600"></i>Paramètres de Sécurité
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Configurez Google reCAPTCHA v3 pour protéger vos formulaires contre les robots et le spam
            </p>
            
            <form method="POST" action="{{ route('config.update.security') }}">
                @csrf
                <div class="space-y-6">
                    <!-- Google reCAPTCHA v3 -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-green-800">
                            <i class="fas fa-robot mr-2"></i>Google reCAPTCHA v3
                        </h3>
                        <p class="text-sm text-gray-700 mb-4">
                            reCAPTCHA v3 fonctionne en arrière-plan et n'affiche pas de challenge aux utilisateurs. 
                            Il analyse le comportement des visiteurs et attribue un score de confiance (0.0 = bot, 1.0 = humain).
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Clé publique (Site Key) *
                                </label>
                                <input type="text" 
                                       name="recaptcha_site_key" 
                                       value="{{ setting('recaptcha_site_key') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" 
                                       placeholder="6Lc...">
                                <p class="text-xs text-gray-500 mt-1">
                                    Obtenez vos clés sur <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-blue-600 hover:underline">Google reCAPTCHA</a>
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Clé secrète (Secret Key) *
                                </label>
                                <input type="password" 
                                       name="recaptcha_secret_key" 
                                       value="{{ setting('recaptcha_secret_key') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" 
                                       placeholder="6Lc...">
                                <p class="text-xs text-gray-500 mt-1">
                                    Cette clé doit rester secrète et ne jamais être exposée côté client
                                </p>
                            </div>
                            
                            @if(setting('recaptcha_site_key') && setting('recaptcha_secret_key'))
                            <div class="bg-green-100 border border-green-300 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span class="text-sm font-medium text-green-800">reCAPTCHA est configuré et actif</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    Les formulaires de contact (téléphone et email) sont protégés par reCAPTCHA v3
                                </p>
                            </div>
                            @else
                            <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    <span class="text-sm font-medium text-yellow-800">reCAPTCHA n'est pas configuré</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    Les formulaires fonctionneront sans protection anti-robot jusqu'à la configuration
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">
                            <i class="fas fa-question-circle mr-2"></i>Comment obtenir vos clés reCAPTCHA ?
                        </h4>
                        <ol class="text-xs text-gray-700 space-y-1 list-decimal list-inside">
                            <li>Allez sur <a href="https://www.google.com/recaptcha/admin" target="_blank" class="text-blue-600 hover:underline">Google reCAPTCHA Admin</a></li>
                            <li>Créez un nouveau site en sélectionnant "reCAPTCHA v3"</li>
                            <li>Ajoutez votre domaine (ex: normesrenovationbretagne.fr)</li>
                            <li>Copiez la "Site Key" et la "Secret Key"</li>
                            <li>Collez-les dans les champs ci-dessus et enregistrez</li>
                        </ol>
                    </div>
                    
                    <!-- Blocage géographique -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-orange-800">
                            <i class="fas fa-globe mr-2"></i>Blocage géographique
                        </h3>
                        <p class="text-sm text-gray-700 mb-4">
                            Restreignez l'accès au formulaire de devis aux utilisateurs localisés en France uniquement.
                        </p>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       name="block_non_france" 
                                       id="block_non_france" 
                                       value="1"
                                       {{ setting('block_non_france', false) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                                <label for="block_non_france" class="ml-3 text-sm font-medium text-gray-700">
                                    Bloquer l'accès au formulaire pour les utilisateurs hors de France
                                </label>
                            </div>
                            
                            @if(setting('block_non_france', false))
                            <div class="bg-orange-100 border border-orange-300 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt text-orange-600 mr-2"></i>
                                    <span class="text-sm font-medium text-orange-800">Blocage géographique activé</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    Les utilisateurs localisés hors de France verront une page de blocage avec des options de contact
                                </p>
                            </div>
                            @else
                            <div class="bg-gray-100 border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-globe text-gray-600 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-800">Blocage géographique désactivé</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-2">
                                    Tous les utilisateurs peuvent accéder au formulaire, quelle que soit leur localisation
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer les paramètres de sécurité
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Analytics Settings -->
    <div id="analytics" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-chart-line mr-2 text-blue-600"></i>Configuration Google Analytics
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Configurez Google Analytics pour suivre les visites et les appels téléphoniques. 
                Les statistiques seront disponibles dans l'onglet <strong>Visites</strong> de l'admin.
            </p>
            
            <form method="POST" action="{{ route('config.update.analytics') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <!-- Google Analytics View ID -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-blue-800">
                            <i class="fas fa-chart-bar mr-2"></i>Google Analytics View ID
                        </h3>
                        <p class="text-sm text-gray-700 mb-4">
                            Le View ID permet à Spatie Laravel Analytics de récupérer les données de votre propriété Google Analytics.
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    View ID (Analytics View ID) *
                                </label>
                                <input type="text" 
                                       name="analytics_view_id" 
                                       value="{{ setting('analytics_view_id') ?: env('ANALYTICS_VIEW_ID') }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="123456789">
                                <p class="text-xs text-gray-500 mt-1">
                                    Trouvez votre View ID dans <strong>Google Analytics > Admin > View Settings</strong>
                                </p>
                            </div>
                            
                            @if(setting('analytics_view_id') || env('ANALYTICS_VIEW_ID'))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                    <span class="text-sm font-medium text-green-800">View ID configuré</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    View ID actuel : <strong>{{ setting('analytics_view_id') ?: env('ANALYTICS_VIEW_ID') }}</strong>
                                </p>
                            </div>
                            @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                    <span class="text-sm font-medium text-yellow-800">View ID non configuré</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    Configurez le View ID pour activer les statistiques de visites
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Service Account Credentials -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-purple-800">
                            <i class="fas fa-key mr-2"></i>Service Account Credentials (JSON)
                        </h3>
                        <p class="text-sm text-gray-700 mb-4">
                            Téléchargez le fichier JSON de votre compte de service Google Cloud. 
                            Ce fichier doit être placé dans <code class="bg-gray-100 px-2 py-1 rounded">storage/app/analytics/service-account-credentials.json</code>
                        </p>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Fichier JSON du Service Account
                                </label>
                                <input type="file" 
                                       name="analytics_credentials" 
                                       accept=".json" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">
                                    Format : fichier JSON téléchargé depuis Google Cloud Console
                                </p>
                            </div>
                            
                            @php
                                $credentialsPath = storage_path('app/analytics/service-account-credentials.json');
                                $hasCredentials = file_exists($credentialsPath);
                            @endphp
                            
                            @if($hasCredentials)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                        <span class="text-sm font-medium text-green-800">Fichier credentials présent</span>
                                    </div>
                                    <span class="text-xs text-gray-600">
                                        Modifié : {{ date('d/m/Y H:i', filemtime($credentialsPath)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    Le fichier est présent dans <code class="bg-gray-100 px-1 rounded">storage/app/analytics/</code>
                                </p>
                            </div>
                            @else
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                                    <span class="text-sm font-medium text-red-800">Fichier credentials manquant</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    Téléchargez le fichier JSON depuis Google Cloud Console et placez-le dans le dossier indiqué ci-dessus
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-800">
                            <i class="fas fa-book mr-2"></i>Instructions de configuration
                        </h3>
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">1</span>
                                <div>
                                    <p class="font-medium mb-1">Créer un compte de service Google Cloud</p>
                                    <p class="text-xs text-gray-600">Allez sur <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>, créez un projet et activez l'API <strong>Google Analytics Reporting API</strong></p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">2</span>
                                <div>
                                    <p class="font-medium mb-1">Télécharger le fichier JSON</p>
                                    <p class="text-xs text-gray-600">Dans <strong>IAM & Admin > Service Accounts</strong>, créez un compte de service et téléchargez le fichier JSON des credentials</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">3</span>
                                <div>
                                    <p class="font-medium mb-1">Donner les permissions dans Google Analytics</p>
                                    <p class="text-xs text-gray-600">Dans <strong>Google Analytics > Admin > Property Access Management</strong>, ajoutez l'email du compte de service avec les permissions <strong>Viewer</strong></p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">4</span>
                                <div>
                                    <p class="font-medium mb-1">Récupérer le View ID</p>
                                    <p class="text-xs text-gray-600">Dans <strong>Google Analytics > Admin > View Settings</strong>, notez le <strong>View ID</strong> (format: 123456789)</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">5</span>
                                <div>
                                    <p class="font-medium mb-1">Configurer ici</p>
                                    <p class="text-xs text-gray-600">Entrez le View ID ci-dessus et téléchargez le fichier JSON. Une fois configuré, les statistiques seront disponibles dans <strong>/admin/visits</strong></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm font-medium text-blue-800 mb-2">
                                <i class="fas fa-link mr-2"></i>Liens utiles
                            </p>
                            <ul class="text-xs text-blue-700 space-y-1">
                                <li><a href="https://console.cloud.google.com/" target="_blank" class="hover:underline">Google Cloud Console</a></li>
                                <li><a href="https://analytics.google.com/" target="_blank" class="hover:underline">Google Analytics</a></li>
                                <li><a href="{{ route('admin.visits') }}" target="_blank" class="hover:underline">Page Statistiques de Visites</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer la configuration Analytics
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FAQ Settings -->
    <div id="conversion" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-question-circle mr-2 text-purple-600"></i>Questions Fréquentes (FAQ)
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Ajoutez des questions fréquentes pour réduire les abandons et améliorer le SEO. Les FAQ s'affichent sur la page Contact.
            </p>
            
            <form method="POST" action="{{ route('config.update.conversion') }}">
                @csrf
                
                    <!-- FAQ -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-purple-800">
                                    <i class="fas fa-question-circle mr-2"></i>Questions Fréquentes (FAQ)
                                </h3>
                                <p class="text-sm text-gray-700 mt-1">
                                    Ajoutez des questions fréquentes pour réduire les abandons et améliorer le SEO
                                </p>
                            </div>
                            <button type="button" 
                                    id="generateFaqBtn"
                                    class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-4 py-2 rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-200 text-sm font-semibold flex items-center whitespace-nowrap">
                                <i class="fas fa-magic mr-2"></i>
                                Générer 5 questions avec l'IA
                            </button>
                        </div>
                    
                    <div id="faq-container" class="space-y-4">
                        @php
                            $faqsData = setting('faqs', '[]');
                            $faqs = is_string($faqsData) ? json_decode($faqsData, true) : ($faqsData ?? []);
                            if (empty($faqs)) {
                                $faqs = [
                                    ['question' => '', 'answer' => '']
                                ];
                            }
                        @endphp
                        
                        @foreach($faqs as $index => $faq)
                        <div class="faq-item bg-white p-4 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Question {{ $index + 1 }}</span>
                                @if($index > 0)
                                <button type="button" onclick="removeFaq(this)" class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </button>
                                @endif
                            </div>
                            <input type="text" 
                                   name="faqs[{{ $index }}][question]" 
                                   value="{{ $faq['question'] ?? '' }}"
                                   placeholder="Ex: Combien de temps prend la réalisation d'un devis ?"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-purple-500 focus:border-purple-500">
                            <textarea name="faqs[{{ $index }}][answer]" 
                                      rows="2"
                                      placeholder="Ex: Nous vous envoyons un devis gratuit sous 24h..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">{{ $faq['answer'] ?? '' }}</textarea>
                        </div>
                        @endforeach
                    </div>
                    
                    <button type="button" onclick="addFaq()" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Ajouter une question
                    </button>
                </div>
                
                <div class="mt-6">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer les configurations
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Branding Settings -->
    <div id="branding" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">🎨 Configuration Branding</h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Personnalisez l'apparence de votre site avec votre identité visuelle
            </p>
            
            <form method="POST" action="{{ route('config.update.branding') }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <!-- Logo de l'entreprise -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-3 text-blue-800">🏢 Logo de l'Entreprise</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logo principal</label>
                                <input type="file" name="company_logo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Formats acceptés : PNG, JPG, SVG, WebP - Max 2 Mo</p>
                            </div>
                            @if(setting('company_logo'))
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Logo actuel :</p>
                                <img src="{{ setting('company_logo') }}" alt="Logo actuel" class="h-16 w-auto border border-gray-200 rounded">
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">🌐 Favicon</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Icône du site (favicon)</label>
                            <input type="file" name="favicon" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format recommandé : ICO ou PNG 32x32px</p>
                            @php
                                $currentFavicon = setting('site_favicon');
                                $faviconUrl = null;
                                if ($currentFavicon) {
                                    $faviconPath = public_path($currentFavicon);
                                    if (file_exists($faviconPath)) {
                                        $faviconUrl = asset($currentFavicon);
                                    }
                                }
                                // Vérifier aussi dans seo_config
                                if (!$faviconUrl) {
                                    $seoConfigData = \App\Models\Setting::get('seo_config', '[]');
                                    $seoConfig = is_string($seoConfigData) ? json_decode($seoConfigData, true) : ($seoConfigData ?? []);
                                    if (!empty($seoConfig['favicon'])) {
                                        $seoFaviconPath = public_path($seoConfig['favicon']);
                                        if (file_exists($seoFaviconPath)) {
                                            $faviconUrl = asset($seoConfig['favicon']);
                                        }
                                    }
                                }
                            @endphp
                            @if($faviconUrl)
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Favicon actuel :</p>
                                <img src="{{ $faviconUrl }}?v={{ time() }}" alt="Favicon actuel" class="w-8 h-8 object-contain border border-gray-200 rounded">
                                <p class="text-xs text-gray-500 mt-1">Si le favicon ne s'affiche pas, videz le cache de votre navigateur (Ctrl+F5 ou Cmd+Shift+R)</p>
                                <p class="text-xs text-blue-600 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Le fichier <code class="bg-gray-100 px-1 rounded">favicon.ico</code> sera automatiquement créé à la racine pour Google.
                                </p>
                                <p class="text-xs text-purple-600 mt-2">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Vérifiez que le favicon est détecté par Google avec l'<a href="https://search.google.com/search-console" target="_blank" class="underline">outil d'inspection d'URL</a> de Search Console.
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Image Hero Contact -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">📸 Image Hero Page Contact</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image de fond pour la page Contact</label>
                            <input type="file" 
                                   name="contact_hero_image" 
                                   accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format recommandé : JPG ou PNG, 1920x600px</p>
                            @if(setting('contact_hero_image'))
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
                                <img src="{{ asset(setting('contact_hero_image')) }}" 
                                     alt="Hero Contact" 
                                     class="h-32 w-auto border border-gray-200 rounded object-cover">
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Image Simulateur -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">💰 Image Simulateur de Coût</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Image pour le simulateur (affichée dans les articles de blog)</label>
                            <input type="file" 
                                   name="simulator_image" 
                                   accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format recommandé : JPG ou PNG carré (400x400px ou plus). Cette image apparaîtra à gauche de la section CTA dans les articles.</p>
                            @if(setting('simulator_image') && file_exists(public_path(setting('simulator_image'))))
                            <div class="mt-3">
                                <p class="text-sm text-gray-600 mb-2">Image actuelle :</p>
                                <img src="{{ asset(setting('simulator_image')) }}" 
                                     alt="Simulateur" 
                                     class="h-32 w-32 object-cover border border-gray-200 rounded">
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Couleurs du site (clair + sombre) -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-2">🎨 Couleurs du site — thème clair &amp; sombre</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Définissez deux palettes : une pour le mode clair, une pour le mode sombre. Les visiteurs peuvent basculer (bouton en bas à gauche) si l’option est activée ci-dessous.
                        </p>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <h4 class="font-semibold text-gray-800 mb-3">☀️ Mode clair</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Principale</label>
                                        <input type="color" name="primary_color" value="{{ setting('primary_color', '#3b82f6') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                        <p class="text-xs text-gray-500 mt-1">Boutons, liens</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Secondaire</label>
                                        <input type="color" name="secondary_color" value="{{ setting('secondary_color', '#1e40af') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                        <p class="text-xs text-gray-500 mt-1">Dégradés, survols</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Accent</label>
                                        <input type="color" name="accent_color" value="{{ setting('accent_color', '#f59e0b') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                        <p class="text-xs text-gray-500 mt-1">Mise en valeur</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-900 rounded-lg p-4 border border-slate-700 text-white">
                                <h4 class="font-semibold mb-3">🌙 Mode sombre</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-200 mb-2">Principale</label>
                                        <input type="color" name="dark_primary_color" value="{{ setting('dark_primary_color', '#60a5fa') }}" class="w-full h-10 border border-slate-600 rounded-lg cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-200 mb-2">Secondaire</label>
                                        <input type="color" name="dark_secondary_color" value="{{ setting('dark_secondary_color', '#34d399') }}" class="w-full h-10 border border-slate-600 rounded-lg cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-200 mb-2">Accent</label>
                                        <input type="color" name="dark_accent_color" value="{{ setting('dark_accent_color', '#fbbf24') }}" class="w-full h-10 border border-slate-600 rounded-lg cursor-pointer">
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 mt-3">Ces couleurs s’appliquent lorsque le site est en thème sombre.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Thème par défaut</label>
                                <select name="theme_default" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    @php $td = old('theme_default', setting('theme_default') ?? 'light'); @endphp
                                    <option value="light" {{ $td === 'light' ? 'selected' : '' }}>Clair</option>
                                    <option value="dark" {{ $td === 'dark' ? 'selected' : '' }}>Sombre</option>
                                    <option value="system" {{ $td === 'system' ? 'selected' : '' }}>Comme l’appareil (clair / sombre automatique)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Utilisé si le visiteur n’a pas encore choisi (stockage local).</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bascule jour / nuit</label>
                                <label class="inline-flex items-center gap-2 cursor-pointer mt-2">
                                    <input type="checkbox" name="theme_show_toggle" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                           {{ filter_var(setting('theme_show_toggle', true), FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-700">Afficher le bouton pour changer de thème sur le site</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Typographie -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">📝 Typographie</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Police principale</label>
                                <select name="primary_font" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Inter" {{ setting('primary_font') == 'Inter' ? 'selected' : '' }}>Inter (Moderne)</option>
                                    <option value="Roboto" {{ setting('primary_font') == 'Roboto' ? 'selected' : '' }}>Roboto (Google)</option>
                                    <option value="Open Sans" {{ setting('primary_font') == 'Open Sans' ? 'selected' : '' }}>Open Sans (Lisible)</option>
                                    <option value="Montserrat" {{ setting('primary_font') == 'Montserrat' ? 'selected' : '' }}>Montserrat (Élégant)</option>
                                    <option value="Poppins" {{ setting('primary_font') == 'Poppins' ? 'selected' : '' }}>Poppins (Rond)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taille de base</label>
                                <select name="font_size" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    <option value="14px" {{ setting('font_size') == '14px' ? 'selected' : '' }}>Petite (14px)</option>
                                    <option value="16px" {{ setting('font_size') == '16px' ? 'selected' : '' }}>Normale (16px)</option>
                                    <option value="18px" {{ setting('font_size') == '18px' ? 'selected' : '' }}>Grande (18px)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Enregistrer le Branding
                    </button>
                    <button type="button" onclick="previewBranding()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-eye mr-2"></i>Aperçu
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Tab navigation
document.querySelectorAll('.config-tab').forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs
        document.querySelectorAll('.config-tab').forEach(t => {
            t.classList.remove('active', 'border-blue-500', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Add active class to clicked tab
        this.classList.add('active', 'border-blue-500', 'text-blue-600');
        this.classList.remove('border-transparent', 'text-gray-500');
        
        // Hide all sections
        document.querySelectorAll('.config-section').forEach(section => {
            section.classList.add('hidden');
        });
        
        // Show target section
        const target = this.getAttribute('href').substring(1);
        document.getElementById(target).classList.remove('hidden');
    });
});

// Test email function
function testEmail() {
    const email = prompt('Entrez l\'adresse email de test :');
    if (!email) return;
    
    fetch('{{ route('config.test.email') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Email de test envoyé avec succès à ' + email);
        } else {
            alert('❌ Erreur : ' + (data.message || 'Impossible d\'envoyer l\'email'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    });
}

// Fonction pour envoyer un email de test depuis la prévisualisation
function sendTestEmail() {
    const email = prompt('Entrez votre email pour recevoir un test :');
    if (!email) return;
    
    fetch('{{ route('config.test.email') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Email de test envoyé avec succès à ' + email);
        } else {
            alert('❌ Erreur : ' + (data.message || 'Impossible d\'envoyer l\'email'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    });
}

// Fonctions pour les templates email
function previewEmailTemplate(type) {
    const form = document.getElementById(type + '-template-form');
    const htmlContent = form.querySelector('textarea[name="html_content"]').value;
    
    if (!htmlContent.trim()) {
        alert('Veuillez d\'abord saisir du contenu HTML');
        return;
    }
    
    // Ouvrir une nouvelle fenêtre avec la prévisualisation
    const previewWindow = window.open('', '_blank', 'width=800,height=600');
    previewWindow.document.write(htmlContent);
    previewWindow.document.close();
}

function testEmailTemplate(type) {
    const email = prompt('Entrez votre email pour recevoir un test :');
    if (!email) return;
    
    fetch('/config/test-email-template', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            test_email: email,
            template_type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Email de test envoyé avec succès à ' + email);
        } else {
            alert('❌ Erreur : ' + (data.message || 'Impossible d\'envoyer l\'email'));
        }
    })
    .catch(error => {
        alert('❌ Erreur : ' + error.message);
    });
}

function loadDefaultTemplate(type) {
    const form = document.getElementById(type + '-template-form');
    const textarea = form.querySelector('textarea[name="html_content"]');
    
    if (type === 'client') {
        textarea.value = `<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Demande de devis reçue</title>
</head>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa;'>
    <div style='max-width: 600px; margin: 0 auto; background-color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center;'>
            <h1 style='margin: 0; font-size: 28px;'>✅ Demande Reçue !</h1>
            <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>{company_name}</p>
        </div>
        
        <!-- Contenu -->
        <div style='padding: 30px;'>
            <p style='font-size: 16px; margin-bottom: 20px;'>Bonjour <strong>{first_name} {last_name}</strong>,</p>
            
            <p style='font-size: 16px; margin-bottom: 25px;'>Nous vous remercions d'avoir choisi <strong>{company_name}</strong> pour votre projet de rénovation.</p>
        
            <div style='background: #f8f9fa; padding: 25px; border-left: 5px solid #007bff; margin: 25px 0; border-radius: 0 8px 8px 0;'>
                <h3 style='color: #007bff; margin-top: 0; font-size: 20px;'>📋 Récapitulatif de votre demande</h3>
                <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                    <div>
                        <p style='margin: 8px 0;'><strong>Type de bien :</strong> {property_type}</p>
                        <p style='margin: 8px 0;'><strong>Surface :</strong> {surface} m²</p>
                        <p style='margin: 8px 0;'><strong>Code postal :</strong> {postal_code}</p>
                    </div>
                    <div>
                        <p style='margin: 8px 0;'><strong>Téléphone :</strong> {phone}</p>
                        <p style='margin: 8px 0;'><strong>Email :</strong> {email}</p>
                    </div>
                </div>
                <p style='margin: 15px 0;'><strong>Types de travaux :</strong> {work_types}</p>
            </div>
            
            <div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 25px 0;'>
                <h3 style='color: #28a745; margin-top: 0; font-size: 18px;'>⏰ Prochaines étapes</h3>
                <ul style='margin: 10px 0; padding-left: 20px;'>
                    <li style='margin: 5px 0;'>Nous étudions votre demande sous 24h</li>
                    <li style='margin: 5px 0;'>Un expert vous contactera pour un rendez-vous</li>
                    <li style='margin: 5px 0;'>Devis gratuit et sans engagement</li>
                </ul>
            </div>
            
            <p style='font-size: 14px; color: #666; margin-top: 30px;'>
                Cordialement,<br>
                <strong>L'équipe {company_name}</strong>
            </p>
        </div>
    </div>
</body>
</html>`;
    }
    
    alert('Template par défaut chargé ! Vous pouvez maintenant le personnaliser.');
}

// Test de l'API ChatGPT
async function testChatGPT() {
    const apiKey = document.getElementById('chatgpt_api_key').value;
    if (!apiKey) {
        alert('Veuillez d\'abord saisir votre clé API ChatGPT');
        return;
    }
    
    const button = document.getElementById('test-chatgpt-btn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    button.disabled = true;
    
    try {
        const response = await fetch('/config/test-chatgpt', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ api_key: apiKey })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Connexion à ChatGPT réussie ! Votre clé API est valide.');
        } else {
            alert('❌ Erreur de connexion : ' + (result.message || 'Clé API invalide'));
        }
    } catch (error) {
        alert('❌ Erreur de connexion : ' + error.message);
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Test de l'API Groq
async function testGroq() {
    const apiKey = document.getElementById('groq_api_key').value;
    if (!apiKey) {
        alert('Veuillez d\'abord saisir votre clé API Groq');
        return;
    }
    
    const button = document.getElementById('test-groq-btn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Test en cours...';
    button.disabled = true;
    
    try {
        const response = await fetch('/config/test-groq', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ api_key: apiKey })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Connexion à Groq réussie ! Votre clé API est valide.');
        } else {
            alert('❌ Erreur de connexion : ' + (result.message || 'Clé API invalide'));
        }
    } catch (error) {
        alert('❌ Erreur de connexion : ' + error.message);
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Test ChatGPT avec génération de contenu
async function testChatGPTGenerate() {
    const apiKey = document.getElementById('chatgpt_api_key').value;
    const prompt = document.getElementById('test_prompt').value;
    
    if (!apiKey && !prompt) {
        alert('Veuillez d\'abord saisir votre clé API ChatGPT et un prompt de test');
        return;
    }
    
    const button = document.getElementById('test-chatgpt-generate-btn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    button.disabled = true;
    
    const resultsDiv = document.getElementById('test-results');
    const resultsContent = document.getElementById('test-results-content');
    
    try {
        const response = await fetch('/config/test-chatgpt-generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                api_key: apiKey || null,
                prompt: prompt 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            let html = '<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">';
            html += '<h5 class="font-semibold text-green-800 mb-2"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</h5>';
            html += '<div class="text-sm text-gray-600 mb-2"><strong>Modèle utilisé:</strong> ' + (result.model || 'N/A') + '</div>';
            if (result.usage) {
                html += '<div class="text-sm text-gray-600 mb-2">';
                html += '<strong>Tokens utilisés:</strong> ' + (result.usage.total_tokens || 'N/A');
                html += ' (Prompt: ' + (result.usage.prompt_tokens || 'N/A') + ', Completion: ' + (result.usage.completion_tokens || 'N/A') + ')';
                html += '</div>';
            }
            html += '</div>';
            
            html += '<div class="bg-white border border-gray-200 rounded-lg p-4">';
            html += '<h5 class="font-semibold mb-2">Prompt envoyé:</h5>';
            html += '<pre class="bg-gray-50 p-3 rounded text-sm mb-4 whitespace-pre-wrap">' + escapeHtml(result.prompt) + '</pre>';
            html += '<h5 class="font-semibold mb-2">Réponse générée:</h5>';
            html += '<div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">' + escapeHtml(result.content) + '</div>';
            html += '</div>';
            
            resultsContent.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        } else {
            let html = '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
            html += '<h5 class="font-semibold text-red-800 mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Erreur</h5>';
            html += '<p class="text-sm text-red-700">' + escapeHtml(result.message) + '</p>';
            if (result.error_details) {
                html += '<div class="mt-2 text-xs text-red-600">';
                html += '<strong>Détails:</strong> ' + JSON.stringify(result.error_details, null, 2);
                html += '</div>';
            }
            html += '</div>';
            
            resultsContent.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultsContent.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-sm text-red-700">Erreur de connexion : ' + escapeHtml(error.message) + '</p></div>';
        resultsDiv.classList.remove('hidden');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Test Groq avec génération de contenu
async function testGroqGenerate() {
    const apiKey = document.getElementById('groq_api_key').value;
    const prompt = document.getElementById('test_prompt').value;
    
    if (!apiKey && !prompt) {
        alert('Veuillez d\'abord saisir votre clé API Groq et un prompt de test');
        return;
    }
    
    const button = document.getElementById('test-groq-generate-btn');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    button.disabled = true;
    
    const resultsDiv = document.getElementById('test-results');
    const resultsContent = document.getElementById('test-results-content');
    
    try {
        const response = await fetch('/config/test-groq-generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ 
                api_key: apiKey || null,
                prompt: prompt 
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            let html = '<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">';
            html += '<h5 class="font-semibold text-green-800 mb-2"><i class="fas fa-check-circle mr-2"></i>' + result.message + '</h5>';
            html += '<div class="text-sm text-gray-600 mb-2"><strong>Modèle utilisé:</strong> ' + (result.model || 'N/A') + '</div>';
            if (result.usage) {
                html += '<div class="text-sm text-gray-600 mb-2">';
                html += '<strong>Tokens utilisés:</strong> ' + (result.usage.total_tokens || 'N/A');
                html += ' (Prompt: ' + (result.usage.prompt_tokens || 'N/A') + ', Completion: ' + (result.usage.completion_tokens || 'N/A') + ')';
                html += '</div>';
            }
            html += '</div>';
            
            html += '<div class="bg-white border border-gray-200 rounded-lg p-4">';
            html += '<h5 class="font-semibold mb-2">Prompt envoyé:</h5>';
            html += '<pre class="bg-gray-50 p-3 rounded text-sm mb-4 whitespace-pre-wrap">' + escapeHtml(result.prompt) + '</pre>';
            html += '<h5 class="font-semibold mb-2">Réponse générée:</h5>';
            html += '<div class="bg-gray-50 p-3 rounded text-sm whitespace-pre-wrap">' + escapeHtml(result.content) + '</div>';
            html += '</div>';
            
            resultsContent.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        } else {
            let html = '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
            html += '<h5 class="font-semibold text-red-800 mb-2"><i class="fas fa-exclamation-circle mr-2"></i>Erreur</h5>';
            html += '<p class="text-sm text-red-700">' + escapeHtml(result.message) + '</p>';
            if (result.error_details) {
                html += '<div class="mt-2 text-xs text-red-600">';
                html += '<strong>Détails:</strong> ' + JSON.stringify(result.error_details, null, 2);
                html += '</div>';
            }
            html += '</div>';
            
            resultsContent.innerHTML = html;
            resultsDiv.classList.remove('hidden');
        }
    } catch (error) {
        resultsContent.innerHTML = '<div class="bg-red-50 border border-red-200 rounded-lg p-4"><p class="text-sm text-red-700">Erreur de connexion : ' + escapeHtml(error.message) + '</p></div>';
        resultsDiv.classList.remove('hidden');
    } finally {
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Fonction utilitaire pour échapper le HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Ajouter une nouvelle FAQ
let faqIndex = {{ is_array($faqs ?? []) ? count($faqs) : 1 }};
function addFaq() {
    const container = document.getElementById('faq-container');
    if (!container) return;
    const newFaq = document.createElement('div');
    newFaq.className = 'faq-item bg-white p-4 rounded-lg border border-gray-200';
    newFaq.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Question ${faqIndex + 1}</span>
            <button type="button" onclick="removeFaq(this)" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash mr-1"></i>Supprimer
            </button>
        </div>
        <input type="text" 
               name="faqs[${faqIndex}][question]" 
               placeholder="Ex: Combien de temps prend la réalisation d'un devis ?"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-purple-500 focus:border-purple-500">
        <textarea name="faqs[${faqIndex}][answer]" 
                  rows="2"
                  placeholder="Ex: Nous vous envoyons un devis gratuit sous 24h..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"></textarea>
    `;
    container.appendChild(newFaq);
    faqIndex++;
}

// Supprimer une FAQ
function removeFaq(button) {
    const item = button.closest('.faq-item');
    if (!item) return;
    item.remove();
    // Réindexer les questions
    const items = document.querySelectorAll('.faq-item');
    items.forEach((item, index) => {
        const span = item.querySelector('span');
        if (span) {
            span.textContent = `Question ${index + 1}`;
        }
        const inputs = item.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/faqs\[\d+\]/, `faqs[${index}]`));
            }
        });
    });
    faqIndex = items.length;
}

// Générer FAQ avec IA
document.getElementById('generateFaqBtn')?.addEventListener('click', function() {
    const btn = this;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';
    
    fetch('{{ route("config.generate.faqs") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.faqs) {
            // Vider les FAQ existantes
            const container = document.getElementById('faq-container');
            container.innerHTML = '';
            faqIndex = 0;
            
            // Ajouter les nouvelles FAQ
            data.faqs.forEach((faq) => {
                addFaqWithValues(faq.question, faq.answer);
            });
            
            // Afficher un message de succès
            alert(data.message || 'FAQ générées avec succès !');
        } else {
            alert(data.message || 'Erreur lors de la génération des FAQ');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la génération des FAQ. Veuillez réessayer.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Fonction pour ajouter une FAQ avec des valeurs
function addFaqWithValues(question = '', answer = '') {
    const container = document.getElementById('faq-container');
    if (!container) return;
    
    const newFaq = document.createElement('div');
    newFaq.className = 'faq-item bg-white p-4 rounded-lg border border-gray-200';
    newFaq.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Question ${faqIndex + 1}</span>
            <button type="button" onclick="removeFaq(this)" class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash mr-1"></i>Supprimer
            </button>
        </div>
        <input type="text" 
               name="faqs[${faqIndex}][question]" 
               value="${question.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}"
               required
               placeholder="Ex: Combien de temps prend la réalisation d'un devis ?"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:ring-purple-500 focus:border-purple-500">
        <textarea name="faqs[${faqIndex}][answer]" 
                  rows="3"
                  required
                  placeholder="Ex: Nous vous envoyons un devis gratuit sous 24h..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">${answer.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}</textarea>
    `;
    container.appendChild(newFaq);
    faqIndex++;
}
</script>
@endsection

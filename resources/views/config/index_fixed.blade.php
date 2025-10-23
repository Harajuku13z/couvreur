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
                <a href="#email-templates" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-edit mr-2"></i>Templates Email
                </a>
                <a href="#ai-config" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-robot mr-2"></i>IA & ChatGPT
                </a>
                <a href="#social" class="config-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    <i class="fas fa-share-alt mr-2"></i>Réseaux Sociaux
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone *</label>
                        <input type="text" name="company_phone" value="{{ setting('company_phone') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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

    <!-- Email Templates Section -->
    <div id="email-templates" class="config-section hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">✉️ Gestion des Templates Email</h2>
            <p class="text-sm text-gray-600 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Personnalisez le contenu et le design de vos emails clients et admin
            </p>
            
            <div class="space-y-6">
                <!-- Email Client Template -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">📧 Template Email Client</h3>
                        <div class="flex space-x-2">
                            <button onclick="previewEmailTemplate('client')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                                <i class="fas fa-eye mr-1"></i>Prévisualiser
                            </button>
                            <button onclick="testEmailTemplate('client')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                                <i class="fas fa-paper-plane mr-1"></i>Tester
                            </button>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('config.update.email-template') }}" id="client-template-form">
                        @csrf
                        <input type="hidden" name="template_type" value="client">
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Configuration -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Sujet de l'email</label>
                                    <input type="text" name="subject" value="{{ setting('email_client_subject', '✅ Demande de devis reçue - Rénovation Expert') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom de l'expéditeur</label>
                                    <input type="text" name="from_name" value="{{ setting('email_client_from_name', 'Rénovation Expert') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email de l'expéditeur</label>
                                    <input type="email" name="from_email" value="{{ setting('email_client_from_email', 'contact@entreprise.com') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Variables disponibles</label>
                                    <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                        <code class="text-blue-600">{first_name}</code> <code class="text-blue-600">{last_name}</code> <code class="text-blue-600">{company_name}</code><br>
                                        <code class="text-blue-600">{work_types}</code> <code class="text-blue-600">{property_type}</code> <code class="text-blue-600">{surface}</code><br>
                                        <code class="text-blue-600">{phone}</code> <code class="text-blue-600">{email}</code> <code class="text-blue-600">{postal_code}</code>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Éditeur HTML -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contenu HTML de l'email</label>
                                <textarea name="html_content" rows="15" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" placeholder="Entrez le HTML de votre email...">{{ setting('email_client_template', '') }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Utilisez les variables ci-dessus pour personnaliser le contenu</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Enregistrer Template Client
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Template par défaut -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">📋 Templates par Défaut</h3>
                    <p class="text-sm text-gray-600 mb-4">Utilisez ces templates comme base pour créer vos propres emails</p>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <button onclick="loadDefaultTemplate('client')" class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg text-left">
                            <h4 class="font-semibold">Template Client par défaut</h4>
                            <p class="text-sm text-gray-600">Email de confirmation avec design professionnel</p>
                        </button>
                    </div>
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
                    
                    <!-- Save Button -->
                    <div class="flex justify-end">
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                        <input type="url" name="facebook_url" value="{{ setting('facebook_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                        <input type="url" name="instagram_url" value="{{ setting('instagram_url') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                        </div>
                    </div>

                    <!-- Couleurs du site -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-semibold mb-3">🎨 Couleurs du Site</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Couleur principale</label>
                                <input type="color" name="primary_color" value="{{ setting('primary_color', '#3b82f6') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">Couleur des boutons et liens</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Couleur secondaire</label>
                                <input type="color" name="secondary_color" value="{{ setting('secondary_color', '#10b981') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">Couleur d'accent</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Couleur d'accent</label>
                                <input type="color" name="accent_color" value="{{ setting('accent_color', '#f59e0b') }}" class="w-full h-10 border border-gray-300 rounded-lg">
                                <p class="text-xs text-gray-500 mt-1">Couleur de mise en valeur</p>
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

</script>
@endsection


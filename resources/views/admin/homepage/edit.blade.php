@extends('layouts.admin')

@section('title', 'Configuration de la Page d\'Accueil')

@section('content')
<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🏠 Configuration de la Page d'Accueil</h1>
            <p class="text-gray-600 mt-1">Personnalisez tous les aspects de votre page d'accueil</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ url('/') }}" target="_blank" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-eye mr-2"></i>Prévisualiser
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul>
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.homepage.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Trust Badges (Garantie, RGE, etc.) -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-shield-alt text-green-500 mr-2"></i>Badges de Confiance
            </h2>
            
            <div class="grid md:grid-cols-2 gap-6">
                <div class="flex items-center">
                    <input type="checkbox" name="trust_badges[garantie_decennale]" id="garantie_decennale" value="1" 
                           {{ ($config['trust_badges']['garantie_decennale'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="garantie_decennale" class="ml-2 text-sm font-medium text-gray-700">
                        <i class="fas fa-shield-alt mr-1"></i>Garantie Décennale
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="trust_badges[certifie_rge]" id="certifie_rge" value="1" 
                           {{ ($config['trust_badges']['certifie_rge'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="certifie_rge" class="ml-2 text-sm font-medium text-gray-700">
                        <i class="fas fa-certificate mr-1"></i>Certifié RGE
                    </label>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="trust_badges[show_rating]" id="show_rating" value="1" 
                           {{ ($config['trust_badges']['show_rating'] ?? true) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                    <label for="show_rating" class="ml-2 text-sm font-medium text-gray-700">
                        <i class="fas fa-star mr-1"></i>Afficher la Note et Avis
                    </label>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-star text-yellow-500 mr-2"></i>Section Héro (Bannière Principale)
                </h2>
                <div class="flex gap-3">
                    <button type="button" onclick="generateAIContent()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-magic mr-2"></i>Générer Hero
                    </button>
                    <button type="button" onclick="generateAllAIContent()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-robot mr-2"></i>Générer TOUT
                    </button>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-image mr-2 text-blue-500"></i>Image de Fond du Héro
                    </label>
                    <input type="file" name="hero_background" accept="image/*" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    @if($config['hero']['background_image'] ?? null)
                    <div class="mt-2">
                        <img src="{{ asset($config['hero']['background_image']) }}" alt="Background actuel" class="max-h-32 rounded border">
                        <label class="inline-flex items-center mt-2">
                            <input type="checkbox" name="remove_hero_background" value="1" class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-600">Supprimer l'image actuelle</span>
                        </label>
                    </div>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">Image de fond pour la section héro (recommandé: 1920x800px)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre Principal</label>
                    <input type="text" name="hero[title]" value="{{ $config['hero']['title'] }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Texte du Bouton CTA</label>
                    <input type="text" name="hero[cta_text]" value="{{ $config['hero']['cta_text'] }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sous-titre</label>
                    <textarea name="hero[subtitle]" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                              required>{{ $config['hero']['subtitle'] }}</textarea>
                </div>

                <div class="md:col-span-2 flex items-center">
                    <input type="checkbox" name="hero[show_phone]" id="hero_show_phone" value="1" 
                           {{ $config['hero']['show_phone'] ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="hero_show_phone" class="ml-2 text-sm text-gray-700">
                        Afficher le bouton téléphone dans le héro
                    </label>
                </div>
            </div>
        </div>

        <!-- About Section Content -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>Section À Propos
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                    <input type="text" name="about[title]" value="{{ $config['about']['title'] ?? 'Qui Sommes-Nous ?' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contenu À Propos</label>
                    <textarea name="about[content]" rows="5" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $config['about']['content'] ?? '' }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Description de votre entreprise, votre histoire, vos valeurs</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image de la Section (Format Carré)</label>
                    <input type="file" name="about_image" accept="image/*" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    @if(!empty($config['about']['image']))
                        <div class="mt-2">
                            <img src="{{ asset($config['about']['image']) }}" alt="Image actuelle" class="w-32 h-32 object-cover rounded-lg">
                            <div class="mt-2">
                                <input type="checkbox" name="remove_about_image" id="remove_about_image" value="1" 
                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <label for="remove_about_image" class="ml-2 text-sm text-red-600">
                                    Supprimer l'image actuelle
                                </label>
                            </div>
                        </div>
                    @endif
                    <p class="text-sm text-gray-500 mt-1">Image carrée recommandée (1:1) pour la section "Qui Sommes-Nous"</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="about[enabled]" id="about_enabled" value="1" 
                           {{ ($config['about']['enabled'] ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="about_enabled" class="ml-2 text-sm text-gray-700">
                        Afficher la section À Propos
                    </label>
                </div>
            </div>
        </div>

        <!-- Ecology Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-leaf text-green-500 mr-2"></i>Engagement Écologique
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input type="text" name="ecology[title]" value="{{ $config['ecology']['title'] ?? 'Notre Engagement Écologique' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="ecology[content]" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $config['ecology']['content'] ?? '' }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Votre engagement pour l'environnement, matériaux écologiques, etc.</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="ecology[enabled]" id="ecology_enabled" value="1" 
                           {{ ($config['ecology']['enabled'] ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="ecology_enabled" class="ml-2 text-sm text-gray-700">
                        Afficher la section Écologie
                    </label>
                </div>
            </div>
        </div>

        <!-- Aides et Financements Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-euro-sign text-orange-500 mr-2"></i>Aides et Financements
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input type="text" name="financing[title]" value="{{ $config['financing']['title'] ?? 'Aides et Financements Disponibles' }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="financing[content]" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $config['financing']['content'] ?? '' }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">MaPrimeRénov', éco-PTZ, CEE, aides locales, etc.</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="financing[enabled]" id="financing_enabled" value="1" 
                           {{ ($config['financing']['enabled'] ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                    <label for="financing_enabled" class="ml-2 text-sm text-gray-700">
                        Afficher la section Aides et Financements
                    </label>
                </div>
            </div>
        </div>

        <!-- Footer Configuration -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-map-marked-alt text-purple-500 mr-2"></i>Configuration du Footer
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone d'Intervention (Texte Libre)</label>
                    <textarea name="footer[intervention_zone]" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $config['footer']['intervention_zone'] ?? 'Nous intervenons dans toute la région ' . setting('company_region', 'Île-de-France') . ' et ses environs pour tous vos projets de rénovation.' }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Description de votre zone d'intervention (remplace la liste de villes)</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mini À Propos (Footer)</label>
                    <textarea name="footer[about]" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ $config['footer']['about'] ?? setting('company_description', '') }}</textarea>
                    <p class="text-sm text-gray-500 mt-1">Courte description de votre entreprise pour le footer</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="footer[show_cities]" id="show_cities" value="1" 
                           {{ ($config['footer']['show_cities'] ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="show_cities" class="ml-2 text-sm text-gray-700">
                        Afficher la liste des villes (au lieu du texte libre)
                    </label>
                </div>
            </div>
        </div>

        <!-- Sections -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-layer-group text-blue-500 mr-2"></i>Sections de la Page
            </h2>

            <div class="space-y-6">
                <!-- Services Section -->
                <div class="border-l-4 border-blue-500 pl-4 bg-blue-50 p-4 rounded-r-lg">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="sections[services][enabled]" id="section_services" value="1" 
                               {{ $config['sections']['services']['enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="section_services" class="ml-2 text-lg font-semibold text-gray-700">
                            <i class="fas fa-tools mr-2"></i>Section Services
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                            <input type="text" name="sections[services][title]" value="{{ $config['sections']['services']['title'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre à Afficher</label>
                            <input type="number" name="sections[services][limit]" value="{{ $config['sections']['services']['limit'] }}" 
                                   min="1" max="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Portfolio Section -->
                <div class="border-l-4 border-green-500 pl-4 bg-green-50 p-4 rounded-r-lg">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="sections[portfolio][enabled]" id="section_portfolio" value="1" 
                               {{ $config['sections']['portfolio']['enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <label for="section_portfolio" class="ml-2 text-lg font-semibold text-gray-700">
                            <i class="fas fa-images mr-2"></i>Section Réalisations
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                            <input type="text" name="sections[portfolio][title]" value="{{ $config['sections']['portfolio']['title'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre à Afficher</label>
                            <input type="number" name="sections[portfolio][limit]" value="{{ $config['sections']['portfolio']['limit'] }}" 
                                   min="1" max="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="border-l-4 border-yellow-500 pl-4 bg-yellow-50 p-4 rounded-r-lg">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="sections[reviews][enabled]" id="section_reviews" value="1" 
                               {{ $config['sections']['reviews']['enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                        <label for="section_reviews" class="ml-2 text-lg font-semibold text-gray-700">
                            <i class="fas fa-star mr-2"></i>Section Avis Clients
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                            <input type="text" name="sections[reviews][title]" value="{{ $config['sections']['reviews']['title'] }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nombre à Afficher</label>
                            <input type="number" name="sections[reviews][limit]" value="{{ $config['sections']['reviews']['limit'] }}" 
                                   min="1" max="12" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                <!-- Why Choose Us Section -->
                <div class="border-l-4 border-purple-500 pl-4 bg-purple-50 p-4 rounded-r-lg">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="sections[why_choose_us][enabled]" id="section_why" value="1" 
                               {{ ($config['sections']['why_choose_us']['enabled'] ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label for="section_why" class="ml-2 text-lg font-semibold text-gray-700">
                            <i class="fas fa-lightbulb mr-2"></i>Section "Pourquoi Nous Choisir?"
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                        <input type="text" name="sections[why_choose_us][title]" value="{{ $config['sections']['why_choose_us']['title'] ?? 'Pourquoi Nous Choisir?' }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="border-l-4 border-red-500 pl-4 bg-red-50 p-4 rounded-r-lg">
                    <div class="flex items-center mb-3">
                        <input type="checkbox" name="sections[cta][enabled]" id="section_cta" value="1" 
                               {{ $config['sections']['cta']['enabled'] ? 'checked' : '' }}
                               class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <label for="section_cta" class="ml-2 text-lg font-semibold text-gray-700">
                            <i class="fas fa-bullhorn mr-2"></i>Section Appel à l'Action (CTA Final)
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la Section</label>
                        <input type="text" name="sections[cta][title]" value="{{ $config['sections']['cta']['title'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                <i class="fas fa-chart-line text-green-500 mr-2"></i>Statistiques & Chiffres Clés
            </h2>
            <p class="text-gray-600 mb-4">Ces chiffres s'affichent juste en dessous de la bannière principale</p>

            <div id="stats-container" class="space-y-4">
                @foreach($config['stats'] as $index => $stat)
                <div class="flex items-center gap-4 p-4 border border-gray-300 rounded-lg stat-item hover:border-blue-500 transition">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Icône Font Awesome</label>
                        <input type="text" placeholder="fa-check-circle" 
                               value="{{ $stat['icon'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-icon">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Valeur</label>
                        <input type="text" placeholder="500+" 
                               value="{{ $stat['value'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-value">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Label</label>
                        <input type="text" placeholder="Projets Réalisés" 
                               value="{{ $stat['label'] }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-label">
                    </div>
                    <button type="button" onclick="removeStat(this)" class="text-red-600 hover:text-red-800 mt-4">
                        <i class="fas fa-trash text-xl"></i>
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" onclick="addStat()" class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter une Statistique
            </button>

            <input type="hidden" name="stats_json" id="stats_json" value="">
        </div>

        <!-- Partners Logos -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-handshake text-purple-500 mr-2"></i>Logos des Partenaires
                    </h2>
                    <p class="text-gray-600 mt-2">Ajoutez les logos de vos partenaires. Ils apparaîtront sur la page d'accueil avec un bouton pour les afficher/masquer.</p>
                </div>
                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="partners[enabled]" value="1" 
                               {{ ($config['partners']['enabled'] ?? false) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 w-5 h-5">
                        <span class="ml-2 text-sm font-medium text-gray-700">Afficher sur le site</span>
                    </label>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Titre de la section</label>
                <input type="text" name="partners[title]" 
                       value="{{ $config['partners']['title'] ?? 'Nos Partenaires' }}" 
                       placeholder="Nos Partenaires"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <p class="text-xs text-gray-500 mt-1">Titre qui s'affichera au-dessus des logos de partenaires</p>
            </div>

            <div id="partners-container" class="space-y-4">
                @php
                    $partners = $config['partners']['logos'] ?? [];
                @endphp
                @if(!empty($partners))
                    @foreach($partners as $index => $partner)
                    <div class="partner-item p-4 border border-gray-300 rounded-lg hover:border-purple-500 transition">
                        <div class="grid md:grid-cols-4 gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                                @if(!empty($partner['logo']))
                                <div class="mb-2">
                                    <img src="{{ asset($partner['logo']) }}" alt="Logo partenaire" class="max-h-16 max-w-32 object-contain border border-gray-200 rounded p-2">
                                </div>
                                @endif
                                <input type="file" name="partner_logos[]" accept="image/*" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <p class="text-xs text-gray-500 mt-1">Format: PNG, JPG (max 2MB)</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du partenaire</label>
                                <input type="text" name="partner_names[]" 
                                       value="{{ $partner['name'] ?? '' }}" 
                                       placeholder="Nom du partenaire"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">URL (optionnel)</label>
                                <input type="url" name="partner_urls[]" 
                                       value="{{ $partner['url'] ?? '' }}" 
                                       placeholder="https://..."
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <button type="button" onclick="removePartner(this)" 
                                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                                    <i class="fas fa-trash mr-2"></i>Supprimer
                                </button>
                            </div>
                        </div>
                        @if(!empty($partner['logo']))
                        <input type="hidden" name="existing_partner_logos[]" value="{{ $partner['logo'] }}">
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>

            <button type="button" onclick="addPartner()" class="mt-4 bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter un Partenaire
            </button>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-4 sticky bottom-4 bg-white p-4 rounded-lg shadow-lg">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                <i class="fas fa-times mr-2"></i>Annuler
            </a>
            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i>Sauvegarder les Modifications
            </button>
        </div>
    </form>
</div>

<script>
    // Serialize data before form submission
    document.querySelector('form').addEventListener('submit', function() {
        // Serialize stats
        const stats = [];
        document.querySelectorAll('.stat-item').forEach(item => {
            const icon = item.querySelector('.stat-icon').value;
            const value = item.querySelector('.stat-value').value;
            const label = item.querySelector('.stat-label').value;
            if (icon && value && label) {
                stats.push({ icon, value, label });
            }
        });
        document.getElementById('stats_json').value = JSON.stringify(stats);
        
    });

    function addStat() {
        const container = document.getElementById('stats-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-4 p-4 border border-gray-300 rounded-lg stat-item hover:border-blue-500 transition';
        div.innerHTML = `
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Icône Font Awesome</label>
                <input type="text" placeholder="fa-check-circle" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-icon">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Valeur</label>
                <input type="text" placeholder="500+" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-value">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Label</label>
                <input type="text" placeholder="Projets Réalisés" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg stat-label">
            </div>
            <button type="button" onclick="removeStat(this)" class="text-red-600 hover:text-red-800 mt-4">
                <i class="fas fa-trash text-xl"></i>
            </button>
        `;
        container.appendChild(div);
    }

    function addPartner() {
        const container = document.getElementById('partners-container');
        const div = document.createElement('div');
        div.className = 'partner-item p-4 border border-gray-300 rounded-lg hover:border-purple-500 transition';
        div.innerHTML = `
            <div class="grid md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
                    <input type="file" name="partner_logos[]" accept="image/*" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <p class="text-xs text-gray-500 mt-1">Format: PNG, JPG (max 2MB)</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom du partenaire</label>
                    <input type="text" name="partner_names[]" 
                           placeholder="Nom du partenaire"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL (optionnel)</label>
                    <input type="url" name="partner_urls[]" 
                           placeholder="https://..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <button type="button" onclick="removePartner(this)" 
                            class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Supprimer
                    </button>
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removePartner(btn) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?')) {
            btn.closest('.partner-item').remove();
        }
    }

    function removeStat(btn) {
        btn.closest('.stat-item').remove();
    }


    function generateAIContent() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération en cours...';

        fetch('{{ route('admin.homepage.generate-ai') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('input[name="hero[title]"]').value = data.content.hero_title || '';
                document.querySelector('textarea[name="hero[subtitle]"]').value = data.content.hero_subtitle || '';
                document.querySelector('input[name="hero[cta_text]"]').value = data.content.hero_cta_text || '';
                
                alert('✅ Contenu généré avec succès par l\'IA!');
            } else {
                alert('❌ Erreur : ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            alert('❌ Erreur : ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function generateAllAIContent() {
        console.log('🚀 Début génération IA complète');
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Génération complète en cours...';

        console.log('📡 Envoi requête vers:', '{{ route('admin.homepage.generate-all-ai') }}');
        
        fetch('{{ route('admin.homepage.generate-all-ai') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('📥 Réponse reçue:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📊 Données reçues:', data);
            if (data.success) {
                // Hero section
                if (data.content.hero) {
                    document.querySelector('input[name="hero[title]"]').value = data.content.hero.title || '';
                    document.querySelector('textarea[name="hero[subtitle]"]').value = data.content.hero.subtitle || '';
                    document.querySelector('input[name="hero[cta_text]"]').value = data.content.hero.cta_text || '';
                }
                
                // About section
                if (data.content.about) {
                    console.log('📝 Traitement section About:', data.content.about);
                    document.querySelector('input[name="about[title]"]').value = data.content.about.title || '';
                    document.querySelector('textarea[name="about[content]"]').value = data.content.about.content || '';
                    // Activer la section À propos
                    document.querySelector('input[name="about[enabled]"]').checked = true;
                    console.log('✅ Section About remplie et activée');
                } else {
                    console.log('❌ Pas de données About dans la réponse');
                }
                
                // Ecology section
                if (data.content.ecology) {
                    console.log('🌱 Traitement section Ecology:', data.content.ecology);
                    document.querySelector('input[name="ecology[title]"]').value = data.content.ecology.title || '';
                    document.querySelector('textarea[name="ecology[content]"]').value = data.content.ecology.content || '';
                    // Activer la section Écologie
                    document.querySelector('input[name="ecology[enabled]"]').checked = true;
                    console.log('✅ Section Ecology remplie et activée');
                } else {
                    console.log('❌ Pas de données Ecology dans la réponse');
                }
                
                // Financing section
                if (data.content.financing) {
                    console.log('💰 Traitement section Financing:', data.content.financing);
                    document.querySelector('input[name="financing[title]"]').value = data.content.financing.title || '';
                    document.querySelector('textarea[name="financing[content]"]').value = data.content.financing.content || '';
                    // Activer la section Aides et Financements
                    document.querySelector('input[name="financing[enabled]"]').checked = true;
                    console.log('✅ Section Financing remplie et activée');
                } else {
                    console.log('❌ Pas de données Financing dans la réponse');
                }
                
                // Footer
                if (data.content.footer) {
                    document.querySelector('textarea[name="footer[about]"]').value = data.content.footer.about || '';
                    document.querySelector('textarea[name="footer[intervention_zone]"]').value = data.content.footer.intervention_zone || '';
                }
                
                alert('✅ TOUT le contenu généré avec succès par l\'IA!\n\nLes sections À Propos, Écologie et Aides ont été activées automatiquement.\n\nLa page va se recharger pour sauvegarder les modifications...');
                
                // Sauvegarder automatiquement et recharger
                setTimeout(() => {
                    document.querySelector('form').submit();
                }, 2000);
            } else {
                alert('❌ Erreur : ' + (data.error || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            alert('❌ Erreur : ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
@endsection











<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administration') - {{ setting('company_name', 'Sauser Couverture') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
        }
        
        .sidebar-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .sidebar-link.active i {
            color: white;
        }
        
        .sidebar-link:hover {
            background-color: #f3f4f6;
        }
        
        .sidebar-link.active:hover {
            background-color: var(--secondary-color);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b">
                <div class="flex items-center">
                    @php
                        $logoPath = setting('company_logo');
                        $logoUrl = null;
                        
                        if ($logoPath) {
                            // Vérifier si le logo existe au chemin spécifié
                            if (file_exists(public_path('uploads/' . $logoPath))) {
                                $logoUrl = asset('uploads/' . $logoPath);
                            } else {
                                // Chercher des fichiers logo dans uploads
                                $uploadsDir = public_path('uploads/');
                                $logoFiles = glob($uploadsDir . '*logo*');
                                if (!empty($logoFiles)) {
                                    $logoUrl = asset('uploads/' . basename($logoFiles[0]));
                                }
                            }
                        }
                    @endphp
                    
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ setting('company_name', 'Logo') }}" class="h-8 w-auto">
                        @else
                        <div class="h-8 w-8 rounded-full flex items-center justify-center" style="background-color: var(--primary-color)">
                            <i class="fas fa-building text-white text-sm"></i>
                        </div>
                        @endif
                    <span class="ml-3 text-lg font-semibold text-gray-900">{{ setting('company_name', 'Sauser Couverture') }}</span>
                </div>
                    </div>
                    
                    <!-- Navigation -->
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <!-- 1. Tableau de bord -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Tableau de bord</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('admin.submissions') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.submissions') ? 'active' : '' }}">
                            <i class="fas fa-file-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Leads
                        </a>
                        
                        <a href="{{ route('admin.phone-calls') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.phone-calls') ? 'active' : '' }}">
                            <i class="fas fa-phone mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Appels
                        </a>
                        
                        <a href="{{ route('admin.statistics') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Statistiques
                        </a>
                    </div>
                </div>

                <!-- 2. Contenu -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Contenu</div>
                    <div class="space-y-1">
                        <a href="{{ route('portfolio.admin.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('portfolio.admin.*') ? 'active' : '' }}">
                            <i class="fas fa-images mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Réalisations
                        </a>
                        
                        <a href="{{ route('services.admin.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('services.admin.*') ? 'active' : '' }}">
                            <i class="fas fa-tools mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Services
                        </a>
                        
                        <a href="{{ route('admin.homepage.edit') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.homepage.*') ? 'active' : '' }}">
                            <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Page d'Accueil
                        </a>
                    </div>
                </div>

                <!-- 3. Blog & SEO -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Blog & SEO</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.articles.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                            <i class="fas fa-newspaper mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Articles
                        </a>
                        
                        <a href="{{ route('admin.seo.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}">
                            <i class="fas fa-search mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            SEO
                        </a>
                    </div>
                </div>

                <!-- 4. Annonces Locales -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Annonces Locales</div>
                    <div class="space-y-1">
                        <a href="{{ route('admin.ads.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.ads.*') ? 'active' : '' }}">
                            <i class="fas fa-bullhorn mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Annonces
                        </a>
                        
                        <a href="{{ route('admin.cities.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                            <i class="fas fa-city mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Villes
                        </a>
                    </div>
                </div>

                <!-- 5. Paramètres -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Paramètres</div>
                    <div class="space-y-1">
                        <a href="{{ route('config.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('config.*') ? 'active' : '' }}">
                            <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Configuration
                        </a>
                        
                        <a href="{{ route('admin.legal.config') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.legal.*') ? 'active' : '' }}">
                            <i class="fas fa-gavel mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Informations Légales
                            </a>
                        </div>
                        </div>
                    </nav>
                    
                    <!-- User Info & Logout -->
                    <div class="flex-shrink-0 flex border-t border-gray-200 p-4">
                        <div class="flex-1 flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center" style="background-color: var(--primary-color)">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700">Administrateur</p>
                        <p class="text-xs text-gray-500">Connecté</p>
                    </div>
                </div>
                <div class="ml-3">
                    <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors duration-150">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('title', 'Administration')</h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">{{ now()->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
    
    @stack('scripts')
</body>
</html>

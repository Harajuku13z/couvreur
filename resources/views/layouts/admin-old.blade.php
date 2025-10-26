<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - ' . company('name', 'Simulateur'))</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @if(setting('site_favicon'))
    <link rel="icon" href="{{ asset(setting('site_favicon')) }}" type="image/x-icon">
    @endif
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: {{ setting('primary_color', '#3b82f6') }};
            --secondary-color: {{ setting('secondary_color', '#10b981') }};
            --accent-color: {{ setting('accent_color', '#f59e0b') }};
        }
        
        .sidebar-link {
            transition: all 0.2s;
        }
        
        .sidebar-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid var(--primary-color);
        }
        
        .sidebar-link.active {
            background-color: rgba(59, 130, 246, 0.1);
            border-left: 4px solid var(--primary-color);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            filter: brightness(1.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile sidebar overlay -->
        <div id="mobile-sidebar-overlay" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 hidden md:hidden"></div>
        
        <!-- Sidebar -->
        <div id="sidebar" class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64">
                <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto bg-white border-r border-gray-200 max-h-screen">
                    <!-- Logo / Company Name -->
                    <div class="flex items-center flex-shrink-0 px-4 mb-8">
                        @if(setting('company_logo'))
                            <img src="{{ asset(setting('company_logo')) }}" alt="{{ company('name') }}" class="h-10 w-auto object-contain">
                        @else
                            <h1 class="text-xl font-bold" style="color: var(--primary-color)">
                                {{ company('name', 'Admin') }}
                            </h1>
                        @endif
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="flex-1 px-2 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Dashboard
                        </a>
                        
                        <a href="{{ route('admin.submissions') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.submissions') ? 'active' : '' }}">
                            <i class="fas fa-file-alt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Leads
                        </a>
                        
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
                        
                        <a href="{{ route('admin.abandoned-submissions') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.abandoned-submissions') ? 'active' : '' }}">
                            <i class="fas fa-times-circle mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Abandonnées
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
                        
                        <a href="{{ route('admin.reviews.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                            <i class="fas fa-star mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Avis Clients
                        </a>
                        
                        <a href="{{ route('admin.homepage.edit') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.homepage.*') ? 'active' : '' }}">
                            <i class="fas fa-home mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Page d'Accueil
                        </a>
                        
                        <a href="{{ route('admin.seo.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.seo.*') ? 'active' : '' }}">
                            <i class="fas fa-search mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            SEO
                        </a>
                        
                        <a href="{{ route('admin.legal.config') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.legal.*') ? 'active' : '' }}">
                            <i class="fas fa-gavel mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Informations Légales
                        </a>

                        <!-- Annonces Menu -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider">Annonces</div>
                            <a href="{{ route('admin.ads.index') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.ads.*') ? 'active' : '' }}">
                                <i class="fas fa-bullhorn mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Toutes les annonces
                            </a>
                            <a href="{{ route('admin.cities.index') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}">
                                <i class="fas fa-city mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Villes
                            </a>
                        </div>
                        
                        <!-- Articles Menu -->
                        <div class="mt-4">
                            <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider">Blog</div>
                            <a href="{{ route('admin.articles.create') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.articles.create') ? 'active' : '' }}">
                                <i class="fas fa-plus mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Nouvel article
                            </a>
                            <a href="{{ route('admin.articles.index') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                                <i class="fas fa-newspaper mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Tous les articles
                            </a>
                        </div>
                        
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <a href="{{ route('config.index') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('config.*') ? 'active' : '' }}">
                                <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Configuration
                            </a>
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
                                <p class="text-sm font-medium text-gray-700">Admin</p>
                                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium text-gray-500 hover:text-gray-700">
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex flex-col flex-1 w-0 overflow-hidden">
            <!-- Top Bar (Mobile) -->
            <div class="relative z-10 flex-shrink-0 flex h-16 bg-white shadow md:hidden">
                <button type="button" id="mobile-menu-button" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars"></i>
                </button>
                <div class="flex-1 px-4 flex justify-between">
                    <div class="flex-1 flex items-center">
                        @if(setting('company_logo'))
                            <img src="{{ asset(setting('company_logo')) }}" alt="{{ company('name') }}" class="h-8 w-auto">
                        @else
                            <span class="text-lg font-bold" style="color: var(--primary-color)">{{ company('name') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Configuration CSRF pour AJAX
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}'
        };
        
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-sidebar-overlay');
            
            if (mobileMenuButton && sidebar && overlay) {
                mobileMenuButton.addEventListener('click', function() {
                    sidebar.classList.remove('hidden');
                    sidebar.classList.add('fixed', 'inset-y-0', 'left-0', 'z-50', 'w-64', 'overflow-y-auto');
                    overlay.classList.remove('hidden');
                });
                
                overlay.addEventListener('click', function() {
                    sidebar.classList.add('hidden');
                    sidebar.classList.remove('fixed', 'inset-y-0', 'left-0', 'z-50', 'w-64', 'overflow-y-auto');
                    overlay.classList.add('hidden');
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>






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
        
        .menu-dropdown {
            transition: all 0.3s ease;
        }
        
        .menu-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .menu-dropdown.open .menu-dropdown-content {
            max-height: 500px;
        }
        
        .menu-dropdown-toggle i {
            transition: transform 0.3s ease;
        }
        
        .menu-dropdown.open .menu-dropdown-toggle i {
            transform: rotate(90deg);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            
            .sidebar-overlay.open {
                display: block;
            }
            
            /* Tables responsive */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .table-responsive table {
                min-width: 640px;
            }
            
            /* Modals responsive */
            .modal-responsive {
                width: 95% !important;
                max-width: 95% !important;
                margin: 1rem auto !important;
            }
            
            /* Flex responsive */
            .flex-responsive {
                flex-direction: column;
            }
            
            .flex-responsive > * {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            /* Stats cards responsive */
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
            
            /* Buttons stack on mobile */
            .btn-group-mobile {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group-mobile > * {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        @media (min-width: 769px) {
            .table-responsive {
                overflow-x: visible;
            }
        }
        
        /* Empêcher le scroll horizontal sur mobile */
        @media (max-width: 768px) {
            body {
                overflow-x: hidden;
            }
            
            .container, .max-w-7xl, .max-w-6xl {
                max-width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            /* Forcer les flex containers à revenir à la ligne */
            .flex-nowrap {
                flex-wrap: wrap !important;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay lg:hidden" onclick="toggleSidebar()"></div>
    
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-64 bg-white shadow-lg flex flex-col lg:relative lg:translate-x-0">
            <!-- Logo -->
            <div class="p-6 border-b">
                <div class="flex items-center">
                    @if(setting('company_logo'))
                        <img src="{{ asset(setting('company_logo')) }}" alt="{{ setting('company_name', 'Logo') }}" class="h-8 w-auto">
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
                <!-- 1. Tableau de bord - Menu déroulant -->
                <div class="mb-6 menu-dropdown {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.submissions') || request()->routeIs('admin.phone-calls') || request()->routeIs('admin.visits') || request()->routeIs('admin.statistics') ? 'open' : '' }}">
                    <button onclick="toggleMenuDropdown(this)" class="menu-dropdown-toggle w-full px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold flex items-center justify-between hover:bg-gray-50 rounded-md transition-colors">
                        <span>Tableau de bord</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                    <div class="menu-dropdown-content mt-1">
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
                            
                            <a href="{{ route('admin.visits') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.visits') ? 'active' : '' }}">
                                <i class="fas fa-chart-line mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Visites
                            </a>
                            
                            <a href="{{ route('admin.statistics') }}" 
                               class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.statistics') ? 'active' : '' }}">
                                <i class="fas fa-chart-bar mr-3 text-gray-400 group-hover:text-gray-500"></i>
                                Statistiques
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 2. Devis & Facturation -->
                <div class="mb-6">
                    <div class="px-3 py-2 text-xs text-gray-400 uppercase tracking-wider font-semibold">Devis & Facturation</div>
                    <div class="space-y-1">
                        @if(Route::has('admin.quotations.dashboard'))
                        <a href="{{ route('admin.quotations.dashboard') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-pie mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Tableau de bord
                        </a>
                        @endif
                        
                        <a href="{{ route('admin.devis.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.devis.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Devis
                        </a>
                        
                        <a href="{{ route('admin.devis.create') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.devis.create') ? 'active' : '' }}">
                            <i class="fas fa-plus-circle mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Créer un devis
                        </a>
                        
                        <a href="{{ route('admin.factures.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.factures.*') ? 'active' : '' }}">
                            <i class="fas fa-receipt mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Factures
                        </a>
                        
                        <a href="{{ route('admin.clients.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                            <i class="fas fa-users mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Clients
                        </a>
                    </div>
                </div>

                <!-- 3. Contenu -->
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
                        
                        <a href="{{ route('admin.reviews.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                            <i class="fas fa-star mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Avis
                        </a>
                    </div>
                </div>

                <!-- 4. Blog & SEO -->
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
                        
                        <a href="{{ route('admin.indexation.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.indexation.*') ? 'active' : '' }}">
                            <i class="fas fa-spider mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Indexation
                        </a>
                        
                        <a href="{{ route('admin.seo-automation.index') }}" 
                           class="sidebar-link group flex items-center px-3 py-2 text-sm font-medium text-gray-600 rounded-md {{ request()->routeIs('admin.seo-automation.*') ? 'active' : '' }}">
                            <i class="fas fa-robot mr-3 text-gray-400 group-hover:text-gray-500"></i>
                            Automatisation SEO
                        </a>
                    </div>
                </div>

                <!-- 5. Annonces Locales -->
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

                <!-- 6. Paramètres -->
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
                <div class="px-4 md:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Mobile Menu Button -->
                            <button onclick="toggleSidebar()" class="lg:hidden text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <h1 class="text-xl md:text-2xl font-semibold text-gray-900">@yield('title', 'Administration')</h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-xs md:text-sm text-gray-500 hidden sm:inline">{{ now()->format('d/m/Y H:i') }}</span>
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
    
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
        
        // Toggle menu dropdown
        function toggleMenuDropdown(button) {
            const menu = button.closest('.menu-dropdown');
            menu.classList.toggle('open');
        }
        
        // Close sidebar when clicking on a link (mobile)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        toggleSidebar();
                    }
                });
            });
        });
        
        // Close sidebar on window resize if desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                sidebar.classList.remove('open');
                overlay.classList.remove('open');
            }
        });
    </script>
</body>
</html>

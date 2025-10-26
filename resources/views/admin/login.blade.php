<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - {{ config('company.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-900 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto">
            <!-- Logo -->
            <div class="text-center mb-8">
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
                    <img src="{{ $logoUrl }}" alt="{{ setting('company_name', 'Logo') }}" class="h-16 mx-auto mb-4 object-contain">
                @else
                    <div class="h-16 w-16 mx-auto mb-4 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-2xl" style="color: var(--primary-color, #3b82f6);"></i>
                    </div>
                @endif
                <h1 class="text-3xl font-bold text-white mb-2">Administration</h1>
                <p class="text-blue-200">{{ setting('company_name', config('company.name')) }}</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Connexion</h2>

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.authenticate') }}">
                    @csrf
                    
                    <!-- Username -->
                    <div class="mb-6">
                        <label for="username" class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-user mr-2"></i>Nom d'utilisateur
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                               placeholder="Entrez votre nom d'utilisateur"
                               required
                               autofocus>
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-lock mr-2"></i>Mot de passe
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition"
                                   placeholder="Entrez votre mot de passe"
                                   required>
                            <button type="button" 
                                    onclick="togglePassword()" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-gray-700">Se souvenir de moi</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition transform hover:scale-105 shadow-lg">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Se connecter
                    </button>
                </form>

                <!-- Info -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Accès réservé aux administrateurs
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-blue-200 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('company.name') }}. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.bg-red-100, .bg-green-100');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>

























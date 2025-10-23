<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Initiale - Simulateur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .step-wizard {
            display: none;
        }
        .step-wizard.active {
            display: block;
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.completed {
            background: #10b981;
            color: white;
        }
        .step-indicator.active {
            background: #3b82f6;
            color: white;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block bg-white rounded-full p-4 shadow-lg mb-6">
                <i class="fas fa-cog text-6xl text-blue-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Bienvenue !</h1>
            <p class="text-xl text-gray-600">Configurons votre simulateur en quelques étapes</p>
        </div>

        <!-- Progress Indicator -->
        <div class="max-w-4xl mx-auto mb-12">
            <div class="flex justify-between items-center">
                <div class="flex-1 text-center">
                    <div class="step-indicator completed w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-2 font-bold" data-step="1">1</div>
                    <p class="text-sm text-gray-600">Entreprise</p>
                </div>
                <div class="flex-1 border-t-4 border-gray-300" id="line-1"></div>
                <div class="flex-1 text-center">
                    <div class="step-indicator w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-2 font-bold" data-step="2">2</div>
                    <p class="text-sm text-gray-600">Administrateur</p>
                </div>
                <div class="flex-1 border-t-4 border-gray-300" id="line-2"></div>
                <div class="flex-1 text-center">
                    <div class="step-indicator w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-2 font-bold" data-step="3">3</div>
                    <p class="text-sm text-gray-600">Email & Logo</p>
                </div>
                <div class="flex-1 border-t-4 border-gray-300" id="line-3"></div>
                <div class="flex-1 text-center">
                    <div class="step-indicator w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-2 font-bold" data-step="4">4</div>
                    <p class="text-sm text-gray-600">Terminé</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('config.setup.process') }}" enctype="multipart/form-data" class="max-w-4xl mx-auto">
            @csrf

            <!-- Step 1: Company Info -->
            <div class="step-wizard active bg-white rounded-2xl shadow-xl p-8" data-step="1">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-building text-blue-600 mr-3"></i>
                    Informations de l'Entreprise
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Nom de l'entreprise *</label>
                        <input type="text" name="company_name" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Ex: Rénovation Pro">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Raison sociale</label>
                        <input type="text" name="company_legal_name"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Ex: Rénovation Pro SARL">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Slogan</label>
                        <input type="text" name="company_slogan"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Ex: Votre expert en rénovation">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Description</label>
                        <textarea name="company_description" rows="3"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Décrivez votre entreprise en quelques lignes..."></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Téléphone *</label>
                        <input type="tel" name="company_phone" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Ex: 01 23 45 67 89">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                        <input type="email" name="company_email" required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="contact@exemple.fr">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Adresse</label>
                        <input type="text" name="company_address"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="123 Rue de la Rénovation">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Ville</label>
                        <input type="text" name="company_city"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Paris">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Code postal</label>
                        <input type="text" name="company_postal_code"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="75001">
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="button" onclick="nextStep()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Admin Credentials -->
            <div class="step-wizard bg-white rounded-2xl shadow-xl p-8" data-step="2">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-user-shield text-blue-600 mr-3"></i>
                    Compte Administrateur
                </h2>

                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                        <div>
                            <p class="text-sm text-yellow-700">
                                <strong>Important :</strong> Choisissez un nom d'utilisateur et un mot de passe sécurisés. 
                                Ces identifiants vous permettront d'accéder à l'administration du site.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 max-w-2xl">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nom d'utilisateur *</label>
                        <input type="text" name="admin_username" required minlength="4"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="admin">
                        <p class="text-sm text-gray-500 mt-1">Minimum 4 caractères</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Mot de passe *</label>
                        <input type="password" name="admin_password" id="admin_password" required minlength="6"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="••••••••">
                        <p class="text-sm text-gray-500 mt-1">Minimum 6 caractères</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Confirmer le mot de passe *</label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required minlength="6"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="••••••••">
                        <p class="text-sm text-gray-500 mt-1">Resaisissez le même mot de passe</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">💡 Conseils de sécurité</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Utilisez un mot de passe unique</li>
                            <li>• Mélangez lettres, chiffres et caractères spéciaux</li>
                            <li>• Ne partagez jamais vos identifiants</li>
                            <li>• Vous pourrez les modifier plus tard dans les paramètres</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button type="button" onclick="prevStep()" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </button>
                    <button type="button" onclick="nextStep()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Email & Logo -->
            <div class="step-wizard bg-white rounded-2xl shadow-xl p-8" data-step="3">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-envelope text-blue-600 mr-3"></i>
                    Email & Branding
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">📧 Paramètres Email</h3>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email d'expédition</label>
                        <input type="email" name="mail_from_address"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="noreply@exemple.fr">
                        <p class="text-sm text-gray-500 mt-1">Laissez vide pour utiliser l'email de l'entreprise</p>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nom d'expédition</label>
                        <input type="text" name="mail_from_name"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none"
                            placeholder="Simulateur - Rénovation Pro">
                        <p class="text-sm text-gray-500 mt-1">Laissez vide pour utiliser le nom de l'entreprise</p>
                    </div>

                    <div class="md:col-span-2 mt-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">🎨 Logo de l'Entreprise</h3>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-semibold mb-2">Logo (optionnel)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                            <input type="file" name="logo" accept="image/*" id="logoInput" class="hidden" onchange="previewLogo(this)">
                            <div id="logoPreview" class="mb-4 hidden">
                                <img src="" alt="Logo preview" class="max-h-32 mx-auto rounded">
                            </div>
                            <button type="button" onclick="document.getElementById('logoInput').click()" class="bg-blue-100 text-blue-600 px-6 py-2 rounded-lg hover:bg-blue-200 transition">
                                <i class="fas fa-upload mr-2"></i> Choisir un logo
                            </button>
                            <p class="text-sm text-gray-500 mt-2">PNG, JPG, SVG ou WebP - Max 2 Mo</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-between">
                    <button type="button" onclick="prevStep()" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i> Retour
                    </button>
                    <button type="button" onclick="nextStep()" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                        Suivant <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Step 4: Confirmation -->
            <div class="step-wizard bg-white rounded-2xl shadow-xl p-8" data-step="4">
                <div class="text-center">
                    <div class="inline-block bg-green-100 rounded-full p-6 mb-6">
                        <i class="fas fa-check-circle text-6xl text-green-600"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Prêt à démarrer !</h2>
                    <p class="text-xl text-gray-600 mb-8">
                        Votre simulateur est configuré et prêt à l'emploi.
                    </p>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8 text-left max-w-2xl mx-auto">
                        <h3 class="font-semibold text-blue-800 mb-4 text-center">📋 Récapitulatif</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-600 mr-3"></i>
                                <span>Informations de l'entreprise configurées</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-600 mr-3"></i>
                                <span>Compte administrateur créé</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-600 mr-3"></i>
                                <span>Paramètres email configurés</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check text-green-600 mr-3"></i>
                                <span>Vous pourrez modifier tous les paramètres depuis l'admin</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" onclick="prevStep()" class="bg-gray-300 text-gray-700 px-8 py-3 rounded-lg hover:bg-gray-400 transition font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Retour
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition font-semibold text-lg">
                            <i class="fas fa-rocket mr-2"></i> Terminer la configuration
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1;

        function showStep(step) {
            document.querySelectorAll('.step-wizard').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelector(`.step-wizard[data-step="${step}"]`).classList.add('active');

            // Update indicators
            document.querySelectorAll('.step-indicator').forEach((el, index) => {
                const elStep = index + 1;
                el.classList.remove('completed', 'active');
                if (elStep < step) {
                    el.classList.add('completed');
                } else if (elStep === step) {
                    el.classList.add('active');
                }
            });

            // Update lines
            for (let i = 1; i <= 3; i++) {
                const line = document.getElementById(`line-${i}`);
                if (i < step) {
                    line.classList.remove('border-gray-300');
                    line.classList.add('border-green-500');
                } else {
                    line.classList.remove('border-green-500');
                    line.classList.add('border-gray-300');
                }
            }
        }

        function nextStep() {
            console.log('🔄 nextStep() appelé, étape actuelle:', currentStep);
            
            // Validation basique avant de passer à l'étape suivante
            if (currentStep === 1) {
                const companyName = document.querySelector('[name="company_name"]').value;
                const companyPhone = document.querySelector('[name="company_phone"]').value;
                const companyEmail = document.querySelector('[name="company_email"]').value;
                
                console.log('📝 Étape 1 - Validation:', { companyName, companyPhone, companyEmail });
                
                if (!companyName || !companyPhone || !companyEmail) {
                    alert('⚠️ Veuillez remplir tous les champs obligatoires (Nom, Téléphone, Email)');
                    return;
                }
            }
            
            if (currentStep === 2) {
                const username = document.querySelector('[name="admin_username"]').value;
                const password = document.querySelector('[name="admin_password"]').value;
                const passwordConfirm = document.querySelector('[name="admin_password_confirmation"]').value;
                
                console.log('👤 Étape 2 - Validation:', { username, password: password ? '***' : '', passwordConfirm: passwordConfirm ? '***' : '' });
                
                if (!username || !password || !passwordConfirm) {
                    alert('⚠️ Veuillez remplir tous les champs du compte administrateur');
                    return;
                }
                
                if (password !== passwordConfirm) {
                    alert('⚠️ Les mots de passe ne correspondent pas');
                    return;
                }
                
                if (password.length < 6) {
                    alert('⚠️ Le mot de passe doit contenir au moins 6 caractères');
                    return;
                }
            }
            
            if (currentStep < 4) {
                currentStep++;
                console.log('➡️ Passage à l\'étape:', currentStep);
                showStep(currentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logoPreview');
                    preview.querySelector('img').src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Initialize
        showStep(1);

        // Feedback visuel lors de la soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('🚀 Formulaire soumis !');
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Configuration en cours...';
        });

        // Debug: Afficher l'étape actuelle
        function debugCurrentStep() {
            console.log('📍 Étape actuelle:', currentStep);
            const activeStep = document.querySelector('.step-wizard.active');
            console.log('📍 Étape active:', activeStep ? activeStep.getAttribute('data-step') : 'Aucune');
        }

        // Debug toutes les 2 secondes
        setInterval(debugCurrentStep, 2000);
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Maestro - VoXY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
        .text-primary { color: rgb(158, 2, 80); }
        .border-primary { border-color: rgb(158, 2, 80); }
        .gradient-bg {
            background: linear-gradient(177deg, #991b1b 0%, #7b1b4b 100%);
        }
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        .shape:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .shape:nth-child(2) { top: 20%; right: 10%; animation-delay: 2s; }
        .shape:nth-child(3) { bottom: 10%; left: 20%; animation-delay: 4s; }
        .shape:nth-child(4) { bottom: 20%; right: 20%; animation-delay: 1s; }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center relative">
    <!-- Floating shapes background -->
    <div class="floating-shapes">
        <div class="shape w-20 h-20 bg-white rounded-full"></div>
        <div class="shape w-16 h-16 bg-white rounded-lg"></div>
        <div class="shape w-12 h-12 bg-white rounded-full"></div>
        <div class="shape w-24 h-24 bg-white rounded-lg transform rotate-45"></div>
    </div>

    <div class="max-w-md w-full space-y-8 relative z-10" x-data="{ showPassword: false, loading: false }">
        <!-- Logo et titre -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 glass-effect rounded-full flex items-center justify-center mb-6">
                <i class="fas fa-user-tie text-3xl text-white"></i>
            </div>
            <h2 class="text-4xl font-bold text-white mb-2">
                Maestro Chorale
            </h2>
            <p class="text-white/80 text-lg">
                Connectez-vous à votre espace de gestion
            </p>
            <p class="mt-2 text-white/60 text-sm">
                Pas encore de chorale ? <a href="{{ route('register.chorale') }}" class="text-white hover:underline font-medium">Créez votre chorale</a>
            </p>
            <p class="mt-1 text-xs text-white/50">
                <a href="{{ route('login') }}" class="hover:underline">Connexion administrateur</a>
            </p>
        </div>
        
        <!-- Messages d'erreur -->
        @if ($errors->any())
            <div class="glass-effect border border-red-300/30 text-white px-4 py-3 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-300 mt-1 mr-3"></i>
                    <div class="flex-1">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button @click="show = false" class="text-white/60 hover:text-white ml-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        <!-- Formulaire de connexion -->
        <form class="mt-8 space-y-6 glass-effect p-8 rounded-2xl" action="{{ route('login.maestro.post') }}" method="POST" @submit="loading = true">
            @csrf
            
            <div class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-envelope mr-2"></i>Adresse email
                    </label>
                    <input id="email" name="email" type="email" required 
                           class="form-input w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent" 
                           placeholder="maestro@chorale.com"
                           value="{{ old('email') }}">
                </div>
                
                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        <i class="fas fa-lock mr-2"></i>Mot de passe
                    </label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required 
                               class="form-input w-full px-4 py-3 pr-12 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent" 
                               placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-white/60 hover:text-white">
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <!-- Se souvenir de moi -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-white/20 focus:ring-white/50 border-white/20 rounded bg-white/10">
                        <label for="remember" class="ml-2 block text-sm text-white/80">
                            Se souvenir de moi
                        </label>
                    </div>
                    <div class="text-sm">
                        <a href="#" class="text-white/80 hover:text-white transition-colors">
                            Mot de passe oublié ?
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bouton de connexion -->
            <div class="pt-4">
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-purple-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white transition-all duration-200 hover:shadow-lg"
                        :disabled="loading">
                    <span x-show="!loading" class="flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Se connecter
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Connexion...
                    </span>
                </button>
            </div>

            <!-- Informations -->
            <div class="text-center pt-4 border-t border-white/20">
                <p class="text-sm text-white/70 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Espace réservé aux maestros de chorale
                </p>
                <p class="text-xs text-white/60">
                    En vous connectant, vous accéderez à la gestion de votre chorale : configuration des pupitres, rubriques, partitions, etc.
                </p>
            </div>
        </form>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-white/60 text-sm">
                © 2024 VoXY. Tous droits réservés.
            </p>
        </div>
    </div>

    <script>
        // Focus animation
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            
            [emailInput, passwordInput].forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('transform', 'scale-105');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('transform', 'scale-105');
                });
            });
        });
    </script>
</body>
</html>


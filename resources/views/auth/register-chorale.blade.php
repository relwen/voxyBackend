<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre Chorale - VoXY</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .bg-primary-gradient { 
            background: linear-gradient(135deg, rgb(78, 13, 4), rgb(158, 2, 80), rgb(120, 2, 60));
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .input-glow:focus {
            box-shadow: 0 0 20px rgba(158, 2, 80, 0.4);
            border-color: rgba(255, 255, 255, 0.4);
        }

        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-primary-gradient min-h-screen selection:bg-white/30" x-data="{ loading: false }">
    <!-- Background Patterns -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-white/5 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute top-[20%] right-[10%] w-[30%] h-[30%] bg-black/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-10">
            <a href="{{ route('login.maestro') }}" class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-3xl shadow-2xl transform hover:rotate-6 transition-transform mb-6">
                <i class="fas fa-microphone-alt text-2xl text-[rgb(158,2,80)]"></i>
            </a>
            <h1 class="text-3xl font-black text-white tracking-tighter mb-2 uppercase">Nouvelle Chorale</h1>
            <p class="text-white/60 font-medium">Rejoignez l'univers musical de VoXY</p>
        </div>

        <!-- Form Container -->
        <div class="w-full max-w-4xl">
            <div class="glass rounded-[2.5rem] p-8 md:p-12 overflow-hidden relative">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <i class="fas fa-music text-8xl text-white"></i>
                </div>

                <form action="{{ route('register.chorale.store') }}" method="POST" @submit="loading = true" class="relative z-10 space-y-10">
                    @csrf
                    
                    @if($errors->any())
                        <div class="bg-red-500/20 border border-red-500/50 backdrop-blur-md rounded-2xl p-6 text-white animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i> Erreurs d'inscription</h3>
                                <button type="button" @click="show = false" class="text-white/60 hover:text-white"><i class="fas fa-times"></i></button>
                            </div>
                            <ul class="list-disc list-inside text-sm space-y-1 opacity-90">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Section 1: Maestro -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                                <i class="fas fa-user-tie text-white opacity-80"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">Le Maestro</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-4">
                                <div class="relative group">
                                    <input type="text" name="name" required value="{{ old('name') }}"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Votre nom complet">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                
                                <div class="relative group">
                                    <input type="email" name="email" required value="{{ old('email') }}"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Votre adresse email">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div class="relative group">
                                    <input type="password" name="password" required
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Mot de passe (8 caractères min)">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                </div>
                                
                                <div class="relative group">
                                    <input type="password" name="password_confirmation" required
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Confirmer le mot de passe">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <div class="relative group">
                                    <input type="tel" name="phone" required value="{{ old('phone') }}"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Téléphone (ex: +243 820 000 000)">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Chorale -->
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                                <i class="fas fa-music text-white opacity-80"></i>
                            </div>
                            <h2 class="text-xl font-bold text-white">La Chorale</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="relative group">
                                <input type="text" name="chorale_name" required value="{{ old('chorale_name') }}"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow text-lg font-bold"
                                    placeholder="Nom de votre chorale">
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative group">
                                    <input type="text" name="chorale_location" value="{{ old('chorale_location') }}"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                                        placeholder="Localisation (ex: Paris, France)">
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                </div>
                                <div class="relative group">
                                    <textarea name="chorale_description" rows="1"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow resize-none"
                                        placeholder="Bref résumé ou slogan...">{{ old('chorale_description') }}</textarea>
                                    <div class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                                        <i class="fas fa-quote-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Journey -->
                    <div class="bg-white/5 border border-white/10 rounded-3xl p-6">
                        <div class="flex gap-4">
                            <div class="h-10 w-10 shrink-0 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                                <i class="fas fa-bolt text-xs"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white mb-2 uppercase tracking-wider">Mise en place immédiate</h3>
                                <p class="text-white/50 text-xs leading-relaxed">En vous inscrivant, vous accédez instantanément à votre cockpit maestro avec les rubriques Messes et Vocalises déjà pré-configurées pour vous.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="flex flex-col gap-6 pt-4">
                        <button type="submit" 
                            class="w-full bg-white text-[rgb(158,2,80)] font-black py-5 rounded-[2rem] shadow-xl hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3"
                            :disabled="loading">
                            <template x-if="!loading">
                                <span class="flex items-center gap-3 uppercase tracking-tighter">
                                    CRÉER MA CHORALE <i class="fas fa-arrow-right"></i>
                                </span>
                            </template>
                            <template x-if="loading">
                                <div class="flex items-center gap-3">
                                    <svg class="animate-spin h-5 w-5 text-[rgb(158,2,80)]" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    TRAITEMENT...
                                </div>
                            </template>
                        </button>

                        <div class="flex items-center justify-center gap-8">
                             <a href="{{ route('login.maestro') }}" class="text-white/40 hover:text-white text-[10px] font-bold uppercase tracking-widest transition-all">
                                <i class="fas fa-arrow-left mr-2"></i> Retour Maestro
                            </a>
                            <div class="w-px h-4 bg-white/10"></div>
                            <a href="{{ route('login') }}" class="text-white/40 hover:text-white text-[10px] font-bold uppercase tracking-widest transition-all">
                                Portail Admin
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center mt-12 text-white/20 text-[10px] font-bold uppercase tracking-[0.2em]">
                &copy; 2026 KUILINGA TECHNOLOGIES
            </p>
        </div>
    </div>
</body>
</html>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoXY - Connexion Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;400;600;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-primary-gradient {
            background: linear-gradient(135deg, rgb(78, 13, 4), rgb(158, 2, 80), rgb(120, 2, 60));
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .input-glow:focus {
            box-shadow: 0 0 20px rgba(158, 2, 80, 0.4);
            border-color: rgba(255, 255, 255, 0.4);
        }
    </style>
</head>

<body class="bg-primary-gradient min-h-screen flex items-center justify-center p-4 overflow-hidden relative">
    <!-- Animated background patterns -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-white/5 rounded-full blur-3xl animate-pulse">
        </div>
        <div class="absolute -bottom-[10%] -right-[10%] w-[50%] h-[50%] bg-black/10 rounded-full blur-3xl"></div>
    </div>

    <div class="max-w-md w-full relative z-10" x-data="{ loading: false, showPass: false }">
        <!-- Logo Section -->
        <div class="text-center mb-10">
            <div
                class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-[2.5rem] shadow-2xl transform rotate-6 mb-6">
                <i class="fas fa-microphone-alt text-3xl text-[rgb(158,2,80)]"></i>
            </div>
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">VoXY <span
                    class="font-extralight opacity-80">Maestro</span></h1>
            <p class="text-white/60 font-medium">Gérez votre chorale avec élégance</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-[3rem] p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-8 opacity-5">
                <i class="fas fa-music text-9xl"></i>
            </div>

            <form action="{{ route('login.maestro.post') }}" method="POST" @submit="loading = true" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-500/50 backdrop-blur-md rounded-2xl p-4 text-white text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <div class="relative group">
                        <input type="email" name="email" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                            placeholder="test@maestro.com">
                        <div
                            class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 group-focus-within:text-white/60 transition-colors">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>

                    <div class="relative group">
                        <input :type="showPass ? 'text' : 'password'" name="password" required
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/30 outline-none transition-all input-glow"
                            placeholder="••••••••">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute right-6 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
                            <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between px-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="remember" class="hidden peer">
                        <div
                            class="w-5 h-5 rounded-md border border-white/20 peer-checked:bg-white peer-checked:border-white transition-all flex items-center justify-center">
                            <i
                                class="fas fa-check text-[rgb(158,2,80)] text-[10px] scale-0 peer-checked:scale-100 transition-transform"></i>
                        </div>
                        <span class="text-xs text-white/60 group-hover:text-white transition-colors">Rester
                            connecté</span>
                    </label>
                    <a href="#" class="text-xs text-white/40 hover:text-white transition-colors">Oublié ?</a>
                </div>

                <button type="submit"
                    class="w-full bg-white text-[rgb(158,2,80)] font-black py-5 rounded-[2rem] shadow-xl hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3"
                    :disabled="loading">
                    <template x-if="!loading">
                        <span class="flex items-center gap-3">
                            ENTRER <i class="fas fa-arrow-right"></i>
                        </span>
                    </template>
                    <template x-if="loading">
                        <i class="fas fa-circle-notch animate-spin"></i>
                    </template>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <p class="text-white/40 text-[10px] uppercase tracking-widest font-bold mb-4">Accès Alternatifs</p>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('register.chorale') }}"
                        class="text-white/60 hover:text-white text-xs font-semibold py-2 px-4 rounded-xl hover:bg-white/5 transition-all">
                        Créer une nouvelle chorale
                    </a>
                    <a href="{{ route('login') }}" class="text-white/30 hover:text-white/60 text-[10px] transition-all">
                        Accès Administration Générale
                    </a>
                </div>
            </div>
        </div>

        <p class="text-center mt-12 text-white/20 text-[10px] font-bold uppercase tracking-[0.2em]">
            &copy; 2026 KUILINGA TECHNOLOGIES
        </p>
    </div>
</body>

</html>
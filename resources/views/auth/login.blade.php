<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VoXY - Administration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-admin-gradient { 
            background: linear-gradient(135deg, #1e293b, #0f172a, #1e1b4b);
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
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
    </style>
</head>
<body class="bg-admin-gradient min-h-screen flex items-center justify-center p-4 overflow-hidden relative">
    <div class="max-w-md w-full relative z-10" x-data="{ loading: false, showPass: false }">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/10 rounded-[2.5rem] backdrop-blur-xl border border-white/20 shadow-2xl mb-6">
                <i class="fas fa-shield-halved text-3xl text-white"></i>
            </div>
            <h1 class="text-4xl font-black text-white tracking-tighter mb-2">VoXY <span class="font-extralight opacity-80">Portal</span></h1>
            <p class="text-white/40 font-medium tracking-widest text-[10px] uppercase">Espace Administrateur Général</p>
        </div>

        <div class="glass rounded-[3rem] p-10">
            <form action="{{ route('login') }}" method="POST" @submit="loading = true" class="space-y-6">
                @csrf
                
                @if($errors->any())
                    <div class="bg-red-500/20 border border-red-500/50 rounded-2xl p-4 text-white text-xs">
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
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 outline-none transition-all focus:border-white/40 focus:bg-white/10"
                               placeholder="admin@voxy.com" value="{{ old('email', 'admin@voxy.com') }}">
                    </div>

                    <div class="relative group">
                        <input :type="showPass ? 'text' : 'password'" name="password" required
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 text-white placeholder-white/20 outline-none transition-all focus:border-white/40 focus:bg-white/10"
                               placeholder="••••••••" value="admin123">
                        <button type="button" @click="showPass = !showPass" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/20 hover:text-white/60">
                            <i class="fas" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-white/10 text-white font-black py-5 rounded-[2rem] border border-white/20 hover:bg-white hover:text-slate-900 transition-all flex items-center justify-center gap-3"
                        :disabled="loading">
                    <span x-show="!loading">AUTHENTIFICATION</span>
                    <i x-show="loading" class="fas fa-circle-notch animate-spin"></i>
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-white/5 text-center">
                <a href="{{ route('login.maestro') }}" class="text-white/40 hover:text-white text-xs transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-user-tie"></i> Retour au portail Maestro
                </a>
            </div>
        </div>
    </div>
</body>
</html>
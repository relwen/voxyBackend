<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VoXY Maestro')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Scripts & Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'rgb(158, 2, 80)',
                            dark: 'rgb(78, 13, 4)',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    backgroundImage: {
                        'primary-gradient': 'linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80))',
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.2); }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }

        .premium-input {
            width: 100%;
            background-color: white !important;
            border: 2px solid #F1F5F9 !important; /* slate-100 */
            border-radius: 1.25rem !important;
            padding: 1rem 1.25rem !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            color: #1E293B !important; /* slate-800 */
            transition: all 0.2s ease-in-out !important;
            outline: none !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
        }

        .premium-input:focus {
            border-color: rgb(158, 2, 80) !important;
            box-shadow: 0 0 0 4px rgba(158, 2, 80, 0.08) !important;
            background-color: white !important;
        }

        .premium-label {
            display: block;
            font-size: 0.65rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94A3B8; /* slate-400 */
            margin-bottom: 0.5rem;
            margin-left: 0.25rem;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">
    <div>
        @include('components.maestro-sidebar')

        <div class="lg:pl-72 flex flex-col min-h-screen">
            <!-- Top Navigation -->
            <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-100 bg-white/80 backdrop-blur-md px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Ouvrir le menu</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest">@yield('page-title', 'Administration')</h2>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications/Search if needed -->
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true"></div>
                        
                        <!-- Profile dropdown wrapper -->
                        <div class="relative">
                            <div class="flex items-center gap-x-3">
                                <div class="hidden lg:block text-right">
                                    <p class="text-sm font-bold text-gray-900 leading-none">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-primary font-black uppercase mt-1 tracking-tighter">{{ Auth::user()->chorale->name ?? 'Maestro' }}</p>
                                </div>
                                <div class="h-10 w-10 rounded-xl bg-primary-gradient p-[2px] shadow-lg shadow-primary/20">
                                    <div class="h-full w-full rounded-[10px] bg-white flex items-center justify-center overflow-hidden">
                                        <span class="text-primary font-black text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="py-10 flex-1">
                <div class="px-4 sm:px-6 lg:px-8 animate-fade-in">
                    @yield('content')
                </div>
            </main>
            
            <footer class="bg-white border-t border-gray-100 py-6">
                <div class="px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-gray-400 font-medium">© {{ date('Y') }} VoXY. Tous droits réservés.</p>
                    <div class="flex items-center gap-6">
                        <a href="#" class="text-xs text-gray-400 hover:text-primary transition-colors">Support</a>
                        <a href="#" class="text-xs text-gray-400 hover:text-primary transition-colors">Confidentialité</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script>
        function viewPartition(id) {
            window.location.href = `/admin/partitions/${id}`;
        }

        function editPartition(id) {
            window.location.href = `/admin/partitions/${id}/edit`;
        }

        function deletePartition(id) {
            console.log('Tentative de suppression de la partition:', id);
            if (confirm('Êtes-vous sûr de vouloir supprimer cette partition ? Cette action est irréversible.')) {
                const url = `/admin/partitions/${id}/delete`;
                console.log('Envoi de la requête POST vers:', url);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Une erreur est survenue lors de la suppression.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Une erreur réseau est survenue.');
                });
            }
        }
    </script>
    @stack('scripts')
</body>
</html>

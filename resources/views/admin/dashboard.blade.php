@extends('layouts.admin')

@section('title', 'Dashboard - VoXY Admin')
@section('page-title', 'Tableau de bord')

@section('content')
<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Bienvenue, {{ Auth::user()->name }} 👋</h1>
    <p class="text-gray-500 mt-1">Voici ce qui se passe sur VoXY aujourd'hui.</p>
</div>

<!-- Statistics Grid -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-10">
    <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-blue-50 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative flex items-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-500 shadow-lg shadow-blue-200">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
            <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Utilisateurs</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['total_users'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center text-xs font-medium text-blue-600">
            <i class="fas fa-arrow-up mr-1"></i>
            <span>Inscriptions totales</span>
        </div>
    </div>

    <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-amber-50 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative flex items-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500 shadow-lg shadow-amber-200">
                <i class="fas fa-user-clock text-white text-xl"></i>
            </div>
            <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">En attente</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['pending_users'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center text-xs font-medium text-amber-600">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <span>Action requise</span>
        </div>
    </div>

    <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-emerald-50 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative flex items-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500 shadow-lg shadow-emerald-200">
                <i class="fas fa-music text-white text-xl"></i>
            </div>
            <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Chorales</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['total_chorales'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center text-xs font-medium text-emerald-600">
            <i class="fas fa-check-circle mr-1"></i>
            <span>Chorales actives</span>
        </div>
    </div>

    <div class="group relative overflow-hidden rounded-3xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-purple-50 opacity-50 group-hover:scale-150 transition-transform duration-500"></div>
        <div class="relative flex items-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-500 shadow-lg shadow-purple-200">
                <i class="fas fa-file-invoice text-white text-xl"></i>
            </div>
            <div class="ml-5">
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Partitions</p>
                <p class="text-3xl font-black text-gray-900">{{ $stats['total_partitions'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center text-xs font-medium text-purple-600">
            <i class="fas fa-plus-circle mr-1"></i>
            <span>Mises à jour</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Users Table -->
    <div class="lg:col-span-2 overflow-hidden rounded-3xl bg-white shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between px-8 py-6 border-b border-gray-50 bg-gray-50/30">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Utilisateurs récents</h3>
                <p class="text-sm text-gray-500">Les derniers membres ayant rejoint la plateforme.</p>
            </div>
            <a href="{{ route('admin.users') }}" class="mt-3 sm:mt-0 inline-flex items-center text-sm font-bold text-primary hover:text-primary-dark transition-colors">
                Voir tout <i class="fas fa-chevron-right ml-2 text-xs"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <tbody>
                    @foreach($recentUsers as $user)
                    <tr class="group hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                        <td class="px-8 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 shrink-0 rounded-xl bg-primary-gradient p-[1.5px]">
                                    <div class="h-full w-full rounded-[9px] bg-white flex items-center justify-center">
                                        <span class="text-primary font-bold text-xs">{{ substr($user->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->chorale)
                                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">
                                    <i class="fas fa-music mr-1.5 opacity-50"></i>
                                    {{ $user->chorale->name }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Sans chorale</span>
                            @endif
                        </td>
                        <td class="px-8 py-4 whitespace-nowrap text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                @if($user->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif($user->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700 @endif shadow-sm">
                                @if($user->status === 'approved') Approuvé
                                @elseif($user->status === 'pending') En attente
                                @else Rejeté @endif
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions / Sidebar in Dashboard -->
    <div class="space-y-6">
        <div class="rounded-3xl bg-primary-gradient p-8 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
                <i class="fas fa-microphone-alt text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h3 class="text-xl font-black">Besoin d'aide ?</h3>
                <p class="mt-2 text-white/80 text-sm leading-relaxed">Gérez les partitions, les chorales et les membres depuis une interface unique et intuitive.</p>
                <div class="mt-6 flex flex-col gap-3">
                    <a href="{{ route('admin.partitions') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-3 text-sm font-bold text-primary shadow-lg hover:bg-gray-50 transition-all">
                        <i class="fas fa-plus mr-2"></i> Ajouter une partition
                    </a>
                    <a href="{{ route('admin.users') }}" class="inline-flex items-center justify-center rounded-xl bg-white/20 px-4 py-3 text-sm font-bold text-white border border-white/30 backdrop-blur-sm hover:bg-white/30 transition-all">
                        Gérer les accès
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-900 border-b border-gray-50 pb-4 mb-4">Mises à jour système</h3>
            <div class="space-y-4">
                <div class="flex gap-4">
                    <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-500 shrink-0">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-1">Nouveau moteur audio v2.0</p>
                        <p class="text-xs text-gray-500 mt-1">Déploiement réussi aujourd'hui.</p>
                    </div>
                </div>
                <div class="flex gap-4 opacity-50">
                    <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-gray-100 text-gray-500 shrink-0">
                        <i class="fas fa-shield-alt text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-1">Sécurité renforcée</p>
                        <p class="text-xs text-gray-500 mt-1">Patch de sécurité appliqué hier.</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Envoi de Notification Push -->
        <div class="rounded-3xl bg-white p-6 shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-900 border-b border-gray-50 pb-4 mb-4">
                <i class="fas fa-paper-plane text-primary mr-2"></i> Envoyer une Notification
            </h3>
            <form action="{{ route('admin.send-notification') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" name="title" id="title" required placeholder="Ex: Nouvelle partition disponible !" 
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm">
                </div>
                <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="body" id="body" rows="3" required placeholder="Tapez votre message ici..." 
                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 text-sm"></textarea>
                </div>
                <button type="submit" class="w-full inline-flex justify-center items-center rounded-xl bg-primary px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-primary-dark transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i> Envoyer à tous
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
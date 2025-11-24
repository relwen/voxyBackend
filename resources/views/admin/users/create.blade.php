@extends('layouts.admin')

@section('title', 'Créer un utilisateur')
@section('page-title', 'Créer un utilisateur')

@section('content')
<div class="max-w-4xl mx-auto" x-data="userForm()">
    <!-- En-tête avec breadcrumb -->
    <div class="mb-8">
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('admin.users') }}" class="text-gray-500 hover:text-gray-700">Utilisateurs</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-gray-700 font-medium">Créer</span>
                    </div>
                </li>
            </ol>
        </nav>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-user-plus text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Créer un nouvel utilisateur</h1>
                    <p class="text-gray-600 mt-1">Ajoutez un nouveau compte utilisateur au système</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire principal -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form method="POST" action="{{ route('admin.users.store') }}" @submit="loading = true" class="p-6">
            @csrf

            <!-- Informations personnelles -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Informations personnelles
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom complet -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card text-gray-400 mr-1"></i>
                            Nom complet *
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('name') border-red-300 @enderror"
                               placeholder="Nom complet de l'utilisateur"
                               x-model="form.name">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                            Adresse email *
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('email') border-red-300 @enderror"
                               placeholder="email@exemple.com"
                               x-model="form.email">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sécurité -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-shield-alt text-green-500 mr-2"></i>
                    Sécurité
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mot de passe -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-gray-400 mr-1"></i>
                            Mot de passe *
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                   class="form-input w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('password') border-red-300 @enderror"
                                   placeholder="Mot de passe sécurisé"
                                   x-model="form.password"
                                   @input="checkPasswordStrength">
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <!-- Indicateur de force du mot de passe -->
                        <div class="mt-2" x-show="form.password.length > 0">
                            <div class="flex items-center space-x-2">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300" 
                                         :class="passwordStrengthColor" 
                                         :style="`width: ${passwordStrength}%`"></div>
                                </div>
                                <span class="text-xs font-medium" :class="passwordStrengthTextColor" x-text="passwordStrengthText"></span>
                            </div>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Confirmation mot de passe -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-check-double text-gray-400 mr-1"></i>
                            Confirmer le mot de passe *
                        </label>
                        <div class="relative">
                            <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                                   class="form-input w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Confirmez le mot de passe"
                                   x-model="form.passwordConfirm">
                            <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i :class="showPasswordConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        <!-- Indicateur de correspondance -->
                        <div class="mt-2" x-show="form.passwordConfirm.length > 0">
                            <div class="flex items-center space-x-2">
                                <i :class="passwordsMatch ? 'fas fa-check text-green-500' : 'fas fa-times text-red-500'"></i>
                                <span class="text-xs" :class="passwordsMatch ? 'text-green-600' : 'text-red-600'" 
                                      x-text="passwordsMatch ? 'Les mots de passe correspondent' : 'Les mots de passe ne correspondent pas'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rôles et permissions -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user-cog text-purple-500 mr-2"></i>
                    Rôles et permissions
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Rôle -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-crown text-gray-400 mr-1"></i>
                            Rôle *
                        </label>
                        <select name="role" id="role" required
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('role') border-red-300 @enderror"
                                x-model="form.role">
                            <option value="">Sélectionner un rôle</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>👤 Utilisateur</option>
                            <option value="moderator" {{ old('role') == 'moderator' ? 'selected' : '' }}>🛡️ Modérateur</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>👑 Administrateur</option>
                        </select>
                        @error('role')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Chorale -->
                    <div>
                        <label for="chorale_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-users-line text-gray-400 mr-1"></i>
                            Chorale
                        </label>
                        <select name="chorale_id" id="chorale_id"
                                class="form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('chorale_id') border-red-300 @enderror"
                                x-model="form.choraleId">
                            <option value="">🎵 Sélectionner une chorale (optionnel)</option>
                            @foreach($chorales as $chorale)
                                <option value="{{ $chorale->id }}" {{ old('chorale_id') == $chorale->id ? 'selected' : '' }}>
                                    🎼 {{ $chorale->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('chorale_id')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Statuts -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-toggle-on text-indigo-500 mr-2"></i>
                    Statuts du compte
                </h3>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Approuvé -->
                        <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200">
                            <input type="checkbox" name="is_approved" id="is_approved" value="1" 
                                   {{ old('is_approved') ? 'checked' : '' }}
                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   x-model="form.isApproved">
                            <div class="ml-3">
                                <label for="is_approved" class="text-sm font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Utilisateur approuvé
                                </label>
                                <p class="text-xs text-gray-500">L'utilisateur peut accéder à la plateforme</p>
                            </div>
                        </div>
                        
                        <!-- Actif -->
                        <div class="flex items-center p-3 bg-white rounded-lg border border-gray-200">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   x-model="form.isActive">
                            <div class="ml-3">
                                <label for="is_active" class="text-sm font-medium text-gray-900 flex items-center">
                                    <i class="fas fa-power-off text-blue-500 mr-2"></i>
                                    Compte actif
                                </label>
                                <p class="text-xs text-gray-500">Le compte est activé et fonctionnel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.users') }}" 
                   class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Annuler
                </a>
                <button type="submit" 
                        class="btn-primary inline-flex justify-center items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                        :disabled="loading || !formValid">
                    <span x-show="!loading" class="flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>
                        Créer l'utilisateur
                    </span>
                    <span x-show="loading" class="flex items-center">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Création en cours...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function userForm() {
    return {
        loading: false,
        showPassword: false,
        showPasswordConfirm: false,
        form: {
            name: '{{ old('name') }}',
            email: '{{ old('email') }}',
            password: '',
            passwordConfirm: '',
            role: '{{ old('role') }}',
            choraleId: '{{ old('chorale_id') }}',
            isApproved: {{ old('is_approved') ? 'true' : 'false' }},
            isActive: {{ old('is_active', true) ? 'true' : 'false' }}
        },
        passwordStrength: 0,
        passwordStrengthText: '',
        passwordStrengthColor: 'bg-gray-300',
        passwordStrengthTextColor: 'text-gray-500',
        
        get passwordsMatch() {
            return this.form.password === this.form.passwordConfirm && this.form.passwordConfirm.length > 0;
        },
        
        get formValid() {
            return this.form.name.length > 0 && 
                   this.form.email.length > 0 && 
                   this.form.password.length >= 6 && 
                   this.passwordsMatch && 
                   this.form.role.length > 0;
        },
        
        checkPasswordStrength() {
            const password = this.form.password;
            let strength = 0;
            let text = '';
            let color = 'bg-red-400';
            let textColor = 'text-red-600';
            
            if (password.length >= 8) strength += 25;
            if (/[a-z]/.test(password)) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            
            if (strength <= 25) {
                text = 'Faible';
                color = 'bg-red-400';
                textColor = 'text-red-600';
            } else if (strength <= 50) {
                text = 'Moyen';
                color = 'bg-yellow-400';
                textColor = 'text-yellow-600';
            } else if (strength <= 75) {
                text = 'Bon';
                color = 'bg-blue-400';
                textColor = 'text-blue-600';
            } else {
                text = 'Excellent';
                color = 'bg-green-400';
                textColor = 'text-green-600';
            }
            
            this.passwordStrength = strength;
            this.passwordStrengthText = text;
            this.passwordStrengthColor = color;
            this.passwordStrengthTextColor = textColor;
        }
    }
}
</script>
@endsection
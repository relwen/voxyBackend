<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Chorale - VoXY</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
        .text-primary { color: rgb(158, 2, 80); }
        .border-primary { border-color: rgb(158, 2, 80); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="flex justify-center mb-4">
                <div class="bg-primary-gradient rounded-full p-4">
                    <i class="fas fa-music text-white text-4xl"></i>
                </div>
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900">Créer votre Chorale</h2>
            <p class="mt-2 text-sm text-gray-600">
                Inscrivez-vous en tant que maestro et créez votre chorale en quelques minutes
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Déjà un compte ? <a href="{{ route('login.maestro') }}" class="text-primary hover:underline">Connectez-vous en tant que maestro</a> ou <a href="{{ route('login') }}" class="text-primary hover:underline">en tant qu'administrateur</a>
            </p>
        </div>

        <!-- Formulaire -->
        <div class="bg-white shadow-xl rounded-lg p-8">
            <form method="POST" action="{{ route('register.chorale.store') }}" class="space-y-6">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Informations personnelles -->
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user mr-2 text-primary"></i>Informations personnelles
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                            <input type="text" name="name" id="name" required
                                   value="{{ old('name') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" id="email" required
                                   value="{{ old('email') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                            <input type="tel" name="phone" id="phone" required
                                   value="{{ old('phone') }}"
                                   placeholder="+33 1 23 45 67 89"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('phone') border-red-300 @enderror">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                            <input type="password" name="password" id="password" required
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('password') border-red-300 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe *</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                </div>

                <!-- Informations de la chorale -->
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-users mr-2 text-primary"></i>Informations de la chorale
                    </h3>
                    <div class="space-y-6">
                        <div>
                            <label for="chorale_name" class="block text-sm font-medium text-gray-700 mb-1">Nom de la chorale *</label>
                            <input type="text" name="chorale_name" id="chorale_name" required
                                   value="{{ old('chorale_name') }}"
                                   placeholder="Ex: Chorale de Paris"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('chorale_name') border-red-300 @enderror">
                            @error('chorale_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="chorale_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="chorale_description" id="chorale_description" rows="3"
                                      placeholder="Décrivez votre chorale..."
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('chorale_description') border-red-300 @enderror">{{ old('chorale_description') }}</textarea>
                            @error('chorale_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="chorale_location" class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                            <input type="text" name="chorale_location" id="chorale_location"
                                   value="{{ old('chorale_location') }}"
                                   placeholder="Ex: Paris, France"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary @error('chorale_location') border-red-300 @enderror">
                            @error('chorale_location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informations importantes -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Ce qui se passe ensuite</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Votre compte sera créé et approuvé automatiquement</li>
                                    <li>Vous serez redirigé vers la page de configuration de votre chorale</li>
                                    <li>Vous pourrez configurer vos pupitres et rubriques</li>
                                    <li>Vous pourrez appliquer un template de base ou créer votre propre structure</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex items-center justify-between">
                    <div class="flex space-x-4">
                        <a href="{{ route('login.maestro') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            <i class="fas fa-arrow-left mr-1"></i>Connexion maestro
                        </a>
                        <span class="text-gray-400">|</span>
                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            Connexion admin
                        </a>
                    </div>
                    <button type="submit" class="bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                        <i class="fas fa-check mr-2"></i>Créer ma chorale
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-sm text-gray-500">
            <p>En créant une chorale, vous acceptez nos conditions d'utilisation</p>
        </div>
    </div>
</body>
</html>


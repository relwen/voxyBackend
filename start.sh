#!/bin/bash

echo "🚀 Démarrage du backend VoXY..."
echo "================================"

# Vérifier si les dépendances sont installées
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install
fi

# Vérifier si le fichier .env existe
if [ ! -f ".env" ]; then
    echo "⚙️  Copie du fichier de configuration..."
    cp .env.example .env
fi

# Générer la clé d'application
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# Créer la base de données SQLite si elle n'existe pas
if [ ! -f "database/database.sqlite" ]; then
    echo "🗄️  Création de la base de données SQLite..."
    touch database/database.sqlite
fi

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php artisan migrate --force

# Exécuter les seeders
echo "🌱 Exécution des seeders..."
php artisan db:seed --force

# Créer le lien symbolique pour le stockage
echo "🔗 Création du lien symbolique pour le stockage..."
php artisan storage:link

echo ""
echo "✅ Backend VoXY prêt!"
echo "📡 Serveur démarré sur: http://localhost:8000"
echo "🔗 API disponible sur: http://localhost:8000/api"
echo ""
echo "👤 Compte administrateur:"
echo "   Email: admin@voxy.com"
echo "   Mot de passe: admin123"
echo ""
echo "🛑 Pour arrêter le serveur: Ctrl+C"
echo ""

# Démarrer le serveur de développement
php artisan serve --host=0.0.0.0 --port=8000 
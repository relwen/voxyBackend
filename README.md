# 🎵 Backend VoXY - API Laravel

Backend Laravel pour l'application VoXY, une plateforme de gestion de chorales et partitions musicales.

## 🚀 Démarrage rapide

### Prérequis
- PHP 8.2+
- Composer
- SQLite (inclus)

### Installation et démarrage

1. **Cloner le projet** (si pas déjà fait)
```bash
cd voxbobackend
```

2. **Démarrer automatiquement**
```bash
./start.sh
```

Ou manuellement :
```bash
# Installer les dépendances
composer install

# Copier la configuration
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Créer la base de données
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate --force

# Exécuter les seeders
php artisan db:seed --force

# Créer le lien symbolique
php artisan storage:link

# Démarrer le serveur
php artisan serve --host=0.0.0.0 --port=8000
```

## 📡 API Endpoints

### 🔓 Routes publiques
- `POST /api/register` - Inscription utilisateur
- `POST /api/login` - Connexion utilisateur
- `GET /api/chorales` - Liste des chorales

### 🔐 Routes protégées (authentification requise)

#### Authentification
- `POST /api/logout` - Déconnexion
- `GET /api/me` - Informations utilisateur connecté

#### Administration (rôle admin requis)
- `GET /api/admin/pending-users` - Utilisateurs en attente
- `POST /api/admin/approve-user/{id}` - Approuver un utilisateur
- `POST /api/admin/reject-user/{id}` - Rejeter un utilisateur
- `GET /api/admin/users` - Tous les utilisateurs
- `GET /api/admin/stats` - Statistiques du tableau de bord
- `POST /api/admin/make-admin/{id}` - Promouvoir administrateur
- `POST /api/admin/remove-admin/{id}` - Retirer le statut admin

#### Chorales
- `GET /api/chorales` - Liste des chorales
- `POST /api/chorales` - Créer une chorale
- `GET /api/chorales/{id}` - Détails d'une chorale
- `PUT /api/chorales/{id}` - Modifier une chorale
- `DELETE /api/chorales/{id}` - Supprimer une chorale

#### Partitions
- `GET /api/partitions` - Liste des partitions
- `POST /api/partitions` - Créer une partition
- `GET /api/partitions/{id}` - Détails d'une partition
- `PUT /api/partitions/{id}` - Modifier une partition
- `DELETE /api/partitions/{id}` - Supprimer une partition
- `GET /api/partitions/{id}/download-pdf` - Télécharger le PDF
- `GET /api/partitions/sync` - Partitions pour synchronisation

#### Parties vocales
- `GET /api/voice-parts` - Liste des parties vocales
- `POST /api/voice-parts` - Créer une partie vocale
- `GET /api/voice-parts/{id}` - Détails d'une partie vocale
- `PUT /api/voice-parts/{id}` - Modifier une partie vocale
- `DELETE /api/voice-parts/{id}` - Supprimer une partie vocale
- `PUT /api/voice-parts/{id}/partition-voix` - Mettre à jour partition voix
- `PUT /api/voice-parts/{id}/partition-musique` - Mettre à jour partition musique
- `POST /api/voice-parts/{id}/upload-audio` - Uploader fichier audio

## 👤 Comptes de test

### Administrateur
- **Email:** admin@voxy.com
- **Mot de passe:** admin123

## 🌐 Interface Web

### Dashboard Administrateur
- **URL** : `http://localhost:8000/login`
- **Email** : `admin@voxy.com`
- **Mot de passe** : `admin123`

### Pages disponibles
- **Dashboard** : `http://localhost:8000/admin` - Vue d'ensemble avec statistiques
- **Utilisateurs** : `http://localhost:8000/admin/users` - Gestion des utilisateurs
- **Chorales** : `http://localhost:8000/admin/chorales` - Gestion des chorales
- **Partitions** : `http://localhost:8000/admin/partitions` - Gestion des partitions

### Fonctionnalités du dashboard
- ✅ Connexion sécurisée avec authentification
- ✅ Statistiques en temps réel
- ✅ Gestion des utilisateurs (approuver/rejeter)
- ✅ Promotion/rétrogradation des administrateurs
- ✅ Consultation des chorales et partitions
- ✅ Interface responsive avec Tailwind CSS

## 🗄️ Structure de la base de données

### Tables principales
- **users** - Utilisateurs avec rôles et statuts
- **chorales** - Chorales avec nom, description, localisation
- **partitions** - Partitions musicales avec fichiers PDF
- **voice_parts** - Parties vocales avec fichiers audio

### Champs utilisateur
- `name` - Nom complet
- `email` - Adresse email (unique)
- `password` - Mot de passe hashé
- `phone` - Numéro de téléphone
- `role` - Rôle (user/admin)
- `status` - Statut (pending/approved/rejected)
- `chorale_id` - ID de la chorale
- `voice_part` - Partie vocale (SOPRANE, TENOR, MEZOSOPRANE, ALTO, BASSE, BARITON)

## 🔧 Configuration

### Variables d'environnement importantes
```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite

FILESYSTEM_DISK=local
```

### CORS
Le backend est configuré pour accepter les requêtes cross-origin depuis n'importe quelle origine (`*`). Pour la production, restreignez les origines autorisées.

## 📁 Structure des fichiers

```
voxbobackend/
├── app/
│   ├── Http/Controllers/     # Contrôleurs API
│   ├── Http/Middleware/      # Middlewares
│   └── Models/              # Modèles Eloquent
├── database/
│   ├── migrations/          # Migrations de base de données
│   └── seeders/            # Seeders pour les données de test
├── routes/
│   └── api.php             # Routes API
├── storage/
│   └── app/public/         # Fichiers uploadés
└── start.sh                # Script de démarrage
```

## 🧪 Tests

Exécuter les tests de l'API :
```bash
php test_api.php
```

## 🔒 Sécurité

- Authentification via Laravel Sanctum
- Validation des données d'entrée
- Protection CSRF
- Middleware d'autorisation pour les routes admin
- Hachage des mots de passe avec Bcrypt

## 📞 Support

Pour toute question ou problème, consultez la documentation Laravel ou contactez l'équipe de développement.

# 🎵 Guide d'utilisation - Dashboard VoXY

## 🚀 Accès au dashboard

1. **Ouvrir votre navigateur**
2. **Aller à l'adresse** : `http://localhost:8000/login`
3. **Se connecter avec** :
   - Email : `admin@voxy.com`
   - Mot de passe : `admin123`

## 📊 Dashboard principal (`/admin`)

### Statistiques affichées
- **Total utilisateurs** : Nombre total d'utilisateurs inscrits
- **En attente** : Utilisateurs en attente d'approbation
- **Chorales** : Nombre total de chorales
- **Partitions** : Nombre total de partitions

### Utilisateurs récents
Liste des 5 derniers utilisateurs inscrits avec :
- Nom et email
- Chorale d'appartenance
- Statut (pending/approved/rejected)
- Rôle (user/admin)

## 👥 Gestion des utilisateurs (`/admin/users`)

### Actions disponibles

#### Pour les utilisateurs en attente :
- **Approuver** : Donne accès à l'application
- **Rejeter** : Refuse l'accès

#### Pour les utilisateurs approuvés :
- **Promouvoir Admin** : Donne les droits d'administrateur
- **Retirer Admin** : Retire les droits d'administrateur

### Informations affichées
- Nom, email et téléphone
- Chorale d'appartenance
- Partie vocale
- Statut et rôle
- Date d'inscription

## 🎼 Gestion des chorales (`/admin/chorales`)

### Informations affichées
- Nom de la chorale
- Description
- Localisation
- Nombre de membres
- Date de création

## 📜 Gestion des partitions (`/admin/partitions`)

### Informations affichées
- Titre de la partition
- Compositeur et arrangeur
- Chorale associée
- Disponibilité du fichier PDF
- Date de création

## 🔐 Sécurité

### Déconnexion
- Cliquer sur le bouton "Déconnexion" en haut à droite
- Ou aller directement à `/logout`

### Protection des routes
- Toutes les pages admin nécessitent une connexion
- Seuls les utilisateurs avec le rôle "admin" peuvent accéder
- Les sessions expirent automatiquement

## 🎨 Interface

### Design responsive
- Interface adaptée aux mobiles et tablettes
- Navigation claire et intuitive
- Messages de confirmation pour les actions
- Indicateurs visuels pour les statuts

### Couleurs des statuts
- **Vert** : Approuvé
- **Jaune** : En attente
- **Rouge** : Rejeté
- **Violet** : Administrateur

## 🚨 Dépannage

### Problème de connexion
1. Vérifier que le serveur fonctionne : `http://localhost:8000`
2. Vérifier les identifiants : admin@voxy.com / admin123
3. Vérifier que l'utilisateur admin existe dans la base

### Page blanche ou erreur
1. Vérifier les logs : `storage/logs/laravel.log`
2. Redémarrer le serveur : `php artisan serve`
3. Vider le cache : `php artisan cache:clear`

### Problème de base de données
1. Vérifier que la base existe : `database/database.sqlite`
2. Relancer les migrations : `php artisan migrate:fresh --seed`

## 📞 Support

Pour toute question ou problème :
1. Consulter les logs Laravel
2. Vérifier la documentation Laravel
3. Contacter l'équipe de développement 
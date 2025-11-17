#!/bin/bash

echo "=== Test du Dashboard Web VoXY ==="
echo ""

echo "1. Test de la page de connexion..."
curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost:8000/login

echo ""
echo "2. Test de la page d'accueil..."
curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost:8000/

echo ""
echo "3. Test de la page de test..."
curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost:8000/test

echo ""
echo "=== Informations de connexion ==="
echo "URL du dashboard: http://localhost:8000/login"
echo "Email: admin@voxy.com"
echo "Mot de passe: admin123"
echo ""
echo "=== Routes disponibles ==="
echo "- /login : Page de connexion"
echo "- /admin : Dashboard principal (après connexion)"
echo "- /admin/users : Gestion des utilisateurs"
echo "- /admin/chorales : Gestion des chorales"
echo "- /admin/partitions : Gestion des partitions"
echo ""
echo "=== API REST ==="
echo "- POST /api/auth/login : Connexion API"
echo "- GET /api/auth/user : Informations utilisateur"
echo "- POST /api/auth/logout : Déconnexion API"
echo "- GET /api/chorales : Liste des chorales"
echo "- GET /api/partitions : Liste des partitions"
echo "- GET /api/voice-parts : Liste des parties vocales" 
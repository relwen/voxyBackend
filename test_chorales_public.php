<?php

/**
 * Script de test pour vérifier l'accès public aux chorales
 * Teste si /api/chorales est accessible sans authentification
 */

echo "=== Test d'accès public aux chorales ===\n\n";

// Configuration - ajustez l'URL selon votre configuration
$baseUrl = 'http://localhost:8000/api'; // ou http://127.0.0.1:8000/api

// Fonction pour faire des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'error' => $error
    ];
}

// Test 1: Accès aux chorales SANS authentification
echo "1. Test d'accès public aux chorales (sans token)\n";
echo "URL: $baseUrl/chorales\n";

$response = makeRequest($baseUrl . '/chorales');

if ($response['error']) {
    echo "✗ ERREUR DE CONNEXION: {$response['error']}\n";
    echo "Vérifiez que le serveur Laravel est démarré avec: php artisan serve\n";
} else {
    echo "Code HTTP: {$response['code']}\n";
    
    if ($response['code'] == 200) {
        echo "✅ SUCCÈS: Endpoint /api/chorales accessible sans authentification\n";
        $chorales = $response['body']['data'] ?? [];
        echo "Nombre de chorales: " . count($chorales) . "\n";
        
        if (count($chorales) > 0) {
            echo "Première chorale: " . ($chorales[0]['name'] ?? 'N/A') . "\n";
        }
    } elseif ($response['code'] == 401) {
        echo "❌ ERREUR 401: Endpoint /api/chorales nécessite encore une authentification\n";
        echo "PROBLÈME: La configuration n'a pas été appliquée correctement\n";
    } else {
        echo "❌ ERREUR {$response['code']}: Problème avec l'endpoint\n";
    }
    
    echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== Résumé ===\n";
if ($response['code'] == 200) {
    echo "✅ CORRECTION RÉUSSIE: L'endpoint /api/chorales est maintenant public\n";
    echo "L'application mobile peut maintenant charger les chorales sans authentification\n";
} else {
    echo "❌ PROBLÈME: L'endpoint n'est pas encore accessible publiquement\n";
    echo "Vérifiez que:\n";
    echo "1. Le serveur Laravel est redémarré\n";
    echo "2. Les modifications dans routes/api.php sont correctes\n";
    echo "3. Aucun cache de routes n'est actif\n";
}

echo "\n=== Fin du test ===\n";

<?php

/**
 * Script de test pour l'API des vocalises
 * 
 * Ce script teste tous les endpoints de l'API vocalises
 * pour s'assurer qu'ils fonctionnent correctement.
 */

// Configuration
$baseUrl = 'http://localhost:8001/api';
$testToken = 'your_test_token_here'; // Remplacez par un token valide

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
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// Tests
echo "=== Test de l'API Vocalises ===\n\n";

// Test 1: Récupérer toutes les vocalises
echo "1. Test GET /vocalises\n";
$response = makeRequest($baseUrl . '/vocalises', 'GET', null, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Synchronisation
echo "2. Test GET /vocalises/sync\n";
$response = makeRequest($baseUrl . '/vocalises/sync', 'GET', null, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Synchronisation avec timestamp
echo "3. Test GET /vocalises/sync avec last_sync\n";
$response = makeRequest($baseUrl . '/vocalises/sync?last_sync=2024-01-01 00:00:00', 'GET', null, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Créer une vocalise (si vous avez les permissions)
echo "4. Test POST /vocalises (création)\n";
$vocaliseData = [
    'title' => 'Test Vocalise',
    'description' => 'Vocalise de test pour l\'API',
    'voice_part' => 'SOPRANE',
    'chorale_id' => 1
];
$response = makeRequest($baseUrl . '/vocalises', 'POST', $vocaliseData, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test 5: Récupérer une vocalise spécifique
echo "5. Test GET /vocalises/1\n";
$response = makeRequest($baseUrl . '/vocalises/1', 'GET', null, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n\n";

// Test 6: Télécharger un fichier audio (si disponible)
echo "6. Test GET /vocalises/1/download-audio\n";
$response = makeRequest($baseUrl . '/vocalises/1/download-audio', 'GET', null, $testToken);
echo "Code HTTP: " . $response['code'] . "\n";
if ($response['code'] == 200) {
    echo "Fichier audio téléchargé avec succès\n";
} else {
    echo "Réponse: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
}

echo "\n=== Fin des tests ===\n";

// Instructions
echo "\n=== Instructions ===\n";
echo "1. Assurez-vous que votre serveur Laravel est démarré\n";
echo "2. Remplacez \$testToken par un token d'authentification valide\n";
echo "3. Ajustez \$baseUrl si nécessaire\n";
echo "4. Exécutez ce script avec: php test_vocalise_api.php\n";
echo "5. Vérifiez que tous les tests retournent des codes HTTP 200 ou 201\n";

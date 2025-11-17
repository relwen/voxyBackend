<?php
/**
 * Script de diagnostic d'authentification - VoXY Box
 */

echo "🔍 DIAGNOSTIC AUTHENTIFICATION - VoXY Box\n";
echo "==========================================\n\n";

// Configuration
$baseURL = 'http://192.168.11.102:8000';
$loginURL = $baseURL . '/api/login';

echo "📡 Configuration:\n";
echo "   - URL Backend: $baseURL\n";
echo "   - URL Login: $loginURL\n\n";

// Test 1: Vérifier la connectivité
echo "1. 🚀 Test de connectivité...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginURL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ Erreur de connexion: $error\n";
    echo "\n🔧 SOLUTIONS:\n";
    echo "   1. Arrêtez votre backend actuel (Ctrl+C)\n";
    echo "   2. Redémarrez avec: php artisan serve --host=0.0.0.0 --port=8000\n";
    echo "   3. Vérifiez que vous êtes sur le même réseau WiFi que votre téléphone\n";
    exit(1);
}

echo "✅ Serveur accessible (Code HTTP: $httpCode)\n\n";

// Test 2: Test d'authentification
echo "2. 🔐 Test d'authentification...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginURL);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'admin@voxy.com',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Code HTTP: $httpCode\n";
echo "Réponse: $response\n";

if ($httpCode == 200) {
    $data = json_decode($response, true);
    if (isset($data['token'])) {
        echo "✅ SUCCÈS! Token reçu\n";
    } else {
        echo "❌ Pas de token dans la réponse\n";
    }
} else {
    echo "❌ Échec de l'authentification\n";
}
?>

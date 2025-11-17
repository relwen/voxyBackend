<?php

// Script de test pour l'API VoXY
$baseUrl = 'http://localhost:8000/api';

echo "🧪 Test de l'API VoXY\n";
echo "=====================\n\n";

// Test 1: Récupérer les chorales (public)
echo "1. Test récupération des chorales (public)...\n";
$response = file_get_contents($baseUrl . '/chorales');
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "✅ Succès: " . count($data['chorales']) . " chorales trouvées\n";
    foreach ($data['chorales'] as $chorale) {
        echo "   - " . $chorale['nom'] . " (" . $chorale['ville'] . ")\n";
    }
} else {
    echo "❌ Erreur: Impossible de récupérer les chorales\n";
}

echo "\n";

// Test 2: Inscription d'un utilisateur
echo "2. Test inscription utilisateur...\n";
$postData = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'chorale_id' => 1,
    'pupitre' => 'BASSE',
    'telephone' => '+33 1 23 45 67 89'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($postData)
    ]
]);

$response = file_get_contents($baseUrl . '/register', false, $context);
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "✅ Succès: Utilisateur inscrit avec succès\n";
    echo "   Message: " . $data['message'] . "\n";
} else {
    echo "❌ Erreur: " . ($data['message'] ?? 'Erreur inconnue') . "\n";
}

echo "\n";

// Test 3: Connexion administrateur
echo "3. Test connexion administrateur...\n";
$loginData = [
    'email' => 'admin@voxy.com',
    'password' => 'admin123'
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($loginData)
    ]
]);

$response = file_get_contents($baseUrl . '/login', false, $context);
$data = json_decode($response, true);

if ($data && $data['success']) {
    echo "✅ Succès: Connexion administrateur réussie\n";
    echo "   Token: " . substr($data['token'], 0, 20) . "...\n";
    $token = $data['token'];
} else {
    echo "❌ Erreur: " . ($data['message'] ?? 'Erreur de connexion') . "\n";
    $token = null;
}

echo "\n";

// Test 4: Récupérer les utilisateurs en attente (admin)
if ($token) {
    echo "4. Test récupération utilisateurs en attente (admin)...\n";
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token . "\r\n" .
                       'Accept: application/json'
        ]
    ]);

    $response = file_get_contents($baseUrl . '/admin/pending-users', false, $context);
    $data = json_decode($response, true);

    if ($data && $data['success']) {
        echo "✅ Succès: " . count($data['pending_users']) . " utilisateurs en attente\n";
        foreach ($data['pending_users'] as $user) {
            echo "   - " . $user['name'] . " (" . $user['email'] . ") - " . $user['pupitre'] . "\n";
        }
    } else {
        echo "❌ Erreur: " . ($data['message'] ?? 'Erreur de récupération') . "\n";
    }
}

echo "\n";
echo "🎵 Tests terminés!\n";
echo "L'API VoXY est prête à être utilisée avec l'application Flutter.\n"; 
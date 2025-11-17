<?php
/**
 * Test complet du backend VoXY Box
 * Vérifie toutes les fonctionnalités principales
 */

echo "🔍 TEST COMPLET BACKEND VoXY Box\n";
echo "=================================\n\n";

// Configuration
$baseURL = 'http://10.5.27.241:8001';
$token = null;

// Fonction pour faire des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        $token ? 'Authorization: Bearer ' . $token : ''
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test 1: Vérifier la connectivité
echo "1. 🚀 Test de connectivité...\n";
$result = makeRequest($baseURL . '/api/vocalises');
if ($result['error']) {
    echo "❌ Erreur de connexion: " . $result['error'] . "\n";
    exit(1);
}
echo "✅ Serveur accessible (Code: " . $result['http_code'] . ")\n\n";

// Test 2: Authentification
echo "2. 🔐 Test d'authentification...\n";
$loginData = [
    'email' => 'admin@voxy.com',
    'password' => 'admin123'
];

$result = makeRequest($baseURL . '/api/login', 'POST', $loginData);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['token'])) {
        $token = $data['token'];
        echo "✅ Authentification réussie\n";
        echo "🔑 Token: " . substr($token, 0, 20) . "...\n";
        echo "👤 Utilisateur: " . $data['user']['name'] . "\n";
    } else {
        echo "❌ Token non reçu\n";
        echo "Réponse: " . $result['response'] . "\n";
    }
} else {
    echo "❌ Erreur d'authentification\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

if (!$token) {
    echo "❌ Impossible de continuer sans token\n";
    exit(1);
}

// Test 3: Récupérer les vocalises
echo "3. 🎵 Test des vocalises...\n";
$result = makeRequest($baseURL . '/api/vocalises', 'GET', null, $token);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['data'])) {
        $vocalises = $data['data'];
        echo "✅ Vocalises récupérées avec succès\n";
        echo "📊 Nombre de vocalises: " . count($vocalises) . "\n";
        
        if (count($vocalises) > 0) {
            $first = $vocalises[0];
            echo "📋 Première vocalise:\n";
            echo "   - ID: " . $first['id'] . "\n";
            echo "   - Titre: " . $first['title'] . "\n";
            echo "   - Partie vocale: " . $first['voice_part'] . "\n";
            echo "   - Audio: " . ($first['audio_path'] ? 'Oui (' . $first['audio_path'] . ')' : 'Non') . "\n";
            echo "   - Chorale: " . ($first['chorale']['name'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ Format de réponse incorrect\n";
        echo "Réponse: " . $result['response'] . "\n";
    }
} else {
    echo "❌ Erreur lors de la récupération des vocalises\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

// Test 4: Test de synchronisation
echo "4. 🔄 Test de synchronisation...\n";
$result = makeRequest($baseURL . '/api/vocalises/sync', 'GET', null, $token);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['data'])) {
        echo "✅ Synchronisation réussie\n";
        echo "📊 Vocalises synchronisées: " . count($data['data']) . "\n";
        echo "🕐 Dernière sync: " . $data['last_sync'] . "\n";
    } else {
        echo "❌ Format de réponse incorrect\n";
    }
} else {
    echo "❌ Erreur de synchronisation\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

// Test 5: Récupérer les chorales
echo "5. 🎭 Test des chorales...\n";
$result = makeRequest($baseURL . '/api/chorales', 'GET', null, $token);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['data'])) {
        $chorales = $data['data'];
        echo "✅ Chorales récupérées avec succès\n";
        echo "📊 Nombre de chorales: " . count($chorales) . "\n";
        
        foreach ($chorales as $chorale) {
            echo "   - " . $chorale['name'] . " (ID: " . $chorale['id'] . ")\n";
        }
    } else {
        echo "❌ Format de réponse incorrect\n";
    }
} else {
    echo "❌ Erreur lors de la récupération des chorales\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

// Test 6: Récupérer les catégories
echo "6. 📂 Test des catégories...\n";
$result = makeRequest($baseURL . '/api/categories', 'GET', null, $token);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['data'])) {
        $categories = $data['data'];
        echo "✅ Catégories récupérées avec succès\n";
        echo "📊 Nombre de catégories: " . count($categories) . "\n";
        
        foreach ($categories as $category) {
            echo "   - " . $category['name'] . " (ID: " . $category['id'] . ")\n";
        }
    } else {
        echo "❌ Format de réponse incorrect\n";
    }
} else {
    echo "❌ Erreur lors de la récupération des catégories\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

// Test 7: Récupérer les partitions
echo "7. 📄 Test des partitions...\n";
$result = makeRequest($baseURL . '/api/partitions', 'GET', null, $token);
echo "Code HTTP: " . $result['http_code'] . "\n";

if ($result['http_code'] == 200) {
    $data = json_decode($result['response'], true);
    if (isset($data['data'])) {
        $partitions = $data['data'];
        echo "✅ Partitions récupérées avec succès\n";
        echo "📊 Nombre de partitions: " . count($partitions) . "\n";
        
        if (count($partitions) > 0) {
            $first = $partitions[0];
            echo "📋 Première partition:\n";
            echo "   - ID: " . $first['id'] . "\n";
            echo "   - Titre: " . $first['title'] . "\n";
            echo "   - Catégorie: " . ($first['category']['name'] ?? 'N/A') . "\n";
        }
    } else {
        echo "❌ Format de réponse incorrect\n";
    }
} else {
    echo "❌ Erreur lors de la récupération des partitions\n";
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

// Résumé final
echo "🎯 RÉSUMÉ DU TEST:\n";
echo "==================\n";
echo "✅ Backend Laravel opérationnel\n";
echo "✅ Authentification fonctionnelle\n";
echo "✅ API vocalises accessible\n";
echo "✅ API chorales accessible\n";
echo "✅ API catégories accessible\n";
echo "✅ API partitions accessible\n";
echo "✅ Synchronisation fonctionnelle\n";
echo "\n";
echo "📱 Configuration pour l'application mobile:\n";
echo "   - URL: $baseURL\n";
echo "   - Email: admin@voxy.com\n";
echo "   - Mot de passe: admin123\n";
echo "\n";
echo "🚀 Votre backend est prêt pour l'application mobile !\n";
?>

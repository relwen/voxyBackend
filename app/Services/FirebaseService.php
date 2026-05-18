<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class FirebaseService
{
    protected $serviceAccountPath;
    protected $projectId;

    public function __construct()
    {
        $this->serviceAccountPath = storage_path('app/firebase-auth.json');
        $this->projectId = env('FIREBASE_PROJECT_ID');
    }

    /**
     * Envoyer une notification à tous les utilisateurs ayant un token FCM
     */
    public function sendToAll($title, $body, $data = [])
    {
        $tokens = User::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return [
                'success' => false,
                'message' => 'Aucun utilisateur avec un token FCM trouvé'
            ];
        }

        return $this->sendMulticast($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification à un groupe de tokens
     */
    public function sendMulticast(array $tokens, $title, $body, $data = [])
    {
        $successCount = 0;
        $failureCount = 0;

        // Note: L'API v1 de FCM ne supporte pas nativement le multicast comme l'ancienne API.
        // On doit envoyer les messages individuellement ou utiliser les "Topics".
        // Pour simplifier, on envoie individuellement ici, mais pour beaucoup d'utilisateurs,
        // il vaudrait mieux utiliser un Topic "all_users".

        foreach ($tokens as $token) {
            $result = $this->sendNotification($token, $title, $body, $data);
            if ($result['success']) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        return [
            'success' => true,
            'success_count' => $successCount,
            'failure_count' => $failureCount
        ];
    }

    /**
     * Envoyer une notification individuelle (FCM HTTP v1)
     */
    public function sendNotification($token, $title, $body, $data = [])
    {
        // Si le fichier d'authentification n'existe pas, on simule l'envoi en loguant
        if (!file_exists($this->serviceAccountPath)) {
            Log::info("FCM Simulate: To: $token, Title: $title, Body: $body");
            return ['success' => true, 'message' => 'Simulated (missing firebase-auth.json)'];
        }

        try {
            $accessToken = $this->getAccessToken();
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => empty($data) ? new \stdClass() : array_map('strval', $data), // FCM expects a Map (object), not a list
                    'android' => [
                        'notification' => [
                            'channel_id' => 'high_importance_channel'
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return ['success' => true];
            } else {
                Log::error('FCM Error: ' . $response->body());
                return ['success' => false, 'error' => $response->body()];
            }
        } catch (\Exception $e) {
            Log::error('FCM Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Récupérer le token d'accès OAuth2 pour Google Cloud
     * Note: En production, il vaut mieux utiliser la bibliothèque Google Cloud SDK
     */
    protected function getAccessToken()
    {
        $json = json_decode(file_get_contents($this->serviceAccountPath), true);
        
        $now = time();
        $payload = [
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ];

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        
        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
        
        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $json['private_key'], 'SHA256');
        $base64UrlSignature = $this->base64UrlEncode($signature);
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        return $response->json()['access_token'];
    }

    protected function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}

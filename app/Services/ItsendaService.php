<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItsendaService
{
    private $bearerToken;
    private $baseUrl;

    public function __construct()
    {
        $this->bearerToken = config('services.itsenda.bearer_token');
        $this->baseUrl = config('services.itsenda.base_url');
    }

    /**
     * Envoyer un SMS via l'API Wasender
     *
     * @param string $to Numéro de téléphone du destinataire
     * @param string $message Message à envoyer
     * @return array
     */
    public function sendSMS(string $to, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/send-message', [
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                Log::info('SMS envoyé avec succès via Wasender', [
                    'to' => $to,
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message' => 'SMS envoyé avec succès',
                    'data' => $response->json()
                ];
            }

            Log::error('Erreur lors de l\'envoi du SMS via Wasender', [
                'to' => $to,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'envoi du SMS via Wasender', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer un code OTP
     *
     * @param string $phone Numéro de téléphone
     * @param string $otp Code OTP à envoyer
     * @return array
     */
    public function sendOTP(string $phone, string $otp): array
    {
        $message = "Votre code de vérification VoXY est: {$otp}. Ce code est valide pendant 5 minutes.";
        
        return $this->sendSMS($phone, $message);
    }
}


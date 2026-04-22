<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
                return [
                    'success' => true,
                    'message' => 'SMS envoyé avec succès',
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du SMS',
                'error' => $response->body()
            ];
        } catch (\Exception $e) {
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

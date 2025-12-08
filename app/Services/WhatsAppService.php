<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        // Configuration via variables d'environnement
        // Option 1: Utiliser Twilio WhatsApp API
        $this->apiUrl = config('services.whatsapp.api_url', env('WHATSAPP_API_URL'));
        $this->apiKey = config('services.whatsapp.api_key', env('WHATSAPP_API_KEY'));
        $this->senderId = config('services.whatsapp.sender_id', env('WHATSAPP_SENDER_ID'));
        
        // Option 2: Pour utiliser l'API WhatsApp Business directement
        // Option 3: Pour utiliser un service tiers comme Twilio
    }

    /**
     * Envoyer un message WhatsApp
     * 
     * @param string $phoneNumber Numéro de téléphone (format international, ex: +229XXXXXXXX)
     * @param string $message Message à envoyer
     * @return bool
     */
    public function sendMessage($phoneNumber, $message)
    {
        try {
            // Nettoyer le numéro de téléphone
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            
            if (!$phoneNumber) {
                Log::warning('Numéro de téléphone invalide pour l\'envoi WhatsApp');
                return false;
            }

            // Vérifier si l'envoi WhatsApp est activé
            if (!config('services.whatsapp.enabled', env('WHATSAPP_ENABLED', false))) {
                Log::info('Envoi WhatsApp désactivé. Message simulé pour: ' . $phoneNumber);
                return true; // Retourner true pour continuer le processus même si WhatsApp est désactivé
            }

            // Méthode 1: Twilio WhatsApp API
            if (config('services.whatsapp.provider') === 'twilio') {
                return $this->sendViaTwilio($phoneNumber, $message);
            }

            // Méthode 2: API WhatsApp Business (Graph API)
            if (config('services.whatsapp.provider') === 'meta') {
                return $this->sendViaMeta($phoneNumber, $message);
            }

            // Méthode 3: Service personnalisé via HTTP
            return $this->sendViaCustomAPI($phoneNumber, $message);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer via Twilio
     */
    protected function sendViaTwilio($phoneNumber, $message)
    {
        $accountSid = config('services.twilio.account_sid', env('TWILIO_ACCOUNT_SID'));
        $authToken = config('services.twilio.auth_token', env('TWILIO_AUTH_TOKEN'));
        $from = config('services.twilio.whatsapp_from', env('TWILIO_WHATSAPP_FROM'));

        if (!$accountSid || !$authToken || !$from) {
            Log::error('Configuration Twilio manquante');
            return false;
        }

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => "whatsapp:{$from}",
                'To' => "whatsapp:{$phoneNumber}",
                'Body' => $message,
            ]);

        if ($response->successful()) {
            Log::info('WhatsApp envoyé via Twilio à: ' . $phoneNumber);
            return true;
        }

        Log::error('Erreur Twilio: ' . $response->body());
        return false;
    }

    /**
     * Envoyer via Meta WhatsApp Business API
     */
    protected function sendViaMeta($phoneNumber, $message)
    {
        $accessToken = config('services.whatsapp.meta_access_token', env('WHATSAPP_META_ACCESS_TOKEN'));
        $phoneNumberId = config('services.whatsapp.meta_phone_number_id', env('WHATSAPP_META_PHONE_NUMBER_ID'));

        if (!$accessToken || !$phoneNumberId) {
            Log::error('Configuration Meta WhatsApp manquante');
            return false;
        }

        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

        if ($response->successful()) {
            Log::info('WhatsApp envoyé via Meta à: ' . $phoneNumber);
            return true;
        }

        Log::error('Erreur Meta WhatsApp: ' . $response->body());
        return false;
    }

    /**
     * Envoyer via API personnalisée
     */
    protected function sendViaCustomAPI($phoneNumber, $message)
    {
        if (!$this->apiUrl || !$this->apiKey) {
            Log::error('Configuration API WhatsApp personnalisée manquante');
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->apiUrl, [
            'to' => $phoneNumber,
            'message' => $message,
            'sender_id' => $this->senderId,
        ]);

        if ($response->successful()) {
            Log::info('WhatsApp envoyé via API personnalisée à: ' . $phoneNumber);
            return true;
        }

        Log::error('Erreur API personnalisée: ' . $response->body());
        return false;
    }

    /**
     * Formater le numéro de téléphone au format international
     */
    protected function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Supprimer tous les espaces, tirets, parenthèses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Si le numéro ne commence pas par +, ajouter +229 par défaut (Bénin)
        // Vous pouvez adapter selon votre pays
        if (!preg_match('/^\+/', $phone)) {
            // Si commence par 0, remplacer par +229
            if (substr($phone, 0, 1) === '0') {
                $phone = '+229' . substr($phone, 1);
            } elseif (substr($phone, 0, 3) === '229') {
                $phone = '+' . $phone;
            } else {
                // Par défaut, ajouter +229
                $phone = '+229' . $phone;
            }
        }

        // Vérifier que le numéro est valide (au moins 10 chiffres après le +)
        if (preg_match('/^\+\d{10,15}$/', $phone)) {
            return $phone;
        }

        return null;
    }

    /**
     * Envoyer une notification d'approbation de compte
     */
    public function sendApprovalNotification($user)
    {
        $sent = false;

        // Envoyer via WhatsApp si le numéro de téléphone existe
        if ($user->phone) {
            $message = "Bonjour {$user->name},\n\n";
            $message .= "Votre compte VoXY Box a été approuvé avec succès ! 🎉\n\n";
            $message .= "Vous pouvez maintenant vous connecter à l'application et profiter de toutes les fonctionnalités.\n\n";
            $message .= "Merci de votre confiance.\n\n";
            $message .= "L'équipe VoXY Box";

            $sent = $this->sendMessage($user->phone, $message);
        } else {
            Log::warning('L\'utilisateur ' . $user->id . ' n\'a pas de numéro de téléphone pour WhatsApp');
        }

        // Envoyer via Email si l'adresse email existe
        if ($user->email) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(
                    new \App\Mail\AccountApprovedMail($user)
                );
                Log::info('Email d\'approbation envoyé à: ' . $user->email);
                $sent = true;
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi de l\'email d\'approbation: ' . $e->getMessage());
            }
        } else {
            Log::warning('L\'utilisateur ' . $user->id . ' n\'a pas d\'adresse email');
        }

        return $sent;
    }
}


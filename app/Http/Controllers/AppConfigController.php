<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    /**
     * Récupérer la configuration de l'application (versions, liens, etc.)
     */
    public function getConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'versions' => [
                    'android' => [
                        'latest_version' => env('VERSION_ANDROID', '1.0.0'),
                        'minimum_version' => env('MIN_VERSION_ANDROID', '1.0.0'),
                        'force_update' => env('FORCE_UPDATE_ANDROID', false),
                        'download_url' => env('ANDROID_DOWNLOAD_URL', 'https://play.google.com/store/apps/details?id=com.kuilingatech.voxbox.voxbox'),
                    ],
                    'ios' => [
                        'latest_version' => env('VERSION_IOS', '1.0.0'),
                        'minimum_version' => env('MIN_VERSION_IOS', '1.0.0'),
                        'force_update' => env('FORCE_UPDATE_IOS', false),
                        'download_url' => env('IOS_DOWNLOAD_URL', 'https://apps.apple.com/app/voxbox/id123456789'),
                    ],
                ],
                'maintenance' => [
                    'is_active' => env('MAINTENANCE_MODE', false),
                    'message' => env('MAINTENANCE_MESSAGE', 'L\'application est en maintenance. Veuillez réessayer plus tard.'),
                ],
                'features' => [
                    'chat_enabled' => env('FEATURE_CHAT_ENABLED', true),
                    'notifications_enabled' => env('FEATURE_NOTIFICATIONS_ENABLED', true),
                ],
            ],
        ]);
    }

    /**
     * Vérifier si une mise à jour est requise pour une version donnée
     * Compare la version de l'app avec la version configurée sur le backend
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        $platform = $request->input('platform', 'android'); // android ou ios
        $currentVersion = $request->input('version', '1.0.0');

        // Récupérer les versions depuis la configuration backend
        $latestVersion = $platform === 'ios'
            ? env('VERSION_IOS', '1.0.0')
            : env('VERSION_ANDROID', '1.0.0');

        $minimumVersion = $platform === 'ios'
            ? env('MIN_VERSION_IOS', '1.0.0')
            : env('MIN_VERSION_ANDROID', '1.0.0');

        $forceUpdate = filter_var(
            $platform === 'ios'
                ? env('FORCE_UPDATE_IOS', false)
                : env('FORCE_UPDATE_ANDROID', false),
            FILTER_VALIDATE_BOOLEAN
        );

        $downloadUrl = $platform === 'ios'
            ? env('IOS_DOWNLOAD_URL', 'https://apps.apple.com/app/voxbox/id123456789')
            : env('ANDROID_DOWNLOAD_URL', 'https://play.google.com/store/apps/details?id=com.kuilingatech.voxbox.voxbox');

        // Comparer les versions : version_compare retourne -1 si version1 < version2
        // Si la version backend (latestVersion) est supérieure à la version de l'app (currentVersion)
        $isUpdateAvailable = version_compare($currentVersion, $latestVersion, '<');
        
        // Mise à jour requise si :
        // 1. La version actuelle est inférieure à la version minimum, OU
        // 2. Le force_update est activé, OU
        // 3. Une mise à jour est disponible et le force_update est activé
        $isUpdateRequired = version_compare($currentVersion, $minimumVersion, '<') || 
                           ($isUpdateAvailable && $forceUpdate);

        // Message personnalisé selon le cas
        $message = 'Votre application est à jour.';
        if ($isUpdateRequired) {
            $message = 'Une mise à jour est requise pour continuer à utiliser l\'application. Veuillez mettre à jour vers la version ' . $latestVersion . '.';
        } elseif ($isUpdateAvailable) {
            $message = 'Une nouvelle version (' . $latestVersion . ') est disponible. Nous vous recommandons de mettre à jour.';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'minimum_version' => $minimumVersion,
                'update_available' => $isUpdateAvailable,
                'update_required' => $isUpdateRequired,
                'force_update' => $forceUpdate,
                'download_url' => $downloadUrl,
                'platform' => $platform,
                'message' => $message,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Chorale;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Récupérer les utilisateurs en attente d'approbation
     */
    public function getPendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')
            ->with('chorale')
            ->get();

        return response()->json([
            'success' => true,
            'pending_users' => $pendingUsers
        ]);
    }

    /**
     * Approuver un utilisateur
     */
    public function approveUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'approved']);

        // Envoyer une notification WhatsApp
        try {
            $whatsappService = new WhatsAppService();
            $whatsappService->sendApprovalNotification($user);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de la notification WhatsApp: ' . $e->getMessage());
            // Ne pas bloquer l'approbation si l'envoi WhatsApp échoue
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur approuvé avec succès'
        ]);
    }

    /**
     * Rejeter un utilisateur
     */
    public function rejectUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur rejeté'
        ]);
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAllUsers()
    {
        $users = User::with('chorale')->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Récupérer les statistiques du tableau de bord
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'approved_users' => User::where('status', 'approved')->count(),
            'total_chorales' => Chorale::count(),
            'total_partitions' => \App\Models\Partition::count()
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Promouvoir un utilisateur au rang d'administrateur
     */
    public function makeAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => 'admin']);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur promu administrateur'
        ]);
    }

    /**
     * Retirer le statut d'administrateur
     */
    public function removeAdmin($id)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Statut d\'administrateur retiré'
        ]);
    }

    /**
     * Activer un utilisateur
     */
    public function activateUser($id)
    {
        $user = User::findOrFail($id);
        $wasActive = $user->is_active;
        $user->update(['is_active' => true]);

        // Envoyer une notification si l'utilisateur vient d'être activé et était approuvé
        if (!$wasActive && $user->status === 'approved') {
            try {
                $whatsappService = new WhatsAppService();
                $whatsappService->sendApprovalNotification($user);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi de la notification: ' . $e->getMessage());
                // Ne pas bloquer l'activation si l'envoi échoue
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur activé avec succès'
        ]);
    }

    /**
     * Désactiver un utilisateur
     */
    public function deactivateUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur désactivé'
        ]);
    }
}

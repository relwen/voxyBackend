<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Chorale;
use App\Services\ItsendaService;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     * Pour l'app mobile : email, name et voice_part sont optionnels
     * L'utilisateur devra compléter son profil après l'inscription
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8', // Optionnel pour l'app mobile (connexion par OTP)
            'chorale_id' => 'required|exists:chorales,id',
            'voice_part' => 'nullable|string|max:255', // Accepter n'importe quel nom de pupitre
            'phone' => 'required|string|max:20|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Laisser name et email vides (NULL) si non fournis - l'utilisateur devra compléter son profil
        $email = $request->email ?: null;
        $name = $request->name ?: null;

        // Générer un mot de passe aléatoire si non fourni (pour l'app mobile avec OTP)
        $password = $request->password;
        if (empty($password)) {
            $password = Hash::make(Str::random(16));
        } else {
            $password = Hash::make($password);
        }

        $user = User::create([
            'name' => $name, // NULL si non fourni
            'email' => $email, // NULL si non fourni
            'password' => $password,
            'chorale_id' => $request->chorale_id,
            'voice_part' => $request->voice_part ?? null, // NULL si non fourni
            'phone' => $request->phone,
            'role' => 'user',
            'status' => 'pending' // En attente par défaut
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie. Veuillez compléter votre profil.',
            'user' => $user,
            'profile_incomplete' => empty($request->name) || empty($request->voice_part) || empty($request->chorale_id)
        ], 201);
    }

    /**
     * Connexion utilisateur
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
        }

        $user = Auth::user();

        // Vérifier si le profil est complet (sans email - email n'est plus requis)
        $profileComplete = !empty($user->name) && !empty($user->voice_part) && !empty($user->chorale_id);

        if ($user->status !== 'approved') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Votre compte n\'est pas encore approuvé'
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user,
            'profile_complete' => $profileComplete,
            'profile_incomplete' => !$profileComplete
        ]);
    }

    /**
     * Déconnexion utilisateur
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('chorale');

        // Vérifier si le profil est complet (sans email - email n'est plus requis)
        $profileComplete = !empty($user->name) && !empty($user->voice_part) && !empty($user->chorale_id);

        return response()->json([
            'success' => true,
            'user' => $user,
            'profile_complete' => $profileComplete,
            'profile_incomplete' => !$profileComplete
        ]);
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     * Permet de compléter le profil après l'inscription
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $request->user()->id,
            'chorale_id' => 'nullable|exists:chorales,id',
            'voice_part' => 'nullable|string|max:255', // Accepter n'importe quel nom de pupitre
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        
        // Mettre à jour uniquement les champs fournis
        if ($request->has('name') && $request->name) {
            $user->name = $request->name;
        }
        
        if ($request->has('email') && $request->email) {
            $user->email = $request->email;
        }
        
        if ($request->has('chorale_id') && $request->chorale_id) {
            $user->chorale_id = $request->chorale_id;
        }
        
        if ($request->has('voice_part') && $request->voice_part) {
            $user->voice_part = $request->voice_part;
        }
        
        $user->save();
        $user->load('chorale');

        // Vérifier si le profil est complet (sans email - email n'est plus requis)
        $profileComplete = !empty($user->name) && !empty($user->voice_part) && !empty($user->chorale_id);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
            'profile_complete' => $profileComplete,
            'profile_incomplete' => !$profileComplete
        ]);
    }

    /**
     * Récupérer la liste des chorales (public)
     */
    public function getChorales()
    {
        $chorales = Chorale::all();

        return response()->json([
            'success' => true,
            'chorales' => $chorales
        ]);
    }

    /**
     * Vérifier si un numéro de téléphone existe en base de données
     */
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->input('phone');
        
        // Vérifier si le numéro existe
        $user = User::where('phone', $phone)->first();
        $exists = $user !== null;

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'message' => $exists ? 'Numéro trouvé' : 'Numéro non trouvé'
        ]);
    }

    /**
     * Demander un code OTP pour la connexion
     */
    public function requestOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->input('phone');

        // Vérifier si le numéro existe
        $user = User::where('phone', $phone)->first();

        // Si l'utilisateur n'existe pas, le créer automatiquement
        if (!$user) {
            $user = User::create([
                'phone' => $phone,
                'name' => null, // Vide par défaut - à compléter dans le profil
                'email' => null, // Vide par défaut - à compléter dans le profil
                'password' => bcrypt(Str::random(16)), // Mot de passe aléatoire (non utilisé avec OTP)
                'status' => 'pending', // En attente - doit compléter le profil
                'is_active' => true, // Actif par défaut
                'role' => 'user', // Rôle utilisateur par défaut
                'voice_part' => null, // Vide par défaut - à compléter dans le profil
            ]);
        }

        // Vérifier si le compte est actif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.'
            ], 403);
        }
        
        // Note: On permet la connexion même si le profil est incomplet ou en attente d'approbation
        // L'app mobile devra vérifier profile_incomplete et rediriger vers la complétion du profil

        // Générer un code OTP (6 chiffres)
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Stocker l'OTP dans le cache avec une expiration de 5 minutes
        $cacheKey = 'otp_' . $phone;
        Cache::put($cacheKey, [
            'otp' => $otp,
            'user_id' => $user->id,
            'attempts' => 0
        ], now()->addMinutes(5));

        // Envoyer le code OTP via Itsenda
        $itsendaService = new ItsendaService();
        $smsResult = $itsendaService->sendOTP($phone, $otp);

        // En mode développement, continuer même si le SMS échoue
        if (!$smsResult['success'] && !env('APP_DEBUG', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code OTP. Veuillez réessayer.',
                'error' => $smsResult['message']
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => $smsResult['success']
                ? 'Code OTP envoyé avec succès'
                : 'Code OTP généré (mode développement - SMS non envoyé)',
            'phone' => $phone,
            // En développement, on retourne toujours l'OTP pour faciliter les tests
            'otp' => env('APP_DEBUG', false) ? $otp : null
        ]);
    }

    /**
     * Vérifier le code OTP et connecter l'utilisateur
     */
    public function verifyOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $phone = $request->input('phone');
        $otp = $request->input('otp');
        
        // Récupérer l'OTP depuis le cache
        $cacheKey = 'otp_' . $phone;
        $otpData = Cache::get($cacheKey);

        if (!$otpData) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP expiré ou invalide. Veuillez demander un nouveau code.'
            ], 400);
        }

        // Vérifier le nombre de tentatives (max 5)
        if ($otpData['attempts'] >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Trop de tentatives échouées. Veuillez demander un nouveau code.'
            ], 429);
        }

        // Vérifier le code OTP
        if ($otpData['otp'] !== $otp) {
            // Incrémenter le compteur de tentatives
            $otpData['attempts']++;
            Cache::put($cacheKey, $otpData, now()->addMinutes(5));

            return response()->json([
                'success' => false,
                'message' => 'Code OTP incorrect',
                'attempts_remaining' => 5 - $otpData['attempts']
            ], 400);
        }

        // Récupérer l'utilisateur
        $user = User::find($otpData['user_id']);
        
        if (!$user) {
            Cache::forget($cacheKey);
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Supprimer l'OTP du cache après utilisation
        Cache::forget($cacheKey);

        // Vérifier si le profil est complet (sans email - email n'est plus requis)
        $profileComplete = !empty($user->name) && !empty($user->voice_part) && !empty($user->chorale_id);
        
        // Créer un token pour l'utilisateur
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'chorale_id' => $user->chorale_id,
                'voice_part' => $user->voice_part,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'profile_complete' => $profileComplete,
            'profile_incomplete' => !$profileComplete
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Chorale;

class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'chorale_id' => 'required|exists:chorales,id',
            'voice_part' => 'required|in:SOPRANE,TENOR,MEZOSOPRANE,ALTO,BASSE,BARITON',
            'phone' => 'required|string|max:20|unique:users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'chorale_id' => $request->chorale_id,
            'voice_part' => $request->voice_part,
            'phone' => $request->phone,
            'role' => 'user',
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie. Votre compte est en attente d\'approbation.',
            'user' => $user
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
            'user' => $user
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

        return response()->json([
            'success' => true,
            'user' => $user
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
     * Connexion par numéro de téléphone (sans mot de passe pour l'OTP)
     */
    public function loginByPhone(Request $request)
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
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone non trouvé'
            ], 404);
        }

        // Vérifier si le compte est approuvé
        if ($user->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est en attente d\'approbation'
            ], 403);
        }

        // Vérifier si le compte est actif
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.'
            ], 403);
        }

        // Créer un token pour l'utilisateur
        $token = $user->createToken('auth_token')->plainTextToken;

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
            ]
        ]);
    }
}

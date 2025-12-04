<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Chorale;
use App\Models\Category;
use App\Models\ChoralePupitre;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion (admin)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Afficher le formulaire de connexion pour les maestros
     */
    public function showMaestroLoginForm()
    {
        return view('auth.login-maestro');
    }

    /**
     * Afficher le formulaire d'inscription pour créer une chorale
     */
    public function showRegisterForm()
    {
        return view('auth.register-chorale');
    }

    /**
     * Traiter l'inscription d'un maestro avec création de chorale
     */
    public function registerChorale(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20|unique:users',
            'chorale_name' => 'required|string|max:255',
            'chorale_description' => 'nullable|string',
            'chorale_location' => 'nullable|string|max:255',
        ]);

        // Créer la chorale
        $chorale = Chorale::create([
            'name' => $request->chorale_name,
            'description' => $request->chorale_description,
            'location' => $request->chorale_location,
        ]);

        // Créer l'utilisateur maestro
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'maestro',
            'status' => 'approved', // Auto-approuvé pour les maestros
            'chorale_id' => $chorale->id,
            'is_active' => true,
        ]);

        // Créer les pupitres par défaut
        $defaultPupitres = [
            ['nom' => 'SOPRANE', 'order' => 1, 'is_default' => false],
            ['nom' => 'ALTO', 'order' => 2, 'is_default' => false],
            ['nom' => 'TENOR', 'order' => 3, 'is_default' => false],
            ['nom' => 'BASSES', 'order' => 4, 'is_default' => false],
            ['nom' => 'TUTTIES', 'order' => 0, 'is_default' => true],
        ];

        foreach ($defaultPupitres as $pupitre) {
            ChoralePupitre::create([
                'chorale_id' => $chorale->id,
                'nom' => $pupitre['nom'],
                'order' => $pupitre['order'],
                'is_default' => $pupitre['is_default'],
            ]);
        }

        // Créer la rubrique "Messes" par défaut (universelle)
        Category::create([
            'name' => 'Messes',
            'description' => 'Rubrique universelle pour les messes',
            'chorale_id' => $chorale->id,
            'structure_type' => 'with_sections', // Pour permettre les parties
            'color' => '#9E0250',
            'icon' => 'church',
        ]);

        // Créer la rubrique "Vocalises" avec structure dynamique (dossiers récursifs)
        Category::create([
            'name' => 'Vocalises',
            'description' => 'Rubrique pour les vocalises avec dossiers',
            'chorale_id' => $chorale->id,
            'structure_type' => 'with_dossiers', // Pour permettre les dossiers récursifs
            'color' => '#2196F3',
            'icon' => 'music_note',
        ]);

        // Connecter automatiquement l'utilisateur
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('admin.chorale.config')->with('success', 'Votre chorale a été créée avec succès ! La rubrique "Messes" et les pupitres par défaut ont été créés automatiquement.');
    }

    /**
     * Traiter la connexion (admin)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->withInput($request->only('email'));
        }

        $user = Auth::user();

        if ($user->status !== 'approved') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Votre compte n\'est pas encore approuvé.',
            ])->withInput($request->only('email'));
        }

        // Vérifier que c'est un admin
        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Accès refusé. Cette page est réservée aux administrateurs.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        return redirect()->intended('/admin');
    }

    /**
     * Traiter la connexion pour les maestros
     */
    public function loginMaestro(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Récupérer l'utilisateur manuellement pour déboguer
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            Log::info('Login maestro échoué: utilisateur non trouvé', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->withInput($request->only('email'));
        }

        Log::info('Login maestro: utilisateur trouvé', [
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'is_active' => $user->is_active,
            'chorale_id' => $user->chorale_id
        ]);

        // Vérifier le mot de passe manuellement
        if (!Hash::check($request->password, $user->password)) {
            Log::info('Login maestro échoué: mot de passe incorrect', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->withInput($request->only('email'));
        }

        // Vérifier le statut
        if ($user->status !== 'approved') {
            Log::info('Login maestro échoué: compte non approuvé', ['email' => $request->email, 'status' => $user->status]);
            return back()->withErrors([
                'email' => 'Votre compte n\'est pas encore approuvé.',
            ])->withInput($request->only('email'));
        }

        // Vérifier que c'est un maestro
        if ($user->role !== 'maestro') {
            Log::info('Login maestro échoué: rôle incorrect', ['email' => $request->email, 'role' => $user->role]);
            return back()->withErrors([
                'email' => 'Accès refusé. Cette page est réservée aux maestros de chorale.',
            ])->withInput($request->only('email'));
        }

        // Vérifier que le maestro a une chorale
        if (!$user->chorale) {
            Log::info('Login maestro échoué: aucune chorale associée', ['email' => $request->email]);
            return back()->withErrors([
                'email' => 'Aucune chorale associée à votre compte. Veuillez contacter l\'administrateur.',
            ])->withInput($request->only('email'));
        }

        // Connecter l'utilisateur
        Auth::login($user, $request->has('remember'));
        $request->session()->regenerate();
        
        Log::info('Login maestro réussi', ['email' => $user->email]);
        
        return redirect()->intended('/admin/chorale/config');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

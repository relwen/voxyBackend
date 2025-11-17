<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
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

        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Accès refusé. Droits d\'administrateur requis.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
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
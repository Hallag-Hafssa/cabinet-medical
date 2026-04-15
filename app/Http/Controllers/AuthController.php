<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return match (auth()->user()->role) {
                'admin' => redirect()->route('admin.dashboard'),
                'medecin' => redirect()->route('medecin.planning'),
                'secretaire' => redirect()->route('secretaire.patients.index'),
                default => redirect()->route('patient.rdv.index'),
            };
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'telephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:homme,femme',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'role' => 'patient',
        ]);

        // Créer la fiche patient automatiquement
        Patient::create([
            'user_id' => $user->id,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'sexe' => $validated['sexe'] ?? null,
        ]);

        Auth::login($user);

        return redirect()->route('patient.rdv.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function profil()
    {
        return view('profil.edit', ['user' => auth()->user()]);
    }

    public function updateProfil(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}

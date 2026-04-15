<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('user')->orderByDesc('created_at')->paginate(15);

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:homme,femme',
            'adresse' => 'nullable|string',
            'groupe_sanguin' => 'nullable|string',
            'allergies' => 'nullable|string',
            'antecedents' => 'nullable|string',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'telephone' => $validated['telephone'] ?? null,
            'role' => 'patient',
        ]);

        Patient::create([
            'user_id' => $user->id,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'sexe' => $validated['sexe'] ?? null,
            'adresse' => $validated['adresse'] ?? null,
            'groupe_sanguin' => $validated['groupe_sanguin'] ?? null,
            'allergies' => $validated['allergies'] ?? null,
            'antecedents' => $validated['antecedents'] ?? null,
        ]);

        return redirect()->route('secretaire.patients.index')
                         ->with('success', 'Patient créé avec succès.');
    }

    public function show(Patient $patient)
    {
        $patient->load(['user', 'consultations.medecin.user', 'rendezVous.medecin.user']);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient)
    {
        $patient->load('user');

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'sexe' => 'nullable|in:homme,femme',
            'adresse' => 'nullable|string',
            'groupe_sanguin' => 'nullable|string',
            'allergies' => 'nullable|string',
            'antecedents' => 'nullable|string',
        ]);

        $patient->user->update([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'],
        ]);

        $patient->update([
            'date_naissance' => $validated['date_naissance'],
            'sexe' => $validated['sexe'],
            'adresse' => $validated['adresse'],
            'groupe_sanguin' => $validated['groupe_sanguin'],
            'allergies' => $validated['allergies'],
            'antecedents' => $validated['antecedents'],
        ]);

        return back()->with('success', 'Patient mis à jour.');
    }

    public function destroy(Patient $patient)
    {
        $patient->user->delete(); // cascade supprime le patient

        return redirect()->route('secretaire.patients.index')
                         ->with('success', 'Patient supprimé.');
    }

    public function search(Request $request)
    {
        $q = $request->input('q');

        $patients = Patient::whereHas('user', function ($query) use ($q) {
            $query->where('nom', 'like', "%{$q}%")
                  ->orWhere('prenom', 'like', "%{$q}%")
                  ->orWhere('telephone', 'like', "%{$q}%");
        })->with('user')->paginate(15);

        return view('patients.index', compact('patients', 'q'));
    }

    /**
     * Patient : voir son propre historique médical
     */
    public function historique()
    {
        $patient = auth()->user()->patient;
        $consultations = $patient->getHistorique();

        return view('patients.historique', compact('consultations'));
    }
}

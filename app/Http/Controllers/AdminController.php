<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Medecin;
use App\Models\RendezVous;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'total_medecins' => Medecin::count(),
            'rdv_aujourdhui' => RendezVous::whereDate('date_heure', today())->count(),
            'consultations_mois' => Consultation::whereMonth('date_consultation', now()->month)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Stats RDV par mois (pour Chart.js - Line Chart)
     */
    public function statsRdvParMois()
    {
        $data = RendezVous::selectRaw('MONTH(date_heure) as mois, COUNT(*) as total')
            ->whereYear('date_heure', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        return response()->json($data);
    }

    /**
     * Patients par spécialité (pour Chart.js - Pie/Doughnut)
     */
    public function statsPatientsParSpecialite()
    {
        $data = DB::table('rendez_vous')
            ->join('medecins', 'rendez_vous.medecin_id', '=', 'medecins.id')
            ->join('specialites', 'medecins.specialite_id', '=', 'specialites.id')
            ->select('specialites.nom', DB::raw('COUNT(DISTINCT rendez_vous.patient_id) as total'))
            ->groupBy('specialites.nom')
            ->get();

        return response()->json($data);
    }

    /**
     * Taux d'annulation (pour Chart.js - Bar Chart)
     */
    public function statsTauxAnnulation()
    {
        $data = RendezVous::selectRaw("
                MONTH(date_heure) as mois,
                COUNT(*) as total,
                SUM(CASE WHEN statut = 'annule' THEN 1 ELSE 0 END) as annules
            ")
            ->whereYear('date_heure', now()->year)
            ->groupBy('mois')
            ->orderBy('mois')
            ->get()
            ->map(fn ($row) => [
                'mois' => $row->mois,
                'taux' => $row->total > 0 ? round(($row->annules / $row->total) * 100, 1) : 0,
            ]);

        return response()->json($data);
    }

    /**
     * RDV par jour de la semaine (pour Chart.js - Bar Chart)
     */
    public function statsRdvParJour()
    {
        $data = RendezVous::selectRaw('DAYOFWEEK(date_heure) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->orderBy('jour')
            ->get();

        return response()->json($data);
    }

    // ─── CRUD Utilisateurs ───────────────────────────────

    public function index()
    {
        $utilisateurs = User::orderByDesc('created_at')->paginate(20);

        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }

    public function create()
    {
        return view('admin.utilisateurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,medecin,secretaire,patient',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // Créer le profil associé au rôle
        match ($user->role) {
            'patient' => Patient::create(['user_id' => $user->id]),
            'medecin' => Medecin::create(['user_id' => $user->id, 'specialite_id' => $request->specialite_id ?? 1, 'matricule' => 'MED-' . $user->id]),
            'secretaire' => \App\Models\Secretaire::create(['user_id' => $user->id]),
            default => null,
        };

        return redirect()->route('admin.utilisateurs.index')
                         ->with('success', 'Utilisateur créé.');
    }

    public function edit(User $utilisateur)
    {
        return view('admin.utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, User $utilisateur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $utilisateur->id,
            'role' => 'required|in:admin,medecin,secretaire,patient',
        ]);

        $utilisateur->update($validated);

        return redirect()->route('admin.utilisateurs.index')
                         ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $utilisateur)
    {
        $utilisateur->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }
}

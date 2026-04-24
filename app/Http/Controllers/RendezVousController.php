<?php

namespace App\Http\Controllers;

use App\Models\Medecin;
use App\Models\RendezVous;
use App\Notifications\RendezVousConfirme;
use Illuminate\Http\Request;

class RendezVousController extends Controller
{
    /**
     * Patient : voir ses propres RDV
     */
    public function mesRendezVous()
    {
        $rdvs = auth()->user()->patient
            ->rendezVous()
            ->with(['medecin.user', 'medecin.specialite'])
            ->orderByDesc('date_heure')
            ->paginate(10);

        return view('rendez-vous.mes-rdv', compact('rdvs'));
    }

    /**
     * Patient : formulaire de prise de RDV
     */
    public function create()
    {
        $medecins = Medecin::with(['user', 'specialite', 'disponibilites'])->get();

        return view('rendez-vous.create', compact('medecins'));
    }

    /**
     * Patient : enregistrer un nouveau RDV
     */
    public function store(Request $request)
    {
        // Combiner date + heure si date_heure n'est pas fourni
        if (!$request->date_heure && $request->date && $request->heure) {
            $request->merge(['date_heure' => $request->date . ' ' . $request->heure . ':00']);
        }

        $validated = $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'date_heure' => 'required|date|after:now',
            'motif' => 'nullable|string|max:500',
        ]);

        $medecin = Medecin::findOrFail($validated['medecin_id']);

        // Vérifier la disponibilité
        if (!$medecin->isDisponible(new \DateTime($validated['date_heure']))) {
            return back()->withErrors(['date_heure' => 'Ce créneau n\'est pas disponible.'])->withInput();
        }

        $rdv = RendezVous::create([
            'patient_id' => auth()->user()->patient->id,
            'medecin_id' => $validated['medecin_id'],
            'date_heure' => $validated['date_heure'],
            'motif' => $validated['motif'],
            'statut' => 'en_attente',
        ]);

        // Notification email de confirmation
        auth()->user()->notify(new RendezVousConfirme($rdv));

        return redirect()->route('patient.rdv.index')
                         ->with('success', 'Rendez-vous pris avec succès !');
    }

    /**
     * Médecin : planning du jour
     */
    public function planning()
    {
        $medecin = auth()->user()->medecin;

        $rdvsAujourdhui = $medecin->rendezVous()
            ->aujourdhui()
            ->with(['patient.user'])
            ->orderBy('date_heure')
            ->get();

        $rdvsAVenir = $medecin->rendezVous()
            ->aVenir()
            ->with(['patient.user'])
            ->orderBy('date_heure')
            ->take(20)
            ->get();

        return view('rendez-vous.planning', compact('rdvsAujourdhui', 'rdvsAVenir'));
    }

    /**
     * Médecin : patients du jour
     */
    public function mesPatientsAujourdhui()
    {
        $rdvs = auth()->user()->medecin
            ->rendezVous()
            ->aujourdhui()
            ->with(['patient.user', 'consultation'])
            ->orderBy('date_heure')
            ->get();

        return view('rendez-vous.aujourdhui', compact('rdvs'));
    }

    /**
     * Confirmer un RDV
     */
    public function confirmer(RendezVous $rendezVous)
    {
        $rendezVous->confirmer();

        return back()->with('success', 'Rendez-vous confirmé.');
    }

    /**
     * Annuler un RDV
     */
    public function annuler(RendezVous $rendezVous)
    {
        $rendezVous->annuler();

        return back()->with('success', 'Rendez-vous annulé.');
    }

    /**
     * Secrétaire : CRUD complet
     */
    public function index()
    {
        $rdvs = RendezVous::with(['patient.user', 'medecin.user'])
            ->orderByDesc('date_heure')
            ->paginate(15);

        return view('rendez-vous.index', compact('rdvs'));
    }

    public function edit(RendezVous $rendezVous)
    {
        $medecins = Medecin::with(['user', 'specialite'])->get();

        return view('rendez-vous.edit', compact('rendezVous', 'medecins'));
    }

    public function update(Request $request, RendezVous $rendezVous)
    {
        $validated = $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'date_heure' => 'required|date|after:now',
            'motif' => 'nullable|string|max:500',
            'statut' => 'required|in:en_attente,confirme,annule,termine',
        ]);

        $rendezVous->update($validated);

        return redirect()->route('secretaire.rendez-vous.index')
                         ->with('success', 'Rendez-vous modifié.');
    }

    public function destroy(RendezVous $rendezVous)
    {
        $rendezVous->delete();

        return back()->with('success', 'Rendez-vous supprimé.');
    }
}

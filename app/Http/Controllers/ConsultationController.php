<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\RendezVous;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function create(RendezVous $rendezVous)
    {
        $patient = $rendezVous->patient->load(['user', 'consultations.medecin.user']);

        return view('consultations.create', compact('rendezVous', 'patient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rendez_vous_id' => 'required|exists:rendez_vous,id',
            'motif' => 'nullable|string',
            'diagnostic' => 'required|string',
            'notes' => 'nullable|string',
            'compte_rendu' => 'required|string',
        ]);

        $rdv = RendezVous::findOrFail($validated['rendez_vous_id']);
        $medecin = auth()->user()->medecin;

        $consultation = Consultation::create([
            'rendez_vous_id' => $rdv->id,
            'medecin_id' => $medecin->id,
            'patient_id' => $rdv->patient_id,
            'motif' => $validated['motif'],
            'diagnostic' => $validated['diagnostic'],
            'notes' => $validated['notes'],
            'compte_rendu' => $validated['compte_rendu'],
            'date_consultation' => now(),
        ]);

        // Passer le RDV à "terminé"
        $rdv->terminer();

        return redirect()->route('medecin.consultation.show', $consultation)
                         ->with('success', 'Consultation enregistrée.');
    }

    public function show(Consultation $consultation)
    {
        $consultation->load(['patient.user', 'medecin.user', 'ordonnance.medicaments', 'rendezVous']);

        return view('consultations.show', compact('consultation'));
    }
}

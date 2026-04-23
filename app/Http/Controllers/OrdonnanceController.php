<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Medicament;
use App\Models\Ordonnance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrdonnanceController extends Controller
{
    public function create(Consultation $consultation)
    {
        $medicaments = Medicament::orderBy('nom')->get();

        return view('ordonnances.create', compact('consultation', 'medicaments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'consultation_id' => 'required|exists:consultations,id',
            'instructions' => 'nullable|string',
            'medicaments' => 'required|array|min:1',
            'medicaments.*.id' => 'required|exists:medicaments,id',
            'medicaments.*.posologie' => 'required|string',
            'medicaments.*.duree' => 'required|string',
            'medicaments.*.remarques' => 'nullable|string',
        ]);

        $consultation = Consultation::findOrFail($validated['consultation_id']);

        $ordonnance = Ordonnance::create([
            'consultation_id' => $consultation->id,
            'medecin_id' => auth()->user()->medecin->id,
            'patient_id' => $consultation->patient_id,
            'date_ordonnance' => now()->toDateString(),
            'instructions' => $validated['instructions'],
        ]);

        // Attacher les médicaments avec pivot
        foreach ($validated['medicaments'] as $med) {
            $ordonnance->medicaments()->attach($med['id'], [
                'posologie' => $med['posologie'],
                'duree' => $med['duree'],
                'remarques' => $med['remarques'] ?? null,
            ]);
        }

        return redirect()->route('medecin.consultation.show', $consultation)
                         ->with('success', 'Ordonnance créée avec succès.');
    }

    /**
     * Export PDF via DomPDF
     */
    public function exportPDF(Ordonnance $ordonnance)
    {
        $ordonnance->load(['medecin.user', 'medecin.specialite', 'patient.user', 'medicaments']);

        $pdf = Pdf::loadView('ordonnances.pdf', compact('ordonnance'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('ordonnance_' . $ordonnance->id . '.pdf');
    }
}

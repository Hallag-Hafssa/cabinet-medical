<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Barryvdh\DomPDF\Facade\Pdf;

class Ordonnance extends Model
{
    use HasFactory;

    protected $fillable = [
        'consultation_id', 'medecin_id', 'patient_id',
        'date_ordonnance', 'instructions',
    ];

    protected $casts = [
        'date_ordonnance' => 'date',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicaments()
    {
        return $this->belongsToMany(Medicament::class, 'ordonnance_medicament')
                    ->withPivot('posologie', 'duree', 'remarques')
                    ->withTimestamps();
    }

    /**
     * Génère le PDF de l'ordonnance via DomPDF
     */
    public function exporterPDF()
    {
        $pdf = Pdf::loadView('ordonnances.pdf', [
            'ordonnance' => $this->load(['medecin.user', 'medecin.specialite', 'patient.user', 'medicaments']),
        ]);

        return $pdf->download('ordonnance_' . $this->id . '.pdf');
    }
}

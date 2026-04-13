<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rendez_vous_id', 'medecin_id', 'patient_id',
        'motif', 'diagnostic', 'notes', 'compte_rendu', 'date_consultation',
    ];

    protected $casts = [
        'date_consultation' => 'datetime',
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function ordonnance()
    {
        return $this->hasOne(Ordonnance::class);
    }
}

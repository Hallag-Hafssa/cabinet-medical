<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date_naissance', 'sexe', 'adresse',
        'groupe_sanguin', 'allergies', 'antecedents',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    // ─── Relations ───────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function ordonnances()
    {
        return $this->hasMany(Ordonnance::class);
    }

    // ─── Helpers ─────────────────────────────────────────

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? Carbon::parse($this->date_naissance)->age : null;
    }

    public function getHistorique()
    {
        return $this->consultations()->with(['medecin.user', 'ordonnance'])->orderByDesc('date_consultation')->get();
    }
}

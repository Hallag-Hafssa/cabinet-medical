<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'patient_id', 'medecin_id', 'date_heure',
        'duree_minutes', 'statut', 'motif',
    ];

    protected $casts = [
        'date_heure' => 'datetime',
    ];

    // ─── Relations ───────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Actions ─────────────────────────────────────────

    public function confirmer(): void
    {
        $this->update(['statut' => 'confirme']);
    }

    public function annuler(): void
    {
        $this->update(['statut' => 'annule']);
    }

    public function terminer(): void
    {
        $this->update(['statut' => 'termine']);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeAVenir($query)
    {
        return $query->where('date_heure', '>=', now())
                     ->whereIn('statut', ['en_attente', 'confirme']);
    }

    public function scopeParMedecin($query, int $medecinId)
    {
        return $query->where('medecin_id', $medecinId);
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date_heure', today());
    }
}

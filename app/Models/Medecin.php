<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'specialite_id', 'matricule'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialite()
    {
        return $this->belongsTo(Specialite::class);
    }

    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function getNomCompletAttribute(): string
    {
        return 'Dr. ' . $this->user->prenom . ' ' . $this->user->nom;
    }

    /**
     * Vérifie si le médecin est disponible à une date/heure donnée
     */
    public function isDisponible(\DateTime $dateHeure, int $duree = 30): bool
    {
        $jour = strtolower(\Carbon\Carbon::parse($dateHeure)->translatedFormat('l'));

        // Vérifier si le médecin travaille ce jour-là
        $dispo = $this->disponibilites()
            ->where('jour_semaine', $jour)
            ->where('heure_debut', '<=', $dateHeure->format('H:i'))
            ->where('heure_fin', '>=', $dateHeure->format('H:i'))
            ->exists();

        if (!$dispo) return false;

        // Vérifier qu'il n'y a pas de conflit avec un autre RDV
        $fin = \Carbon\Carbon::parse($dateHeure)->addMinutes($duree);

        return !$this->rendezVous()
            ->whereIn('statut', ['en_attente', 'confirme'])
            ->where(function ($q) use ($dateHeure, $fin) {
                $q->whereBetween('date_heure', [$dateHeure, $fin])
                  ->orWhere(function ($q2) use ($dateHeure, $fin) {
                      $q2->where('date_heure', '<', $dateHeure)
                         ->whereRaw('DATE_ADD(date_heure, INTERVAL duree_minutes MINUTE) > ?', [$dateHeure]);
                  });
            })
            ->exists();
    }
}

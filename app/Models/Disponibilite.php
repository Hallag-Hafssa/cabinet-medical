<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    use HasFactory;

    protected $fillable = ['medecin_id', 'jour_semaine', 'heure_debut', 'heure_fin'];

    public function medecin()
    {
        return $this->belongsTo(Medecin::class);
    }
}

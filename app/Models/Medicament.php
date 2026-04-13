<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicament extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'dosage', 'forme'];

    public function ordonnances()
    {
        return $this->belongsToMany(Ordonnance::class, 'ordonnance_medicament')
                    ->withPivot('posologie', 'duree', 'remarques')
                    ->withTimestamps();
    }
}

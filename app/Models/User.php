<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'password', 'role', 'telephone',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ─── Relations ───────────────────────────────────────

    public function patient()
    {
        return $this->hasOne(Patient::class);
    }

    public function medecin()
    {
        return $this->hasOne(Medecin::class);
    }

    public function secretaire()
    {
        return $this->hasOne(Secretaire::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Helpers ─────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    public function isSecretaire(): bool
    {
        return $this->role === 'secretaire';
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function getNomCompletAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}

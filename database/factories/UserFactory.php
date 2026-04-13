<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'patient',
            'telephone' => fake()->phoneNumber(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * État : admin
     */
    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }

    /**
     * État : médecin
     */
    public function medecin(): static
    {
        return $this->state(fn () => ['role' => 'medecin']);
    }

    /**
     * État : secrétaire
     */
    public function secretaire(): static
    {
        return $this->state(fn () => ['role' => 'secretaire']);
    }
}

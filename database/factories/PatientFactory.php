<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'patient']),
            'date_naissance' => fake()->date('Y-m-d', '-20 years'),
            'sexe' => fake()->randomElement(['homme', 'femme']),
            'adresse' => fake()->address(),
            'groupe_sanguin' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'allergies' => null,
            'antecedents' => null,
        ];
    }
}

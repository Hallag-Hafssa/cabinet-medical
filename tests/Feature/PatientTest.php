<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    private User $secretaire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->secretaire = User::factory()->create(['role' => 'secretaire']);
    }

    // ─── CRUD PATIENT ────────────────────────────────────

    public function test_secretaire_peut_voir_liste_patients(): void
    {
        $response = $this->actingAs($this->secretaire)->get('/secretaire/patients');
        $response->assertStatus(200);
    }

    public function test_secretaire_peut_creer_patient(): void
    {
        $response = $this->actingAs($this->secretaire)->post('/secretaire/patients', [
            'nom' => 'Idrissi',
            'prenom' => 'Karim',
            'email' => 'karim@test.com',
            'telephone' => '0612345678',
            'date_naissance' => '1990-05-20',
            'sexe' => 'homme',
            'adresse' => 'Casablanca',
            'groupe_sanguin' => 'A+',
            'allergies' => 'Pénicilline',
            'antecedents' => 'Diabète type 2',
        ]);

        $response->assertRedirect(route('secretaire.patients.index'));

        // Vérifie la création de l'utilisateur
        $this->assertDatabaseHas('users', [
            'nom' => 'Idrissi',
            'prenom' => 'Karim',
            'email' => 'karim@test.com',
            'role' => 'patient',
        ]);

        // Vérifie la création de la fiche patient
        $user = User::where('email', 'karim@test.com')->first();
        $this->assertDatabaseHas('patients', [
            'user_id' => $user->id,
            'sexe' => 'homme',
            'groupe_sanguin' => 'A+',
            'allergies' => 'Pénicilline',
        ]);
    }

    public function test_secretaire_peut_modifier_patient(): void
    {
        $user = User::factory()->create(['role' => 'patient', 'nom' => 'Ancien']);
        $patient = Patient::create(['user_id' => $user->id, 'sexe' => 'homme']);

        $response = $this->actingAs($this->secretaire)->put("/secretaire/patients/{$patient->id}", [
            'nom' => 'Nouveau',
            'prenom' => $user->prenom,
            'telephone' => '0699999999',
            'sexe' => 'homme',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Nouveau', $user->fresh()->nom);
    }

    public function test_secretaire_peut_supprimer_patient(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create(['user_id' => $user->id]);

        $response = $this->actingAs($this->secretaire)->delete("/secretaire/patients/{$patient->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
    }

    // ─── RECHERCHE ───────────────────────────────────────

    public function test_recherche_patient_par_nom(): void
    {
        $user = User::factory()->create(['role' => 'patient', 'nom' => 'Bennani', 'prenom' => 'Amina']);
        Patient::create(['user_id' => $user->id]);

        $response = $this->actingAs($this->secretaire)->get('/secretaire/patients/search?q=Bennani');
        $response->assertStatus(200);
        $response->assertSee('Bennani');
    }

    public function test_recherche_patient_aucun_resultat(): void
    {
        $response = $this->actingAs($this->secretaire)->get('/secretaire/patients/search?q=InexistantXYZ');
        $response->assertStatus(200);
        $response->assertSee('Aucun patient');
    }

    // ─── DOSSIER PATIENT ─────────────────────────────────

    public function test_secretaire_peut_voir_dossier_patient(): void
    {
        $user = User::factory()->create(['role' => 'patient', 'nom' => 'TestDossier']);
        $patient = Patient::create([
            'user_id' => $user->id,
            'allergies' => 'Aspirine',
            'groupe_sanguin' => 'O+',
        ]);

        $response = $this->actingAs($this->secretaire)->get("/secretaire/patients/{$patient->id}");
        $response->assertStatus(200);
        $response->assertSee('TestDossier');
        $response->assertSee('Aspirine');
        $response->assertSee('O+');
    }

    // ─── ACCÈS INTERDIT ──────────────────────────────────

    public function test_patient_ne_peut_pas_voir_liste_patients(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $response = $this->actingAs($patient)->get('/secretaire/patients');
        $response->assertStatus(403);
    }

    // ─── ATTRIBUT ÂGE ────────────────────────────────────

    public function test_age_patient_calcule_correctement(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create([
            'user_id' => $user->id,
            'date_naissance' => now()->subYears(30)->format('Y-m-d'),
        ]);

        $this->assertEquals(30, $patient->age);
    }

    public function test_age_null_si_pas_de_date_naissance(): void
    {
        $user = User::factory()->create(['role' => 'patient']);
        $patient = Patient::create([
            'user_id' => $user->id,
            'date_naissance' => null,
        ]);

        $this->assertNull($patient->age);
    }
}

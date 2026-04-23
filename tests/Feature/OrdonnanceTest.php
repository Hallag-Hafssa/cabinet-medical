<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\RendezVous;
use App\Models\Consultation;
use App\Models\Ordonnance;
use App\Models\Medicament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdonnanceTest extends TestCase
{
    use RefreshDatabase;

    private User $medecinUser;
    private Medecin $medecin;
    private Patient $patient;
    private Consultation $consultation;

    protected function setUp(): void
    {
        parent::setUp();

        $specialite = Specialite::create(['nom' => 'Cardiologie']);

        $this->medecinUser = User::factory()->create(['role' => 'medecin']);
        $this->medecin = Medecin::create([
            'user_id' => $this->medecinUser->id,
            'specialite_id' => $specialite->id,
            'matricule' => 'MED-002',
        ]);

        $patientUser = User::factory()->create(['role' => 'patient']);
        $this->patient = Patient::create([
            'user_id' => $patientUser->id,
            'date_naissance' => '1985-06-15',
        ]);

        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now(),
            'statut' => 'termine',
        ]);

        $this->consultation = Consultation::create([
            'rendez_vous_id' => $rdv->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'diagnostic' => 'Hypertension légère',
            'compte_rendu' => 'Tension artérielle élevée',
            'date_consultation' => now(),
        ]);

        // Créer des médicaments de test
        Medicament::create(['id' => 1, 'nom' => 'Paracétamol', 'dosage' => '500mg', 'forme' => 'comprimé']);
        Medicament::create(['id' => 2, 'nom' => 'Amoxicilline', 'dosage' => '1g', 'forme' => 'comprimé']);
        Medicament::create(['id' => 3, 'nom' => 'Oméprazole', 'dosage' => '20mg', 'forme' => 'gélule']);
    }

    // ─── CRÉATION ORDONNANCE ─────────────────────────────

    public function test_medecin_peut_voir_formulaire_ordonnance(): void
    {
        $response = $this->actingAs($this->medecinUser)
            ->get("/medecin/ordonnance/create/{$this->consultation->id}");

        $response->assertStatus(200);
        $response->assertSee('Paracétamol');
        $response->assertSee('Amoxicilline');
    }

    public function test_medecin_peut_creer_ordonnance(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/ordonnance', [
            'consultation_id' => $this->consultation->id,
            'instructions' => 'Prendre les médicaments après les repas',
            'medicaments' => [
                ['id' => 1, 'posologie' => '1 cp matin et soir', 'duree' => '7 jours', 'remarques' => ''],
                ['id' => 2, 'posologie' => '1 cp 3x/jour', 'duree' => '5 jours', 'remarques' => 'Avec un verre d\'eau'],
            ],
        ]);

        $response->assertSessionHas('success');

        // Vérifie la création de l'ordonnance
        $this->assertDatabaseHas('ordonnances', [
            'consultation_id' => $this->consultation->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'instructions' => 'Prendre les médicaments après les repas',
        ]);

        // Vérifie les médicaments dans la table pivot
        $ordonnance = Ordonnance::first();
        $this->assertCount(2, $ordonnance->medicaments);

        $paracetamol = $ordonnance->medicaments->where('nom', 'Paracétamol')->first();
        $this->assertEquals('1 cp matin et soir', $paracetamol->pivot->posologie);
        $this->assertEquals('7 jours', $paracetamol->pivot->duree);
    }

    public function test_ordonnance_echoue_sans_medicament(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/ordonnance', [
            'consultation_id' => $this->consultation->id,
            'instructions' => 'Test',
            'medicaments' => [],
        ]);

        $response->assertSessionHasErrors('medicaments');
    }

    public function test_ordonnance_echoue_medicament_inexistant(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/ordonnance', [
            'consultation_id' => $this->consultation->id,
            'medicaments' => [
                ['id' => 999, 'posologie' => 'test', 'duree' => 'test'],
            ],
        ]);

        $response->assertSessionHasErrors('medicaments.0.id');
    }

    public function test_ordonnance_echoue_sans_posologie(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/ordonnance', [
            'consultation_id' => $this->consultation->id,
            'medicaments' => [
                ['id' => 1, 'posologie' => '', 'duree' => '7 jours'],
            ],
        ]);

        $response->assertSessionHasErrors('medicaments.0.posologie');
    }

    // ─── EXPORT PDF ──────────────────────────────────────

    public function test_export_pdf_ordonnance(): void
    {
        $ordonnance = Ordonnance::create([
            'consultation_id' => $this->consultation->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'date_ordonnance' => now()->toDateString(),
            'instructions' => 'Repos',
        ]);

        $ordonnance->medicaments()->attach(1, [
            'posologie' => '1 cp/jour',
            'duree' => '5 jours',
        ]);

        $response = $this->actingAs($this->medecinUser)
            ->get("/medecin/ordonnance/{$ordonnance->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    // ─── RELATIONS ───────────────────────────────────────

    public function test_ordonnance_liee_a_consultation(): void
    {
        $ordonnance = Ordonnance::create([
            'consultation_id' => $this->consultation->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'date_ordonnance' => now()->toDateString(),
        ]);

        $this->assertEquals($this->consultation->id, $ordonnance->consultation->id);
        $this->assertNotNull($this->consultation->fresh()->ordonnance);
    }

    public function test_ordonnance_avec_plusieurs_medicaments(): void
    {
        $ordonnance = Ordonnance::create([
            'consultation_id' => $this->consultation->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'date_ordonnance' => now()->toDateString(),
        ]);

        $ordonnance->medicaments()->attach(1, ['posologie' => '1 cp/jour', 'duree' => '7 jours']);
        $ordonnance->medicaments()->attach(2, ['posologie' => '2 cp/jour', 'duree' => '5 jours']);
        $ordonnance->medicaments()->attach(3, ['posologie' => '1 gélule/jour', 'duree' => '10 jours']);

        $this->assertCount(3, $ordonnance->fresh()->medicaments);
    }

    // ─── ACCÈS ───────────────────────────────────────────

    public function test_patient_ne_peut_pas_creer_ordonnance(): void
    {
        $patientUser = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patientUser)->post('/medecin/ordonnance', [
            'consultation_id' => $this->consultation->id,
            'medicaments' => [
                ['id' => 1, 'posologie' => 'test', 'duree' => 'test'],
            ],
        ]);

        $response->assertStatus(403);
    }
}

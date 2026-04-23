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

class ConsultationTest extends TestCase
{
    use RefreshDatabase;

    private User $medecinUser;
    private Medecin $medecin;
    private Patient $patient;
    private RendezVous $rdv;

    protected function setUp(): void
    {
        parent::setUp();

        $specialite = Specialite::create(['nom' => 'Médecine générale']);

        $this->medecinUser = User::factory()->create(['role' => 'medecin']);
        $this->medecin = Medecin::create([
            'user_id' => $this->medecinUser->id,
            'specialite_id' => $specialite->id,
            'matricule' => 'MED-001',
        ]);

        $patientUser = User::factory()->create(['role' => 'patient']);
        $this->patient = Patient::create(['user_id' => $patientUser->id]);

        $this->rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'date_heure' => now(),
            'duree_minutes' => 30,
            'statut' => 'confirme',
        ]);
    }

    // ─── CONSULTATION ────────────────────────────────────

    public function test_medecin_peut_voir_formulaire_consultation(): void
    {
        $response = $this->actingAs($this->medecinUser)
            ->get("/medecin/consultation/create/{$this->rdv->id}");

        $response->assertStatus(200);
    }

    public function test_medecin_peut_creer_consultation(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/consultation', [
            'rendez_vous_id' => $this->rdv->id,
            'motif' => 'Douleurs abdominales',
            'diagnostic' => 'Gastrite légère',
            'compte_rendu' => 'Examen clinique normal. Prescription de protecteur gastrique.',
            'notes' => 'Patient stressé, à surveiller.',
        ]);

        $response->assertSessionHas('success');

        // Vérifie la création en BDD
        $this->assertDatabaseHas('consultations', [
            'rendez_vous_id' => $this->rdv->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'diagnostic' => 'Gastrite légère',
        ]);

        // Vérifie que le RDV passe à "terminé"
        $this->assertEquals('termine', $this->rdv->fresh()->statut);
    }

    public function test_consultation_echoue_sans_diagnostic(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/consultation', [
            'rendez_vous_id' => $this->rdv->id,
            'motif' => 'Test',
            'diagnostic' => '',
            'compte_rendu' => 'Test',
        ]);

        $response->assertSessionHasErrors('diagnostic');
    }

    public function test_consultation_echoue_sans_compte_rendu(): void
    {
        $response = $this->actingAs($this->medecinUser)->post('/medecin/consultation', [
            'rendez_vous_id' => $this->rdv->id,
            'diagnostic' => 'Test diag',
            'compte_rendu' => '',
        ]);

        $response->assertSessionHasErrors('compte_rendu');
    }

    public function test_medecin_peut_voir_detail_consultation(): void
    {
        $consultation = Consultation::create([
            'rendez_vous_id' => $this->rdv->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'diagnostic' => 'Grippe',
            'compte_rendu' => 'Repos recommandé',
            'date_consultation' => now(),
        ]);

        $response = $this->actingAs($this->medecinUser)
            ->get("/medecin/consultation/{$consultation->id}");

        $response->assertStatus(200);
        $response->assertSee('Grippe');
    }

    // ─── RELATIONS ───────────────────────────────────────

    public function test_consultation_liee_au_rdv(): void
    {
        $consultation = Consultation::create([
            'rendez_vous_id' => $this->rdv->id,
            'medecin_id' => $this->medecin->id,
            'patient_id' => $this->patient->id,
            'diagnostic' => 'Test',
            'compte_rendu' => 'Test',
            'date_consultation' => now(),
        ]);

        $this->assertEquals($this->rdv->id, $consultation->rendezVous->id);
        $this->assertEquals($this->medecin->id, $consultation->medecin->id);
        $this->assertEquals($this->patient->id, $consultation->patient->id);
    }

    public function test_historique_patient(): void
    {
        // 3 consultations pour le même patient
        for ($i = 1; $i <= 3; $i++) {
            $rdv = RendezVous::create([
                'patient_id' => $this->patient->id,
                'medecin_id' => $this->medecin->id,
                'date_heure' => now()->subDays($i),
                'statut' => 'termine',
            ]);
            Consultation::create([
                'rendez_vous_id' => $rdv->id,
                'medecin_id' => $this->medecin->id,
                'patient_id' => $this->patient->id,
                'diagnostic' => "Diagnostic $i",
                'compte_rendu' => "Compte-rendu $i",
                'date_consultation' => now()->subDays($i),
            ]);
        }

        $historique = $this->patient->getHistorique();
        $this->assertCount(3, $historique);
        // Vérifie l'ordre décroissant
        $this->assertEquals('Diagnostic 1', $historique->first()->diagnostic);
    }
}

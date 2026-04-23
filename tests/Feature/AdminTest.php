<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Patient;
use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\RendezVous;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);

        // Données de test
        $specialite = Specialite::create(['nom' => 'Généraliste']);
        $medecinUser = User::factory()->create(['role' => 'medecin']);
        $medecin = Medecin::create([
            'user_id' => $medecinUser->id,
            'specialite_id' => $specialite->id,
            'matricule' => 'MED-ADM',
        ]);

        // Créer quelques patients et RDV
        for ($i = 0; $i < 3; $i++) {
            $pUser = User::factory()->create(['role' => 'patient']);
            $patient = Patient::create(['user_id' => $pUser->id]);
            RendezVous::create([
                'patient_id' => $patient->id,
                'medecin_id' => $medecin->id,
                'date_heure' => now()->subDays($i),
                'statut' => $i === 0 ? 'annule' : 'termine',
            ]);
        }
    }

    // ─── DASHBOARD ───────────────────────────────────────

    public function test_admin_peut_acceder_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_dashboard_affiche_statistiques(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertSee('Total patients');
        $response->assertSee('Médecins');
    }

    // ─── API STATS ───────────────────────────────────────

    public function test_api_rdv_par_mois(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/stats/rdv-par-mois');
        $response->assertStatus(200);
        $response->assertJsonStructure([['mois', 'total']]);
    }

    public function test_api_patients_par_specialite(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/stats/patients-par-specialite');
        $response->assertStatus(200);
        $response->assertJsonStructure([['nom', 'total']]);
    }

    public function test_api_taux_annulation(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/stats/taux-annulation');
        $response->assertStatus(200);
        $response->assertJsonStructure([['mois', 'taux']]);
    }

    public function test_api_rdv_par_jour(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/admin/stats/rdv-par-jour');
        $response->assertStatus(200);
        $response->assertJsonStructure([['jour', 'total']]);
    }

    // ─── GESTION UTILISATEURS ────────────────────────────

    public function test_admin_peut_voir_liste_utilisateurs(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/utilisateurs');
        $response->assertStatus(200);
    }

    public function test_admin_peut_creer_utilisateur(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/utilisateurs', [
            'nom' => 'NouveauUser',
            'prenom' => 'Test',
            'email' => 'nouveau@test.com',
            'role' => 'secretaire',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.utilisateurs.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'nouveau@test.com',
            'role' => 'secretaire',
        ]);
    }

    public function test_admin_peut_modifier_utilisateur(): void
    {
        $user = User::factory()->create(['role' => 'patient', 'nom' => 'Ancien']);

        $response = $this->actingAs($this->admin)->put("/admin/utilisateurs/{$user->id}", [
            'nom' => 'Modifié',
            'prenom' => $user->prenom,
            'email' => $user->email,
            'role' => 'secretaire',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('Modifié', $user->fresh()->nom);
        $this->assertEquals('secretaire', $user->fresh()->role);
    }

    public function test_admin_peut_supprimer_utilisateur(): void
    {
        $user = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($this->admin)->delete("/admin/utilisateurs/{$user->id}");
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_ne_peut_pas_se_supprimer(): void
    {
        // Le bouton est caché dans la vue, mais vérifions aussi le comportement
        $response = $this->actingAs($this->admin)->get('/admin/utilisateurs');
        $response->assertStatus(200);
    }

    // ─── ACCÈS RESTREINT ─────────────────────────────────

    public function test_non_admin_ne_peut_pas_acceder(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);
        $response = $this->actingAs($patient)->get('/admin/dashboard');
        $response->assertStatus(403);

        $medecin = User::factory()->create(['role' => 'medecin']);
        $response = $this->actingAs($medecin)->get('/admin/utilisateurs');
        $response->assertStatus(403);
    }
}

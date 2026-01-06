<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Treatment;
use App\Models\Medicament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicamentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_medicament(){
        $patient = User::factory()->create(['role' => 'patient']);
        $treatment = Treatment::factory()->create(['patient_id' => $patient->id]);

        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/medicament', [
            'name' => 'Paracetamol',
            'dosage' => '500mg',
            'interval_hours' => 8,
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'comment' => 'Take it after every meal',
            'treatment_id' => $treatment->id,
        ]);

        $response->assertCreated()
                 ->assertJson(['message' => 'Medicament created successfully']);

        $this->assertDatabaseHas('medicament', [
            'name' => 'Paracetamol',
            'treatment_id' => $treatment->id,
        ]);
    }

    public function test_create_duplicate_medicament(){
        $treatment = Treatment::factory()->create();
        Medicament::factory()->create([
            'name' => 'Ibuprofeno',
            'treatment_id' => $treatment->id,
        ]);

        $patient = $treatment->patient;
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/medicament', [
            'name' => 'Ibuprofeno',
            'dosage' => '400mg',
            'interval_hours' => 6,
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'treatment_id' => $treatment->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Medicament already in the treatment']);
    }

    public function test_get_medicament_list(){
        $treatment = Treatment::factory()->create();
        Medicament::factory()->count(3)->create(['treatment_id' => $treatment->id]);

        Sanctum::actingAs($treatment->patient);

        $response = $this->getJson("/api/medicament/{$treatment->id}");

        $response->assertOk()
                 ->assertJsonCount(3);
    }

    public function test_cannot_get_medicament_list(){
        $treatment = Treatment::factory()->create();
        Sanctum::actingAs($treatment->patient);

        $response = $this->getJson("/api/medicament/{$treatment->id}");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No medicaments found']);
    }

    public function test_update_medicament(){
        $medicament = Medicament::factory()->create();
        Sanctum::actingAs($medicament->treatment->patient);

        $response = $this->putJson("/api/medicament/{$medicament->id}", [
            'name' => 'Amoxicilin',
            'dosage' => '750mg',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Medicament updated successfully']);

        $this->assertDatabaseHas('medicament', [
            'id' => $medicament->id,
            'name' => 'Amoxicilin',
        ]);
    }

    public function test_delete_medicament(){
        $medicament = Medicament::factory()->create();
        Sanctum::actingAs($medicament->treatment->patient);

        $response = $this->deleteJson("/api/medicament/{$medicament->id}");

        $response->assertOk()
                 ->assertJson(['message' => 'Medicament deleted successfully']);

        $this->assertDatabaseMissing('medicament', ['id' => $medicament->id]);
    }

    public function test_delete_medicament_not_found(){
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/medicament/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Medicament not found']);
    }
}

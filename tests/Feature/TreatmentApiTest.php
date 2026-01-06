<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Treatment;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class TreatmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_treatment(){
        $patient = User::factory()->create(['role' => 'patient']);
        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/treatment', [
            'patient_id' => $patient->id,
            'name' => 'Test Treatment',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'comment' => 'Initial comment',
        ]);

        $response->assertCreated()
                 ->assertJson(['message' => 'Treatment successfully created']);

        $this->assertDatabaseHas('treatment', [
            'patient_id' => $patient->id,
            'name' => 'Test Treatment',
        ]);
    }

    public function test_get_treatments_by_patient(){
        $patient = User::factory()->create(['role' => 'patient']);
        Treatment::factory()->count(3)->create(['patient_id' => $patient->id]);

        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/treatment/byPatient');

        $response->assertOk()
                 ->assertJsonCount(3);
    }

    public function test_get_treatments_by_date(){
        $patient = User::factory()->create(['role' => 'patient']);
        $date = now();
        Treatment::factory()->create([
            'patient_id' => $patient->id,
            'start_date' => $date->copy()->startOfMonth(),
            'end_date' => $date->copy()->endOfMonth(),
        ]);

        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/treatment/byDate?month=' . $date->format('m') . '&year=' . $date->format('Y'));

        $response->assertOk()
                 ->assertJsonCount(1);
    }

    public function test_update_treatment(){
        $patient = User::factory()->create(['role' => 'patient']);
        $treatment = Treatment::factory()->create(['patient_id' => $patient->id]);

        Sanctum::actingAs($patient);

        $response = $this->putJson('/api/treatment/' . $treatment->id, [
            'comment' => 'Updated comment',
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Treatment updated successfully']);

        $this->assertDatabaseHas('treatment', [
            'id' => $treatment->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_delete_treatment(){
        $patient = User::factory()->create(['role' => 'patient']);
        $treatment = Treatment::factory()->create(['patient_id' => $patient->id]);

        Sanctum::actingAs($patient);

        $response = $this->deleteJson('/api/treatment/' . $treatment->id);

        $response->assertOk()
                 ->assertJson(['message' => 'Treatment deleted successfully']);

        $this->assertDatabaseMissing('treatment', ['id' => $treatment->id]);
    }

    public function test_delete_treatment_not_found(){
        $patient = User::factory()->create(['role' => 'patient']);
        Sanctum::actingAs($patient);

        $response = $this->deleteJson('/api/treatment/999');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Treatment not found']);
    }
}

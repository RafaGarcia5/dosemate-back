<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class RelationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_relation(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregiver = User::factory()->create(['role' => 'caregiver']);

        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/relation', [
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);

        $response->assertCreated()
                 ->assertJson(['message' => 'Relation created successfully']);

        $this->assertDatabaseHas('patient_relation', [
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);
    }

    public function test_cannot_create_duplicate_relation(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregiver = User::factory()->create(['role' => 'caregiver']);

        DB::table('patient_relation')->insert([
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);

        Sanctum::actingAs($patient);

        $response = $this->postJson('/api/relation', [
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);

        $response->assertStatus(409)
                 ->assertJson(['message' => 'The relation already exists']);
    }

    public function test_get_caregiver_list(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregivers = User::factory()->count(2)->create(['role' => 'caregiver']);

        $patient->caregivers()->attach($caregivers->pluck('id'));

        Sanctum::actingAs($patient);

        $response = $this->getJson('/api/relation/caregiverList');

        $response->assertOk()
                 ->assertJsonCount(2);
    }

    public function test_get_patient_list(){
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patients = User::factory()->count(3)->create(['role' => 'patient']);

        foreach ($patients as $patient) {
            $patient->caregivers()->attach($caregiver->id);
        }

        Sanctum::actingAs($caregiver);

        $response = $this->getJson('/api/relation/patientList');

        $response->assertOk()
                 ->assertJsonCount(3);
    }

    public function test_delete_relation(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregiver = User::factory()->create(['role' => 'caregiver']);

        $patient->caregivers()->attach($caregiver->id);

        Sanctum::actingAs($patient);

        $response = $this->deleteJson('/api/relation/deleteRelation', [
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Relation patient-caregiver deleted']);

        $this->assertDatabaseMissing('patient_relation', [
            'patient_id' => $patient->id,
            'caregiver_id' => $caregiver->id,
        ]);
    }

    public function test_delete_all_caregivers(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregivers = User::factory()->count(2)->create(['role' => 'caregiver']);
        $patient->caregivers()->attach($caregivers->pluck('id'));

        Sanctum::actingAs($patient);

        $response = $this->deleteJson('/api/relation/deleteCaregivers');

        $response->assertOk()
                 ->assertJson(['message' => 'All caregivers of the patient are deleted']);

        $this->assertDatabaseMissing('patient_relation', [
            'patient_id' => $patient->id,
        ]);
    }

    public function test_delete_all_patients(){
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patients = User::factory()->count(2)->create(['role' => 'patient']);
        foreach ($patients as $patient) {
            $patient->caregivers()->attach($caregiver->id);
        }

        Sanctum::actingAs($caregiver);

        $response = $this->deleteJson('/api/relation/deletePatients');

        $response->assertOk()
                 ->assertJson(['message' => 'All patients of the caregiver are deleted']);

        $this->assertDatabaseMissing('patient_relation', [
            'caregiver_id' => $caregiver->id,
        ]);
    }
}

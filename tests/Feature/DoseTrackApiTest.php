<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Treatment;
use App\Models\Medicament;
use App\Models\DoseTrack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DoseTrackApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_dose_track(){
        $track = DoseTrack::factory()->make();
        $user = $track->medicament->treatment->patient;

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/dose-track', [
            'medicament_id' => $track->medicament_id,
            'schedule' => $track->schedule,
            'taken_dose' => $track->taken_dose,
            'taken_time' => $track->taken_time,
        ]);

        $response->assertCreated()
                 ->assertJson(['message' => 'Track successfully created']);

        $this->assertDatabaseHas('dose_track', [
            'medicament_id' => $track->medicament_id,
            'schedule' => $track->schedule,
        ]);
    }

    public function test_create_track_duplication(){
        $track = DoseTrack::factory()->create();
        Sanctum::actingAs($track->medicament->treatment->patient);

        $response = $this->postJson('/api/dose-track', [
            'medicament_id' => $track->medicament_id,
            'schedule' => $track->schedule,
        ]);

        $response->assertStatus(409)
                 ->assertJson(['error' => 'Track already exists']);
    }

    public function test_get_track_by_id(){
        $track = DoseTrack::factory()->create();
        Sanctum::actingAs($track->medicament->treatment->patient);

        $response = $this->getJson("/api/dose-track/trackById/{$track->id}");

        $response->assertOk()
                 ->assertJson(['id' => $track->id]);
    }

    public function test_get_tracks_by_medicament(){
        $medicament = Medicament::factory()->create();
        DoseTrack::factory()->count(3)->create(['medicament_id' => $medicament->id]);
        Sanctum::actingAs($medicament->treatment->patient);

        $response = $this->getJson("/api/dose-track/trackByMedicament/{$medicament->id}");

        $response->assertOk()
                 ->assertJsonCount(3);
    }

    public function test_get_tracks_by_schedule(){
        $medicament = Medicament::factory()->create();
        $track = DoseTrack::factory()->create([
            'medicament_id' => $medicament->id,
            'schedule' => now()->format('Y-m-d 08:00:00'),
        ]);
        Sanctum::actingAs($medicament->treatment->patient);

        $response = $this->getJson("/api/dose-track/trackBySchedule?schedule=" . now()->format('Y-m-d'));

        $response->assertOk();
        $this->assertNotEmpty($response->json());
    }

    public function test_update_track(){
        $track = DoseTrack::factory()->create();
        Sanctum::actingAs($track->medicament->treatment->patient);

        $response = $this->putJson("/api/dose-track/{$track->id}", [
            'taken_dose' => true,
            'taken_time' => now()->format('Y-m-d 08:00:00'),
        ]);

        $response->assertOk()
                 ->assertJson(['message' => 'Track updated successfully']);

        $this->assertDatabaseHas('dose_track', [
            'id' => $track->id,
            'taken_dose' => true,
            'taken_time' => now()->format('Y-m-d 08:00:00'),
        ]);
    }

    public function test_delete_track(){
        $track = DoseTrack::factory()->create();
        Sanctum::actingAs($track->medicament->treatment->patient);

        $response = $this->deleteJson("/api/dose-track/trackById/{$track->id}");

        $response->assertOk()
                 ->assertJson(['message' => 'Track deleted successfully']);

        $this->assertDatabaseMissing('dose_track', ['id' => $track->id]);
    }

    public function test_delete_track_not_found(){
        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson('/api/dose-track/trackById/999');

        $response->assertStatus(404)
                 ->assertJson(['error' => 'Track not found']);
    }

    public function test_delete_all_tracks_by_medicament(){
        $medicament = Medicament::factory()->create();
        DoseTrack::factory()->count(2)->create(['medicament_id' => $medicament->id]);

        Sanctum::actingAs($medicament->treatment->patient);

        $response = $this->deleteJson("/api/dose-track/trackByMedicament/{$medicament->id}");

        $response->assertOk()
                 ->assertJson(['message' => 'Tracks deleted']);

        $this->assertDatabaseMissing('dose_track', ['medicament_id' => $medicament->id]);
    }
}

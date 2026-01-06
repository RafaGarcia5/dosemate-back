<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Treatment;
use App\Models\Medicament;
use App\Models\DoseTrack;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DoseTrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_track_belongs_to_a_medicament(){
        $medicament = Medicament::factory()->create();
        $doseTrack = DoseTrack::factory()->create([
            'medicament_id' => $medicament->id,
        ]);

        $this->assertInstanceOf(Medicament::class, $doseTrack->medicament);
        $this->assertEquals($medicament->id, $doseTrack->medicament->id);
    }

    public function test_add_duplicate_schedule(){
        $medicament = Medicament::factory()->create();
        $schedule = now()->format('Y-m-d H:i:s');

        DoseTrack::factory()->create([
            'medicament_id' => $medicament->id,
            'schedule' => $schedule,
        ]);

        $exists = DoseTrack::existsDuplicate($medicament->id, $schedule);
        $this->assertEquals(1, $exists);
    }

    public function test_returns_schedule_tracks(){
        $user = User::factory()->create();
        $treatment = Treatment::factory()->create(['patient_id' => $user->id]);
        $medicament = Medicament::factory()->create([
            'treatment_id' => $treatment->id,
        ]);
        $schedule = now()->format('Y-m-d H:i:s');
        $track = DoseTrack::factory()->create([
            'medicament_id' => $medicament->id,
            'schedule' => $schedule,
        ]);

        $result = DoseTrack::findByScheduleWithRelations($user->id, now()->format('Y-m-d'));

        $this->assertCount(1, $result);
        $this->assertEquals($track->id, $result->first()->id);
        $this->assertEquals($medicament->name, $result->first()->medicament_name);
    }

    public function test_DoseTrack_model_has_fillable_attributes(){
        $doseTrack = new DoseTrack();
        $expected = [
            'medicament_id', 
            'schedule', 
            'taken_dose', 
            'taken_time',
        ];

        $this->assertEquals($expected, $doseTrack->getFillable());
    }

    public function test_DoseTrack_model_hides_defined_attributes(){
        $doseTrack = DoseTrack::factory()->make();

        $this->assertContains('created_at', $doseTrack->getHidden());
        $this->assertContains('updated_at', $doseTrack->getHidden());
    }
}

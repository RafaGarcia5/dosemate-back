<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DoseTrack;
use App\Models\Medicament;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MedicamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_date_formats(){
        $medicament = Medicament::factory()->make([
            'start_date' => '2025-06-01 08:00:00',
            'end_date' => '2025-06-03 00:00:00',
        ]);

        $this->assertEquals('2025-06-01 08:00:00', $medicament->start_date);
        $this->assertEquals('2025-06-03', $medicament->end_date);
    }

    public function test_medicament_belongs_to_a_treatment(){
        $treatment = Treatment::factory()->create();
        $medicament = Medicament::factory()->create([
            'treatment_id' => $treatment->id,
        ]);

        $this->assertInstanceOf(Treatment::class, $medicament->treatment);
        $this->assertEquals($treatment->id, $medicament->treatment->id);
    }

    public function test_medicament_has_tracks(){
        $medicament = Medicament::factory()->create();
        $track1 = DoseTrack::factory()->create(['medicament_id' => $medicament->id]);
        $track2 = DoseTrack::factory()->create(['medicament_id' => $medicament->id]);

        $this->assertCount(2, $medicament->doseTracks);
        $this->assertTrue($medicament->doseTracks->contains($track1));
        $this->assertTrue($medicament->doseTracks->contains($track2));
    }

    public function test_generate_dose_tracks(){
        $medicament = Medicament::factory()->create([
            'start_date' => '2025-06-01 08:00:00',
            'end_date' => '2025-06-01 20:00:00',
            'interval_hours' => 4,
        ]);

        $medicament->generateDoseTracks();

        $doseTracks = DoseTrack::where('medicament_id', $medicament->id)->get();

        $this->assertCount(4, $doseTracks);
        $this->assertEquals('2025-06-01 08:00:00', $doseTracks[0]->schedule);
        $this->assertEquals('2025-06-01 12:00:00', $doseTracks[1]->schedule);
        $this->assertEquals('2025-06-01 16:00:00', $doseTracks[2]->schedule);
        $this->assertEquals('2025-06-01 20:00:00', $doseTracks[3]->schedule);
    }

    public function test_medicament_model_has_fillable_attributes(){
        $medicament = new Medicament();
        $expected = [
            'name', 
            'dosage', 
            'interval_hours', 
            'start_date',
            'end_date',
            'comment', 
            'treatment_id',
        ];

        $this->assertEquals($expected, $medicament->getFillable());
    }

    public function test_medicament_model_hides_defined_attributes(){
        $medicament = Medicament::factory()->make();

        $this->assertContains('treatment_id', $medicament->getHidden());
        $this->assertContains('created_at', $medicament->getHidden());
        $this->assertContains('updated_at', $medicament->getHidden());
    }
}

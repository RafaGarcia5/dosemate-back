<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Treatment;
use App\Models\Medicament;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TreatmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_date_formats(){
        $treatment = Treatment::factory()->make([
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-10',

        ]);
        $this->assertEquals('2025-06-01', $treatment->start_date);
        $this->assertEquals('2025-06-10', $treatment->end_date);
    }

    public function test_treatment_belongs_to_a_patient(){
        $user = User::factory()->create();
        $treatment = Treatment::factory()->create([
            'patient_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $treatment->patient);
        $this->assertEquals($user->id, $treatment->patient->id);
    }

    public function test_treatment_has_medicaments(){
        $treatment = Treatment::factory()->create();
        $medicament1 = Medicament::factory()->create(['treatment_id' => $treatment->id]);
        $medicament2 = Medicament::factory()->create(['treatment_id' => $treatment->id]);

        $this->assertCount(2, $treatment->medicaments);
        $this->assertTrue($treatment->medicaments->contains($medicament1));
        $this->assertTrue($treatment->medicaments->contains($medicament2));
    }

    public function test_treatment_model_has_fillable_attributes(){
        $treatment = new Treatment();
        $expected = [
            'patient_id', 
            'name', 
            'start_date', 
            'end_date',
            'comment',
        ];

        $this->assertEquals($expected, $treatment->getFillable());
    }

    public function test_treatment_model_hides_defined_attributes(){
        $treatment = Treatment::factory()->make();

        $this->assertContains('created_at', $treatment->getHidden());
        $this->assertContains('updated_at', $treatment->getHidden());
    }
}

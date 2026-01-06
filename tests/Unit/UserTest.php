<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Treatment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_date_format(){
        $user = User::factory()->make(['birth_date' => '2001-01-09']);

        $this->assertEquals('2001-01-09', $user->birth_date);
    }

    public function test_user_has_treatments(){
        $user = User::factory()->create(['role' => 'patient']);
        $treatments = Treatment::factory()->count(3)->create(['patient_id' => $user->id]);

        $this->assertCount(3, $user->treatments);
    }

    public function test_user_caregiver_has_patients(){
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient = User::factory()->create(['role' => 'patient']);
        $caregiver->patients()->attach($patient->id);

        $this->assertTrue($caregiver->patients->contains($patient));
    }

    public function test_user_patient_has_caregivers(){
        $patient = User::factory()->create(['role' => 'patient']);
        $caregiver = User::factory()->create(['role' => 'caregiver']);
        $patient->caregivers()->attach($caregiver->id);

        $this->assertTrue($patient->caregivers->contains($caregiver));
    }

    public function test_user_model_has_fillable_attributes(){
        $user = new User();
        $expected = [
            'name', 
            'birth_date', 
            'gender', 
            'email',
            'password', 
            'role', 
            'doctor',
        ];

        $this->assertEquals($expected, $user->getFillable());
    }

    public function test_user_model_hides_defined_attributes(){
        $user = User::factory()->make();

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_register(){
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'birth_date' => '2001-01-09',
            'gender' => 'M',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'patient',
            'doctor' => null
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User successfully registered']);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_login(){
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'token', 'user']);
    }

    public function test_user_profile(){
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $user->id]);
    }

    public function test_user_update_profile(){
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User updated successfully']);

        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    }

    public function test_user_deleted(){
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/profile');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}

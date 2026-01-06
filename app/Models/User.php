<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'birth_date',
        'gender',
        'email',
        'password',
        'role',
        'doctor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function getBirthDateAttribute($value){
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function patients(){
        return $this->belongsToMany(User::class, 'patient_relation', 'caregiver_id', 'patient_id')
                    ->where('role', 'patient');
    }

    public function caregivers(){
        return $this->belongsToMany(User::class, 'patient_relation', 'patient_id', 'caregiver_id')
                    ->where('role', 'caregiver');
    }

    public function treatments(){
        return $this->hasMany(Treatment::class, 'patient_id');
    }
}

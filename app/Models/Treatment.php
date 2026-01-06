<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Treatment extends Model
{
    use HasFactory;
    protected $table = 'treatment';

    protected $fillable = [
        'patient_id',
        'name',
        'start_date',
        'end_date',
        'comment',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    { 
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function getStartDateAttribute($value){
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }
    
    public function getEndDateAttribute($value){
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function patient(){
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function medicaments(){
        return $this->hasMany(Medicament::class, 'treatment_id');
    }
}

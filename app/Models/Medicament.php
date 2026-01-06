<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\DoseTrack;
use Carbon\Carbon;

class Medicament extends Model
{
    use HasFactory;
    protected $table = 'medicament';

    protected $fillable = [
        'name',
        'dosage',
        'interval_hours',
        'start_date',
        'end_date',
        'comment',
        'treatment_id',
    ];

    protected $hidden = [
        'treatment_id',
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
        return \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
    
    public function getEndDateAttribute($value){
        return \Carbon\Carbon::parse($value)->format('Y-m-d');
    }

    public function treatment(){
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }

    public function doseTracks(){
        return $this->hasMany(DoseTrack::class);
    }

    public function generateDoseTracks(){
        if (!$this->start_date || !$this->end_date || !$this->interval_hours) {
            return;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date)->endOfDay();
        $tracks = [];
        while ($start <= $end) {
            $tracks[] = [
                'medicament_id' => $this->id,
                'schedule' => $start->format('Y-m-d H:i:s'),
                'taken_dose' => false,
                'taken_time' => null,
            ];
            $start->addHours($this->interval_hours);
        }
        DoseTrack::insert($tracks);
    }

}

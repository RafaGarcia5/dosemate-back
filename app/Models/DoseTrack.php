<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoseTrack extends Model
{
    use HasFactory;

    protected $table = 'dose_track';
    public $timestamps = false;

    protected $fillable = [
        'medicament_id',
        'schedule',
        'taken_dose',
        'taken_time',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function medicament(){
        return $this->belongsTo(Medicament::class);
    }

    public static function existsDuplicate($medicamentId, $schedule){
        return self::where('medicament_id', $medicamentId)
                   ->where('schedule', $schedule)
                   ->count();
    }

    public static function findByScheduleWithRelations($userId, $schedule){
        $trackList = self::select('dose_track.*', 'm.name as medicament_name', 'm.dosage', 'm.interval_hours', 't.name as treatment_name')
            ->join('medicament as m', 'dose_track.medicament_id', '=', 'm.id')
            ->join('treatment as t', 'm.treatment_id', '=', 't.id')
            ->where('t.patient_id', $userId)
            ->whereDate('dose_track.schedule', $schedule)
            ->get();
        return $trackList;
    }
}

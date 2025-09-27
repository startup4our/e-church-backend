<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $table = 'schedule';
    protected $fillable = [
        'name',
        'description',
        'local',
        'start_date',
        'end_date',
        'observation',
        'type',
        'approved',
        'user_creator'
    ];

    protected $casts = [
        'type' => ScheduleType::class,
        'approved' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Relação com UserSchedule (N:1)
     */
    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class, 'schedule_id');
    }
}

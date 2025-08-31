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
        'date_time',
        'observation',
        'type',
        'aproved',
        'user_creator'
    ];

    protected $casts = [
        'type' => ScheduleType::class,
        'aproved' => 'boolean',
        'date_time' => 'datetime',
    ];

    // Relação com User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_creator');
    }
}

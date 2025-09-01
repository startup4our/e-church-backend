<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    use HasFactory;

    protected $table = 'user_schedule';

    protected $fillable = [
        'schedule_id',
        'user_id',
        'status'
    ];

    protected $casts = [
        'status' => UserScheduleStatus::class,
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    // Relação com User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

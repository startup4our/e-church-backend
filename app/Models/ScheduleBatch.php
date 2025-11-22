<?php

namespace App\Models;

use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleBatch extends Model
{
    use HasFactory;

    protected $table = 'schedule_batch';

    protected $fillable = [
        'name',
        'total_schedules',
        'created_schedules',
        'failed_schedules',
        'recurrence',
        'start_date',
        'end_date',
        'status',
        'error_message',
        'template_id',
        'user_creator'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => BatchStatus::class,
    ];

    /**
     * Relação com Schedule (many-to-many)
     */
    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'schedule_batch_schedule', 'batch_id', 'schedule_id');
    }

    /**
     * Relação com ScheduleTemplate
     */
    public function template()
    {
        return $this->belongsTo(ScheduleTemplate::class, 'template_id');
    }

    /**
     * Relação com User (creator)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_creator');
    }
}


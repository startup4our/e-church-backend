<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplateRole extends Model
{
    use HasFactory;

    protected $table = 'schedule_template_role';

    protected $fillable = [
        'template_id',
        'area_id',
        'role_id',
        'count',
        'order'
    ];

    /**
     * Relação com ScheduleTemplate
     */
    public function template()
    {
        return $this->belongsTo(ScheduleTemplate::class, 'template_id');
    }

    /**
     * Relação com Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Relação com Role
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}


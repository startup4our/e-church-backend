<?php

namespace App\Models;

use App\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ScheduleTemplate extends Model
{
    use HasFactory;

    protected $table = 'schedule_template';

    protected $fillable = [
        'name',
        'type',
        'user_id',
        'music_template_id'
    ];

    protected $casts = [
        'type' => ScheduleType::class,
    ];

    /**
     * Relação com User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relação com ScheduleTemplateRole
     */
    public function templateRoles()
    {
        return $this->hasMany(ScheduleTemplateRole::class, 'template_id');
    }

    /**
     * Relação com MusicTemplate (sem FK constraint por enquanto)
     * TODO: Implementar quando a tabela music_template for criada
     */
    // public function musicTemplate()
    // {
    //     return $this->belongsTo(MusicTemplate::class, 'music_template_id');
    // }

    /**
     * Retorna áreas únicas do template
     */
    public function getAreasAttribute()
    {
        return $this->templateRoles()
            ->with('area')
            ->get()
            ->pluck('area')
            ->unique('id')
            ->values();
    }
}


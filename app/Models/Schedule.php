<?php

namespace App\Models;

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

    // Relação com User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

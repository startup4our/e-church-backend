<?php

namespace App\Models;

use App\Enums\RecordingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $table = 'recordings';
    protected $fillable = [
        'path',
        'type',
        'description',
        'song_id'
    ];

    protected $casts = [
        'type' => RecordingType::class,
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}

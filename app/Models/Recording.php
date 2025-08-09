<?php

namespace App\Models;

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

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}

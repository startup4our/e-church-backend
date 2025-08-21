<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table = 'song';
    protected $fillable = [
        'cover_path',
        'name',
        'artist',
        'spotify_id',
        'preview_url',
        'duration',
        'album',
        'spotify_url',
    ];
}

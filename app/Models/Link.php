<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $table = 'links';
    protected $fillable = [
        'name',
        'destination',
        'description',
        'song_id'
    ];

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}

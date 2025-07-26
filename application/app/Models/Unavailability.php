<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unavailability extends Model
{
    use HasFactory;

    protected $table = 'unavailability';
    protected $fillable = [
        'weekday',
        'shift',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

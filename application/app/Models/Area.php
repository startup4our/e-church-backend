<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];
}

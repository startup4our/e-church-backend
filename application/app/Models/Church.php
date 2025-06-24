<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Church extends Model
{
    use HasFactory;
    protected $table = 'church'; 
    protected $fillable = [
        'name',
        'cep',
        'street',
        'number',
        'complement',
        'quarter',
        'city',
        'state',
    ];
}

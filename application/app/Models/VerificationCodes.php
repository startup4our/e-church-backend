<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCodes extends Model
{
    use HasFactory;
    protected $table = 'verification_codes';
    protected $fillable = [
        'code',
        'used',
    ];
}

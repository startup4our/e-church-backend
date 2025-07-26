<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DateException extends Model
{
    use HasFactory;

    protected $table = 'date_exceptions';
    protected $fillable = [
        'exception_date',
        'shift',
        'justification',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

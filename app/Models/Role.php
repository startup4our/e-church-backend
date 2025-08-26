<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'description',
        'area_id'
    ];

    // Relação com Area
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}

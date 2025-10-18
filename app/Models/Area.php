<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $table = 'area';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'church_id',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'area_id');
    }
}

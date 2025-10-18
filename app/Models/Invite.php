<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $table = 'invite';

    protected $fillable = [
        'email',
        'area_id',
        'church_id',
        'role_ids',
        'token',
        'used',
        'expires_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}

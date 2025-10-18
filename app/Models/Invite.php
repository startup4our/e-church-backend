<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    protected $table = 'invite';

    protected $fillable = [
        'email',
          'church_id',
        'token',
        'used',
        'expires_at',
    ];

    protected $casts = [
        'used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'invite_area');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'invite_role');
    }
}

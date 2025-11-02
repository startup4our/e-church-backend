<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permission';

    protected $fillable = [
        'user_id',
        'create_scale',
        'read_scale',
        'update_scale',
        'delete_scale',
        'create_music',
        'read_music',
        'update_music',
        'delete_music',
        'create_role',
        'read_role',
        'update_role',
        'delete_role',
        'create_area',
        'read_area',
        'update_area',
        'delete_area',
        'create_chat',
        'read_chat',
        'update_chat',
        'delete_chat',
        'manage_handouts',
        'manage_users',
        'manage_church_settings',
        'manage_app_settings',
    ];

    public $timestamps = false;

    // Relação com User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'area_id',
        'create_scale','read_scale','update_scale','delete_scale',
        'create_music','read_music','update_music','delete_music',
        'create_role','read_role','update_role','delete_role',
        'create_area','read_area','update_area','delete_area',
        'manage_users','manage_church_settings','manage_app_settings',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Enums\UserStatus;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'fcm_token',
        'photo_path',
        'birthday',
        'password',
        'status',
        'church_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    public function areas()
    {
        return $this->hasMany(UserArea::class, 'user_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('priority')
            ->orderBy('role_user.priority', 'asc');
    }

    /**
     * Get user roles ordered by priority
     */
    public function getRolesByPriority()
    {
        return $this->roles()->orderBy('role_user.priority', 'asc')->get();
    }

    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class, 'user_id');
    }
}

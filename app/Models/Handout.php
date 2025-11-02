<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handout extends Model
{
    use HasFactory;

    protected $fillable = [
        'church_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'priority',
        'status',
        'area_id', // can be null if handout is for all areas
        'link_name',
        'link_url',
        'image_url',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationship church
    public function church()
    {
        return $this->belongsTo(Church::class);
    }

    // Relationship area
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'A');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'P');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'I');
    }

    public function scopeVisibleNow($query)
    {
        $now = now();
        return $query->where('status', 'A')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }
}

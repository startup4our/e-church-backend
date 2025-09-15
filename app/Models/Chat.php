<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = 'chat';
    protected $fillable = [
        'name',
        'description',
        'chatable_id',
        'chatable_type',
    ];

     /**
     * Relação polimórfica (Area ou Schedule)
     */
    public function chatable()
    {
        return $this->morphTo();
    }
}

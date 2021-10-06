<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $casts = [
        'players_id' => 'array',
    ];

    public function fields() {
        return $this->hasMany(Field::class, 'id', 'fields_id');
    }
    public function players() {
        return $this->hasMany(Player::class, 'id', 'players_id');
    }

}

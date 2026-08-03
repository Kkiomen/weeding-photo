<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guest extends Model
{
    protected $fillable = ['nickname', 'xp'];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function getLevelAttribute(): int
    {
        return (int) floor($this->xp / 100) + 1;
    }
}

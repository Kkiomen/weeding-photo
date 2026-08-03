<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = ['title', 'description', 'icon', 'xp_reward', 'sort_order'];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }
}

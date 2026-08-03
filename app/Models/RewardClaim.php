<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardClaim extends Model
{
    protected $fillable = ['guest_id', 'reward_id', 'claimed_at'];

    protected $casts = ['claimed_at' => 'datetime'];

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}

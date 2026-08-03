<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BingoCard extends Model
{
    protected $fillable = ['guest_id', 'field_ids', 'lines_won', 'full_card_won'];

    protected $casts = [
        'field_ids' => 'array',
        'full_card_won' => 'bool',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function marks(): HasMany
    {
        return $this->hasMany(BingoMark::class, 'card_id');
    }
}

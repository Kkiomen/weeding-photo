<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BingoMark extends Model
{
    protected $fillable = ['card_id', 'field_id', 'photo_id', 'marked_at'];

    protected $casts = ['marked_at' => 'datetime'];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(BingoCard::class, 'card_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BingoField::class, 'field_id');
    }
}

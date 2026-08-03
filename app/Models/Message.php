<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['guest_id', 'body', 'photo_path', 'thumb_path'];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}

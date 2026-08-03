<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Photo extends Model
{
    protected $fillable = ['guest_id', 'task_id', 'path', 'thumb_path', 'file_hash'];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function bingoMark(): HasOne
    {
        return $this->hasOne(BingoMark::class);
    }
}

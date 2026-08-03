<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLike extends Model
{
    protected $fillable = ['quote_id', 'guest_id'];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exchange_id',
    'giver_id',
    'receiver_id',
])]
class GiftExchangeExclusion extends Model
{
    use HasFactory;

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(GiftExchange::class, 'exchange_id');
    }

    public function giver(): BelongsTo
    {
        return $this->belongsTo(GiftExchangeParticipant::class, 'giver_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(GiftExchangeParticipant::class, 'receiver_id');
    }
}

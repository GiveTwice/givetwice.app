<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftExchangeExclusion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange_id',
        'giver_id',
        'receiver_id',
    ];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class GiftExchangeParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'exchange_id',
        'user_id',
        'name',
        'email',
        'token',
        'assigned_to_participant_id',
        'joined_at',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'viewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GiftExchangeParticipant $participant) {
            if (empty($participant->token)) {
                $participant->token = Str::random(64);
            }
        });
    }

    public function exchange(): BelongsTo
    {
        return $this->belongsTo(GiftExchange::class, 'exchange_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assigned_to_participant_id');
    }

    public function assignedBy(): HasOne
    {
        return $this->hasOne(self::class, 'assigned_to_participant_id', 'id');
    }

    public function defaultWishlist(): ?GiftList
    {
        if (! $this->user_id) {
            return null;
        }

        return GiftList::where('creator_id', $this->user_id)
            ->where('is_default', true)
            ->first();
    }

    public function hasViewed(): bool
    {
        return ! is_null($this->viewed_at);
    }

    public function markAsViewed(): void
    {
        if (! $this->hasViewed()) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function isTokenExpired(): bool
    {
        return $this->created_at->addDays(90)->isPast();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'wishlist_id',
        'whatsapp_opened_at',
        'sent_at',
        'token_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'viewed_at' => 'datetime',
            'whatsapp_opened_at' => 'datetime',
            'sent_at' => 'datetime',
            'token_generated_at' => 'datetime',
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

    /** @return BelongsTo<GiftExchange, $this> */
    public function exchange(): BelongsTo
    {
        return $this->belongsTo(GiftExchange::class, 'exchange_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<self, $this> */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'assigned_to_participant_id');
    }

    public function assignedBy(): HasOne
    {
        return $this->hasOne(self::class, 'assigned_to_participant_id', 'id');
    }

    public function exclusionsAsGiver(): HasMany
    {
        return $this->hasMany(GiftExchangeExclusion::class, 'giver_id');
    }

    public function exclusionsAsReceiver(): HasMany
    {
        return $this->hasMany(GiftExchangeExclusion::class, 'receiver_id');
    }

    /** @return BelongsTo<GiftList, $this> */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'wishlist_id');
    }

    public function defaultWishlist(): ?GiftList
    {
        if ($this->wishlist_id) {
            return $this->wishlist;
        }

        if (! $this->user_id) {
            return null;
        }

        return GiftList::where('creator_id', $this->user_id)
            ->where('is_default', true)
            ->first();
    }

    public static function forReveal(string $token): self
    {
        $participant = self::where('token', $token)->with('exchange', 'wishlist')->firstOrFail();
        abort_unless($participant->exchange->isDrawn(), 404);
        abort_if($participant->isTokenExpired(), 410, __('This link has expired. Ask the organizer for a new one.'));

        return $participant;
    }

    public function revealUrl(): string
    {
        return route('exchanges.reveal', ['locale' => $this->exchange->locale, 'token' => $this->token]);
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
        return ($this->token_generated_at ?? $this->created_at)->copy()->addDays(90)->isPast();
    }
}

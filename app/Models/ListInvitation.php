<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property Carbon $expires_at
 * @property Carbon|null $accepted_at
 * @property Carbon|null $declined_at
 * @property GiftList $list
 * @property User $inviter
 * @property User|null $invitee
 */
class ListInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'list_id',
        'inviter_id',
        'invitee_id',
        'email',
        'token',
        'accepted_at',
        'declined_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ListInvitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(64);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(30);
            }
        });
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at)
            && is_null($this->declined_at)
            && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function accept(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    public function decline(): void
    {
        $this->update(['declined_at' => now()]);
    }
}

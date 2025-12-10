<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Claim extends Model
{
    protected $fillable = [
        'gift_id',
        'user_id',
        'claimer_email',
        'claimer_name',
        'confirmation_token',
        'confirmed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Claim $claim) {
            if (empty($claim->confirmation_token) && empty($claim->user_id)) {
                $claim->confirmation_token = Str::random(64);
            }
        });
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public function isPending(): bool
    {
        return $this->confirmed_at === null;
    }

    public function isAnonymous(): bool
    {
        return $this->user_id === null;
    }

    public function confirm(): void
    {
        $this->update([
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    public function getClaimerDisplayName(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->claimer_name ?? $this->claimer_email ?? 'Someone';
    }
}

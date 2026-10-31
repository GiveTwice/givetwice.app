<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property array<string, mixed>|null $reservation
 * @property Carbon|null $confirmed_at
 * @property string|null $notes
 */
class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation',
        'resolution',
        'gift_id',
        'user_id',
        'claimer_email',
        'claimer_name',
        'confirmation_token',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'reservation' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Claim $claim) {
            $claim->claimer_email ??= $claim->user?->email;
            $claim->reservation ??= $claim->snapshot();
            if (empty($claim->confirmation_token)) {
                $claim->confirmation_token = Str::random(64);
            }
        });
    }

    /**
     * @return BelongsTo<Gift, $this>
     */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class)->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function latestAlert(): ?ReservationAlert
    {
        return $this->alerts->whereNull('resolved_at')->sortByDesc('id')->first();
    }

    /** @return HasMany<ReservationAlert, $this> */
    public function alerts(): HasMany
    {
        return $this->hasMany(ReservationAlert::class);
    }

    public function historyUrl(?string $locale = null): string
    {
        return URL::temporarySignedRoute('reservations.index', now()->addDays(30), ['locale' => $locale ?? app()->getLocale(), 'email' => $this->claimer_email ?? $this->user->email]);
    }

    public function isRemoved(): bool
    {
        return ! $this->gift || $this->gift->trashed() || (! empty($this->reservation['list_id']) && ! $this->gift->lists->contains('id', $this->reservation['list_id']));
    }

    public function snapshot(): array
    {
        $gift = $this->gift?->loadMissing('user');
        $list = $gift?->lists()->with('creator')->find(request()->integer('list')) ?? $gift?->lists()->with('creator')->first();

        return ['title' => $gift?->title, 'price' => $gift?->formatPrice(), 'url' => $gift?->url, 'owner_note' => $gift?->owner_note,
            'image' => $gift?->getImageUrl('thumb'), 'list_id' => $list?->id, 'list_name' => $list?->name,
            'owner' => $list ? $list->creator->name : $gift?->user?->name, 'owner_id' => $list ? $list->creator_id : $gift?->user_id,
            'date' => $list?->occasion_date?->format('Y-m-d')];
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
        ]);
    }

    public function getClaimerDisplayName(): string
    {
        /** @var User|null $user */
        $user = $this->user;
        if ($user) {
            return $user->name;
        }

        return $this->claimer_name ?? $this->claimer_email ?? 'Someone';
    }
}

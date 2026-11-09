<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * @property array<string, mixed>|null $reservation
 * @property Carbon|null $confirmed_at
 * @property string|null $notes
 */
#[Fillable([
    'receipt_requested_at',
    'receipt_sent_at',
    'receipt_locale',
    'reservation',
    'resolution',
    'gift_id',
    'user_id',
    'claimer_email',
    'claimer_name',
    'confirmation_token',
    'confirmed_at',
])]
class Claim extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'receipt_requested_at' => 'datetime',
            'receipt_sent_at' => 'datetime',
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

    #[Scope]
    protected function confirmed(Builder $query): void
    {
        $query->whereNotNull('confirmed_at');
    }

    public function belongsToBrowser(Request $request): bool
    {
        return ($request->user() && $this->user_id === $request->user()->id)
            || ($request->session()->has('reservations_email') && $this->claimer_email === $request->session()->get('reservations_email'))
            || (filled($this->confirmation_token) && hash_equals($this->confirmation_token, $request->cookie('reservation_'.$this->gift_id, '')));
    }

    /**
     * Returns the address that gets mail about this reservation. New claims store it in
     * claimer_email; older account reservations only have it on the user.
     */
    public function recipientEmail(): string
    {
        return $this->claimer_email ?? $this->user->email ?? '';
    }

    public function historyUrl(?string $locale = null): string
    {
        return URL::temporarySignedRoute('reservations.index', now()->addDays(30), ['locale' => $locale ?? app()->getLocale(), 'email' => $this->recipientEmail()]);
    }

    public function isRemoved(): bool
    {
        return ! $this->gift || $this->gift->trashed() || (! empty($this->reservation['list_id']) && ! $this->gift->lists->contains('id', $this->reservation['list_id']));
    }

    public function snapshot(): array
    {
        $gift = $this->gift?->loadMissing('user');
        $list = $gift?->lists()->with('creator')->find(request()->integer('list')) ?? $gift?->lists()->with('creator')->first();

        return ['locale' => app()->getLocale(), 'title' => $gift?->title, 'price' => $gift?->formatPrice(), 'url' => $gift?->url, 'owner_note' => $gift?->owner_note,
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

    public function confirm(): void
    {
        $this->update([
            'confirmed_at' => now(),
        ]);
    }
}

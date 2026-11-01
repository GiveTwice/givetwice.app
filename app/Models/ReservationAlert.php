<?php

namespace App\Models;

use App\Jobs\SendReservationAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/** @property array<string, mixed> $before
 * @property array<string, mixed> $after
 */
class ReservationAlert extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['before' => 'array', 'after' => 'array', 'sent_at' => 'datetime'];
    }

    /** @return BelongsTo<Claim, $this> */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    /** @return array<string, array{before: mixed, after: mixed}> */
    public function changedFields(): array
    {
        if ($this->after['deleted']) {
            return [];
        }
        $changes = [];
        foreach (['title', 'price', 'url', 'owner_note'] as $field) {
            if (($this->before[$field] ?? null) !== ($this->after[$field] ?? null)) {
                $changes[$field] = ['before' => $this->before[$field] ?? null, 'after' => $this->after[$field] ?? null];
            }
        }

        return $changes;
    }

    public function summary(): string
    {
        return __('reservations.'.($this->after['deleted'] ? 'list_removed' : 'list_changed'), [
            'list' => $this->before['list_name'] ?? $this->before['owner'],
            'gift' => $this->before['title'],
        ]);
    }

    public function choiceUrl(string $choice): string
    {
        return URL::temporarySignedRoute($choice === 'mute' ? 'reservations.mute' : 'reservations.choose', now()->addDays(30), ['locale' => $this->locale, 'claim' => $this->claim_id, 'choice' => $choice, 'email' => hash('sha256', $this->email), 'alert' => $this->id]);
    }

    public static function record(Gift $gift, bool $deleted = false, ?int $listId = null): void
    {
        foreach ($gift->claims()->whereNotNull('confirmed_at')->get() as $claim) {
            if ($listId && $claim->reservation['list_id'] !== $listId) {
                continue;
            }
            $claim->getConnection()->transaction(function () use ($claim, $gift, $deleted) {
                $claim->refreshForUpdate();
                $email = $claim->claimer_email ?? $claim->user->email;
                $isManager = GiftList::query()->whereKey($claim->reservation['list_id'])
                    ->where(fn ($query) => $query->whereHas('creator', fn ($query) => $query->where('email', $email))
                        ->orWhereHas('users', fn ($query) => $query->where('email', $email)))->exists();
                if ($isManager) {
                    $claim->alerts()->whereNull('sent_at')->update(['resolved_at' => now()]);

                    return;
                }
                $old = new Gift($gift->getOriginal());
                $before = array_replace($claim->reservation, ['title' => $old->title, 'price' => $old->formatPrice(), 'url' => $old->url, 'owner_note' => $old->owner_note]);
                $after = array_replace($before, ['title' => $gift->title, 'price' => $gift->formatPrice(), 'url' => $gift->url, 'owner_note' => $gift->owner_note, 'deleted' => $deleted]);
                $alert = static::where('claim_id', $claim->id)->whereNull('sent_at')->whereNull('resolved_at')->first();
                if ($alert) {
                    $alert->update(['after' => $after]);
                } else {
                    $alert = static::create(['claim_id' => $claim->id, 'gift_id' => $gift->id, 'list_id' => $claim->reservation['list_id'], 'email' => $claim->claimer_email ?? $claim->user->email, 'locale' => $claim->user->locale_preference ?? $claim->reservation['locale'] ?? app()->getLocale(), 'before' => $before, 'after' => $after]);
                    SendReservationAlert::dispatch($alert->id)->delay(now()->addMinutes(10))->afterCommit();
                }
            });
        }
    }
}

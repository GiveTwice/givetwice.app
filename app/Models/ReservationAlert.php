<?php

namespace App\Models;

use App\Jobs\SendReservationAlert;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/** @property array<string, mixed> $before
 * @property array<string, mixed> $after
 */
#[Unguarded]
class ReservationAlert extends Model
{
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

    /**
     * Returns the occasion date that decides whether this reservation is settled.
     * The list's current occasion date wins while the list exists. After the list is gone,
     * the date saved on the alert when it was recorded is used instead.
     */
    public function occasionDate(): ?string
    {
        $list = GiftList::find($this->list_id);

        return $list ? $list->occasion_date?->toDateString() : ($this->after['date'] ?? null);
    }

    public function removalSummary(): string
    {
        return __('reservations.removed_summary', ['list' => $this->before['list_name'] ?? $this->before['owner']]);
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
        foreach ($gift->claims()->confirmed()->get() as $claim) {
            if ($listId && $claim->reservation['list_id'] !== $listId) {
                continue;
            }
            $claim->getConnection()->transaction(function () use ($claim, $gift, $deleted) {
                $claim->refreshForUpdate();
                $email = $claim->recipientEmail();
                $isManager = GiftList::query()->whereKey($claim->reservation['list_id'])
                    ->where(fn ($query) => $query->whereHas('creator', fn ($query) => $query->where('email', $email))
                        ->orWhereHas('users', fn ($query) => $query->where('email', $email)))->exists();
                if ($isManager) {
                    $claim->alerts()->whereNull('sent_at')->update(['resolved_at' => now()]);

                    return;
                }
                $list = GiftList::find($claim->reservation['list_id']);
                $occasionDate = $list ? $list->occasion_date?->toDateString() : ($claim->reservation['date'] ?? null);
                if ($claim->isSettled($occasionDate)) {
                    $claim->alerts()->whereNull('resolved_at')->when(! $deleted, fn ($alerts) => $alerts->whereNull('sent_at'))->update(['resolved_at' => now()]);

                    return;
                }
                $old = new Gift($gift->getOriginal());
                $before = array_replace($claim->reservation, ['title' => $old->title, 'price' => $old->formatPrice(), 'url' => $old->url, 'owner_note' => $old->owner_note]);
                $after = array_replace($before, ['title' => $gift->title, 'price' => $gift->formatPrice(), 'url' => $gift->url, 'owner_note' => $gift->owner_note, 'date' => $occasionDate, 'deleted' => $deleted]);
                $alert = static::where('claim_id', $claim->id)->whereNull('sent_at')->whereNull('resolved_at')->first();
                if ($alert) {
                    $alert->update(['after' => $after]);
                } else {
                    $alert = static::create(['claim_id' => $claim->id, 'gift_id' => $gift->id, 'list_id' => $claim->reservation['list_id'], 'email' => $claim->recipientEmail(), 'locale' => $claim->user->locale_preference ?? $claim->reservation['locale'] ?? app()->getLocale(), 'before' => $before, 'after' => $after]);
                    SendReservationAlert::dispatch($alert->id)->delay(now()->addMinutes(10))->afterCommit();
                }
            });
        }
    }
}

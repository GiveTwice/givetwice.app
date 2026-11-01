<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class FollowedList extends Model
{
    protected $fillable = [
        'user_id',
        'list_id',
        'notifications',
        'last_change_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $follow): void {
            $follow->last_change_id = ListChange::where('list_id', $follow->list_id)->max('id') ?? 0;
        });
    }

    public function muteUrl(): string
    {
        return URL::signedRoute('list-updates.mute', [
            'locale' => $this->user->locale_preference ?? 'en', 'follow' => $this->id,
        ]);
    }

    protected function casts(): array
    {
        return [
            'notifications' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<GiftList, $this>
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('list', function (Builder $listQuery) use ($user) {
            $listQuery->where('creator_id', '!=', $user->id)
                ->whereDoesntHave('users', function (Builder $userQuery) use ($user) {
                    $userQuery->where('user_id', $user->id);
                });
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if (! $this->list) {
            return false;
        }

        return $this->list->creator_id !== $user->id
            && ! $this->list->hasUser($user);
    }
}

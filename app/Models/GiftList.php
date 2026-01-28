<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiftList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'creator_id',
        'name',
        'description',
        'slug',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GiftList $list) {
            if (empty($list->slug)) {
                // Temporary slug for insertion - will be updated after creation
                $list->slug = Str::uuid()->toString();
            }
        });

        static::created(function (GiftList $list) {
            $list->slug = $list->generateSlug();
            $list->saveQuietly();
        });

        static::updating(function (GiftList $list) {
            if ($list->isDirty('name')) {
                $list->slug = $list->generateSlug();
            }
        });
    }

    public function generateSlug(): string
    {
        $baseSlug = Str::slug($this->name) ?: 'list';

        return $this->id.'-'.$baseSlug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getPublicUrl(?string $locale = null): string
    {
        return route('public.list', [
            'locale' => $locale ?? app()->getLocale(),
            'list' => $this->id,
            'slug' => $this->slug,
        ]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'list_user', 'list_id', 'user_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function hasUser(User $user): bool
    {
        if ($this->relationLoaded('users')) {
            return $this->users->contains('id', $user->id);
        }

        return $this->users()->where('user_id', $user->id)->exists();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ListInvitation::class, 'list_id');
    }

    public function pendingInvitations(): HasMany
    {
        return $this->invitations()
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where('expires_at', '>', now());
    }

    public function followers(): HasMany
    {
        return $this->hasMany(FollowedList::class, 'list_id');
    }

    public function gifts(): BelongsToMany
    {
        return $this->belongsToMany(Gift::class, 'gift_list', 'list_id', 'gift_id')
            ->withPivot('sort_order', 'added_at')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}

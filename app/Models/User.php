<?php

namespace App\Models;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $last_active_at
 * @property \Illuminate\Support\Carbon|null $inactive_warning_sent_at
 * @property \Illuminate\Support\Carbon|null $last_friend_digest_at
 */
class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory;
    use Impersonate;
    use InteractsWithMedia;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'locale_preference',
        'google_id',
        'facebook_id',
        'is_admin',
        'email_verified_at',
        'friend_notifications_enabled',
        'last_friend_digest_at',
        'last_active_at',
        'inactive_warning_sent_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'friend_notifications_enabled' => 'boolean',
            'last_friend_digest_at' => 'datetime',
            'last_active_at' => 'datetime',
            'inactive_warning_sent_at' => 'datetime',
        ];
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }

    /**
     * @return BelongsToMany<GiftList, $this>
     */
    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(GiftList::class, 'list_user', 'user_id', 'list_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function createdLists(): HasMany
    {
        return $this->hasMany(GiftList::class, 'creator_id');
    }

    public function listInvitations(): HasMany
    {
        return $this->hasMany(ListInvitation::class, 'invitee_id');
    }

    public function pendingListInvitations(): HasMany
    {
        return $this->listInvitations()
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->where('expires_at', '>', now());
    }

    public function hasPendingListInvitations(): bool
    {
        return $this->pendingListInvitations()->exists();
    }

    /**
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * @return HasMany<FollowedList, $this>
     */
    public function followedLists(): HasMany
    {
        return $this->hasMany(FollowedList::class);
    }

    /**
     * Follow a list if the user is eligible (not creator, not collaborator).
     */
    public function followListIfEligible(GiftList $list): void
    {
        if ($list->creator_id === $this->id || $list->hasUser($this)) {
            return;
        }

        FollowedList::firstOrCreate([
            'user_id' => $this->id,
            'list_id' => $list->id,
        ]);
    }

    public function defaultList(): ?GiftList
    {
        /** @var GiftList|null */
        return $this->lists()->where('lists.is_default', true)->first();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->useFallbackUrl($this->avatar ?? '')
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 96, 96)
            ->nonQueued()
            ->performOnCollections('profile');

        $this->addMediaConversion('medium')
            ->fit(Fit::Crop, 256, 256)
            ->nonQueued()
            ->performOnCollections('profile');
    }

    public function getProfileImageUrl(?string $conversion = null): ?string
    {
        $media = $this->getFirstMedia('profile');

        if ($media) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }

        return $this->avatar;
    }

    public function hasProfileImage(): bool
    {
        return $this->hasMedia('profile') || $this->avatar !== null;
    }

    public function getInitials(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr(end($words), 0, 1));
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }

    public function canImpersonate(): bool
    {
        return $this->is_admin;
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->is_admin;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(
            (new VerifyEmail)->locale($this->locale_preference ?? config('app.locale'))
        );
    }
}

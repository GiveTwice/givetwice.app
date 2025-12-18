<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, InteractsWithMedia, Notifiable, TwoFactorAuthenticatable;

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
        ];
    }

    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class);
    }

    public function lists(): HasMany
    {
        return $this->hasMany(GiftList::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function defaultList(): ?GiftList
    {
        /** @var GiftList|null */
        return $this->lists()->where('is_default', true)->first();
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
        return $this->hasMedia('profile') || $this->avatar;
    }

    public function getInitials(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1).mb_substr(end($words), 0, 1));
        }

        return strtoupper(mb_substr($this->name, 0, 2));
    }
}

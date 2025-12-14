<?php

namespace App\Models;

use App\Enums\SupportedCurrency;
use App\Events\GiftCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Gift extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $dispatchesEvents = [
        'created' => GiftCreated::class,
    ];

    protected $attributes = [
        'fetch_status' => 'pending',
    ];

    protected $fillable = [
        'user_id',
        'url',
        'title',
        'description',
        'price_in_cents',
        'currency',
        'original_image_url',
        'fetch_status',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'price_in_cents' => 'integer',
            'fetched_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->useFallbackUrl($this->original_image_url ?? '')
            ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Max, 256, 256)
            ->performOnCollections('image')
            ->nonQueued();

        $this->addMediaConversion('card')
            ->fit(Fit::Max, 600, 600)
            ->performOnCollections('image')
            ->nonQueued();

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1000, 1000)
            ->performOnCollections('image')
            ->nonQueued();
    }

    public function formatPrice(bool $useSymbol = true): ?string
    {
        if ($this->price_in_cents === null) {
            return null;
        }

        $amount = number_format($this->price_in_cents / 100, 2, '.', '');
        $currency = SupportedCurrency::tryFrom($this->currency);
        $currencyDisplay = $useSymbol && $currency
            ? $currency->symbol()
            : $this->currency;

        return $currencyDisplay.' '.$amount;
    }

    public function getPriceAsDecimal(): ?float
    {
        if ($this->price_in_cents === null) {
            return null;
        }

        return $this->price_in_cents / 100;
    }

    public function hasPrice(): bool
    {
        return $this->price_in_cents !== null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(GiftList::class, 'gift_list', 'gift_id', 'list_id')
            ->withPivot('sort_order', 'added_at')
            ->withTimestamps();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function isClaimed(): bool
    {
        return $this->claims()->whereNotNull('confirmed_at')->exists();
    }

    public function isPending(): bool
    {
        return $this->fetch_status === 'pending';
    }

    public function isFetching(): bool
    {
        return $this->fetch_status === 'fetching';
    }

    public function isFetched(): bool
    {
        return $this->fetch_status === 'completed';
    }

    public function isFetchFailed(): bool
    {
        return $this->fetch_status === 'failed';
    }

    public function getImageUrl(?string $conversion = null): ?string
    {
        $media = $this->getFirstMedia('image');

        if ($media) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        }

        return $this->original_image_url;
    }

    public function getImage(): ?Media
    {
        return $this->getFirstMedia('image');
    }

    public function hasImage(): bool
    {
        return $this->hasMedia('image') || $this->original_image_url;
    }
}

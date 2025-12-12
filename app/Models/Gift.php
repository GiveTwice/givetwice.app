<?php

namespace App\Models;

use App\Events\GiftCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gift extends Model
{
    use SoftDeletes;

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
        'image_url',
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

    /**
     * Format the price for display (e.g., "€ 12.99" or "EUR 12.99").
     */
    public function formatPrice(bool $useSymbol = true): ?string
    {
        if ($this->price_in_cents === null) {
            return null;
        }

        $amount = number_format($this->price_in_cents / 100, 2, '.', '');

        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'JPY' => '¥',
        ];

        $currencyDisplay = $useSymbol && isset($symbols[$this->currency])
            ? $symbols[$this->currency]
            : $this->currency;

        return $currencyDisplay.' '.$amount;
    }

    /**
     * Get the price as a decimal value (for form inputs).
     */
    public function getPriceAsDecimal(): ?float
    {
        if ($this->price_in_cents === null) {
            return null;
        }

        return $this->price_in_cents / 100;
    }

    /**
     * Check if the gift has a price set.
     */
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
}

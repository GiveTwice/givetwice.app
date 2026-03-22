<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiftExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'name',
        'budget_amount',
        'budget_currency',
        'event_date',
        'slug',
        'status',
        'draw_completed_at',
        'locale',
    ];

    protected function casts(): array
    {
        return [
            'budget_amount' => 'integer',
            'event_date' => 'date',
            'draw_completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GiftExchange $exchange) {
            if (empty($exchange->slug)) {
                $exchange->slug = Str::uuid()->toString();
            }
            if (empty($exchange->status)) {
                $exchange->status = 'draft';
            }
        });

        static::created(function (GiftExchange $exchange) {
            $exchange->slug = $exchange->generateSlug();
            $exchange->saveQuietly();
        });
    }

    public function generateSlug(): string
    {
        $baseSlug = Str::slug($this->name) ?: 'exchange';

        return $this->id.'-'.$baseSlug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(GiftExchangeParticipant::class, 'exchange_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isDrawn(): bool
    {
        return $this->status === 'drawn';
    }

    public function formatBudget(): ?string
    {
        if (! $this->budget_amount) {
            return null;
        }

        $amount = $this->budget_amount / 100;
        $symbol = $this->budget_currency === 'USD' ? '$' : '€';

        return $symbol.number_format($amount, $amount == intval($amount) ? 0 : 2);
    }

    public function exchangeTypeSlugs(): array
    {
        return [
            'en' => 'secret-santa',
            'nl' => 'lootjes-trekken',
            'fr' => 'tirage-au-sort',
        ];
    }

    public function getExchangeTypeSlug(?string $locale = null): string
    {
        $locale = $locale ?? $this->locale;

        return $this->exchangeTypeSlugs()[$locale] ?? 'secret-santa';
    }
}

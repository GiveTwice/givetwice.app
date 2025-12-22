<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class GiftList extends Model
{
    use HasFactory;

    protected $table = 'lists';

    protected $fillable = [
        'user_id',
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
                $list->slug = $list->generateSlug();
            }
        });

        static::updating(function (GiftList $list) {
            if ($list->isDirty('name')) {
                $list->slug = $list->generateSlug();
            }
        });
    }

    public function generateSlug(): string
    {
        return Str::slug($this->name) ?: Str::uuid()->toString();
    }

    public function getPublicUrl(?string $locale = null): string
    {
        return route('public.list', [
            'locale' => $locale ?? app()->getLocale(),
            'list' => $this->id,
            'slug' => $this->slug,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gifts(): BelongsToMany
    {
        return $this->belongsToMany(Gift::class, 'gift_list', 'list_id', 'gift_id')
            ->withPivot('sort_order', 'added_at')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }
}

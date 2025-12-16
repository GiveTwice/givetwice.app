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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'slug',
        'is_default',
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (GiftList $list) {
            if (empty($list->slug) || ! str_starts_with($list->slug, $list->id.'-')) {
                $list->slug = $list->id.'-'.Str::slug($list->name);
                $list->saveQuietly();
            }
        });
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class GiftList extends Model
{
    protected $table = 'lists';

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
        static::creating(function (GiftList $list) {
            if (empty($list->slug)) {
                $list->slug = static::generateUniqueSlug($list->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
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

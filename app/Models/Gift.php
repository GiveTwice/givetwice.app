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
        'price',
        'currency',
        'image_url',
        'fetch_status',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'fetched_at' => 'datetime',
        ];
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

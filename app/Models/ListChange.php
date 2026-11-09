<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Unguarded]
class ListChange extends Model
{
    use HasFactory;

    /** @return BelongsTo<GiftList, $this> */
    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
    }

    /** @return BelongsTo<Gift, $this> */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(Gift $gift, int $listId, string $kind): void
    {
        static::create([
            'list_id' => $listId,
            'actor_id' => auth()->id() ?? $gift->user_id,
            'gift_id' => $gift->id,
            'kind' => $kind,
            'title' => $gift->title,
        ]);
    }
}

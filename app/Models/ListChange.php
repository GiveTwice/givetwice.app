<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListChange extends Model
{
    use HasFactory;

    protected $guarded = [];

    /** @return BelongsTo<GiftList, $this> */
    public function list(): BelongsTo
    {
        return $this->belongsTo(GiftList::class, 'list_id');
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

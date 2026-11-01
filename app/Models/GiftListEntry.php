<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GiftListEntry extends Pivot
{
    protected $table = 'gift_list';

    protected static function booted(): void
    {
        static::created(fn (self $entry) => $entry->recordChange('added'));
        static::deleted(fn (self $entry) => $entry->recordChange('removed'));
    }

    private function recordChange(string $kind): void
    {
        $gift = Gift::find($this->gift_id);
        if ($gift) {
            ListChange::record($gift, $this->list_id, $kind);
        }
    }
}
